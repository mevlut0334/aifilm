<?php

namespace App\Http\Controllers;

use App\Models\CustomImage;
use App\Models\Setting;
use App\Services\TokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomImageController extends Controller
{
    public function __construct(
        private TokenService $tokenService
    ) {}

    public function index(): View
    {
        $images = CustomImage::byUser(Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('custom-images.index', compact('images'));
    }

    public function create(): View
    {
        $tokenCost = Setting::get('custom_image_token_cost', 50);
        $userBalance = Auth::user()->tokenBalance->balance ?? 0;

        return view('custom-images.create', [
            'tokenCost' => $tokenCost,
            'userBalance' => $userBalance,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:2000',
            'format' => 'required|in:vertical,horizontal,square',
            'input_image' => 'nullable|image|max:10240', // 10MB max (backward compatibility)
            'reference_images' => 'nullable|array',
            'reference_images.*' => 'image|max:10240', // 10MB max per image
        ]);

        $tokenCost = Setting::get('custom_image_token_cost', 50);
        $user = Auth::user();

        // Check token balance
        $currentBalance = $this->tokenService->getBalance($user->id);
        if ($currentBalance < $tokenCost) {
            return back()->withErrors(['error' => __('custom_images.insufficient_balance')]);
        }

        // Handle single image upload if provided (backward compatibility)
        $inputImagePath = null;
        if ($request->hasFile('input_image')) {
            $inputImagePath = $request->file('input_image')->store('custom-images/inputs', 'public');
        }

        // Deduct tokens
        $this->tokenService->deductTokens(
            $user->id,
            $tokenCost,
            'custom_image_request',
            'Custom Image Request'
        );

        // Create custom image request
        $customImage = CustomImage::create([
            'user_id' => $user->id,
            'prompt' => $validated['prompt'],
            'format' => $validated['format'],
            'input_image_path' => $inputImagePath,
            'status' => 'pending',
            'progress' => 0,
            'token_cost' => $tokenCost,
        ]);

        // Handle multiple reference images
        if ($request->hasFile('reference_images')) {
            $order = 0;
            foreach ($request->file('reference_images') as $referenceImage) {
                $path = $referenceImage->store('custom-images/references', 'public');
                $customImage->referenceImages()->create([
                    'image_path' => $path,
                    'order' => $order++,
                ]);
            }
        }

        return redirect()
            ->route('custom-images.show', $customImage->uuid)
            ->with('success', __('custom_images.request_created'));
    }

    public function show(string $uuid): View
    {
        $image = CustomImage::with('referenceImages')
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('custom-images.show', compact('image'));
    }
}

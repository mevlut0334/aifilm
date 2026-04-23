<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomVideoEditRequest;
use App\Models\CustomVideoRequest;
use App\Models\CustomVideoSegment;
use App\Services\TokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomVideoController extends Controller
{
    public function __construct(
        private TokenService $tokenService
    ) {}

    public function index(): View
    {
        $requests = CustomVideoRequest::byUser(Auth::id())
            ->with('segments')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('custom-videos.index', compact('requests'));
    }

    public function create(): View
    {
        $userBalance = Auth::user()->tokenBalance->balance ?? 0;

        return view('custom-videos.create', [
            'userBalance' => $userBalance,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string', // No length limit
            'format' => 'required|in:vertical,horizontal,square',
            'input_image' => 'nullable|image|max:10240', // Optional main image
            'reference_images.*' => 'nullable|image|max:10240', // Multiple reference images
        ]);

        $user = Auth::user();

        // Check if user has any tokens (> 0)
        $currentBalance = $this->tokenService->getBalance($user->id);
        if ($currentBalance <= 0) {
            return back()->withErrors(['error' => __('custom_videos.insufficient_balance')]);
        }

        // Handle main input image upload (optional)
        $inputImagePath = null;
        if ($request->hasFile('input_image')) {
            $inputImagePath = $request->file('input_image')->store('custom-videos/inputs', 'public');
        }

        // Create custom video request (no tokens deducted yet)
        $customVideoRequest = CustomVideoRequest::create([
            'user_id' => $user->id,
            'prompt' => $validated['prompt'],
            'format' => $validated['format'],
            'input_image_path' => $inputImagePath,
            'status' => 'pending',
            'token_cost' => null, // Will be set by admin
            'token_deducted' => false,
        ]);

        // Handle multiple reference images
        if ($request->hasFile('reference_images')) {
            foreach ($request->file('reference_images') as $index => $image) {
                $imagePath = $image->store('custom-videos/references', 'public');
                $customVideoRequest->referenceImages()->create([
                    'image_path' => $imagePath,
                    'order' => $index,
                ]);
            }
        }

        return redirect()
            ->route('custom-videos.show', $customVideoRequest->uuid)
            ->with('success', __('custom_videos.request_created'));
    }

    public function show(string $uuid): View
    {
        $request = CustomVideoRequest::with(['segments.editRequests', 'referenceImages'])
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('custom-videos.show', compact('request'));
    }

    public function requestSegmentEdit(Request $request, int $segmentId): RedirectResponse
    {
        $validated = $request->validate([
            'edit_prompt' => 'required|string', // No length limit
        ]);

        $segment = CustomVideoSegment::findOrFail($segmentId);

        // Verify segment belongs to current user's video request
        if ($segment->customVideoRequest->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if segment is completed
        if ($segment->status !== 'completed') {
            return back()->withErrors(['error' => __('custom_videos.Can only request edits for completed segments')]);
        }

        // Check if there's already a pending edit request
        $hasPendingEdit = CustomVideoEditRequest::where('custom_video_segment_id', $segmentId)
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingEdit) {
            return back()->withErrors(['error' => __('custom_videos.Segment already has a pending edit request')]);
        }

        // Create edit request
        CustomVideoEditRequest::create([
            'custom_video_segment_id' => $segmentId,
            'edit_prompt' => $validated['edit_prompt'],
            'status' => 'pending',
        ]);

        return back()->with('success', __('custom_videos.Edit request submitted successfully'));
    }
}

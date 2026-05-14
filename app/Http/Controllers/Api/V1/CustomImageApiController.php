<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCustomImageRequest;
use App\Models\CustomImage;
use App\Models\Setting;
use App\Services\TokenService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomImageApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        private TokenService $tokenService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $images = CustomImage::byUser(auth()->id())
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $data = $images->getCollection()->map(function ($image) {
                return [
                    'uuid'           => $image->uuid,
                    'type'           => 'custom_image',
                    'status'         => $image->status,
                    'progress'       => $image->progress ?? 0,
                    'orientation'    => match ($image->format) {
                        'horizontal' => 'landscape',
                        'square'     => 'square',
                        default      => 'portrait',
                    },
                    'description'    => $image->prompt,
                    'token_cost'     => $image->token_cost,
                    'output_url'     => $image->admin_image_url,
                    'failure_reason' => $image->failure_reason,
                    'created_at'     => $image->created_at->toIso8601String(),
                    'updated_at'     => $image->updated_at->toIso8601String(),
                ];
            });

            return $this->successResponse([
                'requests' => $data,
                'pagination' => [
                    'current_page' => $images->currentPage(),
                    'last_page'    => $images->lastPage(),
                    'per_page'     => $images->perPage(),
                    'total'        => $images->total(),
                ],
            ], 'Requests retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'description'      => 'required|string|max:2000',
                'orientation'      => 'required|in:portrait,landscape,square',
                'input_image'      => 'nullable|image|max:10240',
                'reference_images'   => 'nullable|array',
                'reference_images.*' => 'image|max:10240',
            ]);

            // orientation → format dönüşümü
            $format = match ($validated['orientation']) {
                'landscape' => 'horizontal',
                'square'    => 'square',
                default     => 'vertical',
            };

            $tokenCost = Setting::get('custom_image_token_cost', 50);
            $user = auth()->user();

            // Token bakiye kontrolü
            $currentBalance = $this->tokenService->getBalance($user->id);
            if ($currentBalance < $tokenCost) {
                return $this->errorResponse('Insufficient token balance', 422);
            }

            // Input image upload
            $inputImagePath = null;
            if ($request->hasFile('input_image')) {
                $inputImagePath = $request->file('input_image')
                    ->store('custom-images/inputs', 'public');
            }

            // Token düş
            $this->tokenService->deductTokens(
                $user->id,
                $tokenCost,
                'custom_image_request',
                'Custom Image Request'
            );

            // Kayıt oluştur
            $customImage = CustomImage::create([
                'user_id'          => $user->id,
                'prompt'           => $validated['description'],
                'format'           => $format,
                'input_image_path' => $inputImagePath,
                'status'           => 'pending',
                'progress'         => 0,
                'token_cost'       => $tokenCost,
            ]);

            ProcessCustomImageRequest::dispatch($customImage);

            // Reference images
            if ($request->hasFile('reference_images')) {
                $order = 0;
                foreach ($request->file('reference_images') as $referenceImage) {
                    $path = $referenceImage->store('custom-images/references', 'public');
                    $customImage->referenceImages()->create([
                        'image_path' => $path,
                        'order'      => $order++,
                    ]);
                }
            }

            return $this->successResponse([
                'request' => [
                    'uuid'        => $customImage->uuid,
                    'type'        => 'custom_image',
                    'status'      => $customImage->status,
                    'progress'    => $customImage->progress,
                    'orientation' => $validated['orientation'],
                    'description' => $customImage->prompt,
                    'token_cost'  => $customImage->token_cost,
                    'created_at'  => $customImage->created_at->toIso8601String(),
                    'updated_at'  => $customImage->updated_at->toIso8601String(),
                ],
            ], 'Custom image request created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse($e->errors(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}

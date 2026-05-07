<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomImage;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CustomImageApiController extends Controller
{
    use ApiResponse;

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
}

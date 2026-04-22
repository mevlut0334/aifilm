<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TemplateService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    use ApiResponse;

    public function __construct(
        private TemplateService $templateService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orientation = $request->query('orientation');

        if ($orientation && in_array($orientation, ['landscape', 'portrait', 'square'])) {
            $templates = $this->templateService->getTemplatesByOrientation($orientation, true);
        } else {
            $templates = $this->templateService->getActiveTemplates();
        }

        return $this->successResponse(
            data: $templates->map(function ($template) {
                return [
                    'uuid' => $template->uuid,
                    'title' => $template->getTranslations('title'),
                    'description' => $template->getTranslations('description'),
                    'token_cost' => $template->token_cost,
                    'landscape_video_url' => $template->landscape_video_path
                        ? asset('storage/'.$template->landscape_video_path)
                        : null,
                    'portrait_video_url' => $template->portrait_video_path
                        ? asset('storage/'.$template->portrait_video_path)
                        : null,
                    'square_video_url' => $template->square_video_path
                        ? asset('storage/'.$template->square_video_path)
                        : null,
                    'created_at' => $template->created_at->toIso8601String(),
                ];
            })
        );
    }

    public function show(string $uuid): JsonResponse
    {
        $template = $this->templateService->getTemplateByUuid($uuid);

        if (! $template || ! $template->is_active) {
            return $this->errorResponse(
                message: __('api.template_not_found'),
                status: 404
            );
        }

        return $this->successResponse(
            data: [
                'uuid' => $template->uuid,
                'title' => $template->getTranslations('title'),
                'description' => $template->getTranslations('description'),
                'token_cost' => $template->token_cost,
                'landscape_video_url' => $template->landscape_video_path
                    ? asset('storage/'.$template->landscape_video_path)
                    : null,
                'portrait_video_url' => $template->portrait_video_path
                    ? asset('storage/'.$template->portrait_video_path)
                    : null,
                'square_video_url' => $template->square_video_path
                    ? asset('storage/'.$template->square_video_path)
                    : null,
                'created_at' => $template->created_at->toIso8601String(),
            ]
        );
    }
}

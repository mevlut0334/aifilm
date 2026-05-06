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

    private function resolveVideoUrl(?string $path): ?string
    {
        if (!$path) return null;
        return str_starts_with($path, 'http')
            ? $path
            : asset('storage/' . $path);
    }

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
                    'landscape_video_url' => $this->resolveVideoUrl($template->landscape_video_path),
                    'portrait_video_url' => $this->resolveVideoUrl($template->portrait_video_path),
                    'square_video_url' => $this->resolveVideoUrl($template->square_video_path),
                    'poster_url' => $template->poster_url,
                    'created_at' => $template->created_at->toIso8601String(),
                ];
            })->values()
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
                'landscape_video_url' => $this->resolveVideoUrl($template->landscape_video_path),
                'portrait_video_url' => $this->resolveVideoUrl($template->portrait_video_path),
                'square_video_url' => $this->resolveVideoUrl($template->square_video_path),
                'poster_url' => $template->poster_url,
                'created_at' => $template->created_at->toIso8601String(),
            ]
        );
    }
}

<?php

namespace App\Services;

use App\Models\Template;
use App\Repositories\TemplateRepository;
use Illuminate\Support\Facades\Storage;

class TemplateService
{
    public function __construct(
        private TemplateRepository $templateRepository
    ) {}

    public function getAllTemplates()
    {
        return $this->templateRepository->getAll();
    }

    public function getActiveTemplates()
    {
        return $this->templateRepository->getActive();
    }

    public function getTemplatesByOrientation(string $orientation, bool $activeOnly = true)
    {
        if ($activeOnly) {
            return $this->templateRepository->getActiveByOrientation($orientation);
        }

        return $this->templateRepository->getAll()
            ->filter(fn ($template) => $template->hasVideoForOrientation($orientation));
    }

    public function getTemplateByUuid(string $uuid): ?Template
    {
        return $this->templateRepository->findByUuid($uuid);
    }

    public function createTemplate(array $data): Template
    {
        // Set order for new template
        if (! isset($data['order'])) {
            $data['order'] = $this->templateRepository->getMaxOrder() + 1;
        }

        return $this->templateRepository->create($data);
    }

    public function updateTemplate(Template $template, array $data): bool
    {
        return $this->templateRepository->update($template, $data);
    }

    public function deleteTemplate(Template $template): bool
    {
        // Delete associated video files
        $this->deleteTemplateVideos($template);

        return $this->templateRepository->delete($template);
    }

    public function uploadVideo(Template $template, $file, string $orientation): string
    {
        // Delete old video if exists
        $oldPath = $template->getVideoPathForOrientation($orientation);
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        // Store new video
        $path = $file->store("templates/{$template->uuid}/{$orientation}", 'public');

        // Update template
        $fieldName = "{$orientation}_video_path";
        $template->update([$fieldName => $path]);

        return $path;
    }

    public function deleteVideo(Template $template, string $orientation): bool
    {
        $path = $template->getVideoPathForOrientation($orientation);

        if (! $path) {
            return false;
        }

        // Delete file from storage
        Storage::disk('public')->delete($path);

        // Update template
        $fieldName = "{$orientation}_video_path";
        $template->update([$fieldName => null]);

        return true;
    }

    private function deleteTemplateVideos(Template $template): void
    {
        $orientations = ['landscape', 'portrait', 'square'];

        foreach ($orientations as $orientation) {
            $path = $template->getVideoPathForOrientation($orientation);
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    public function toggleActive(Template $template): bool
    {
        return $template->update(['is_active' => ! $template->is_active]);
    }

    public function reorder(array $order): void
    {
        foreach ($order as $index => $uuid) {
            $template = $this->getTemplateByUuid($uuid);
            if ($template) {
                $template->update(['order' => $index]);
            }
        }
    }
}

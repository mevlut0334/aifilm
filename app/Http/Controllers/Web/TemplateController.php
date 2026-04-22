<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function __construct(
        private TemplateService $templateService
    ) {}

    public function index(Request $request): View
    {
        $orientation = $request->query('orientation');

        if ($orientation && in_array($orientation, ['landscape', 'portrait', 'square'])) {
            $templates = $this->templateService->getTemplatesByOrientation($orientation, true);
        } else {
            $templates = $this->templateService->getActiveTemplates();
        }

        return view('web.templates.index', [
            'templates' => $templates,
            'selectedOrientation' => $orientation,
        ]);
    }

    public function show(string $uuid): View
    {
        $template = $this->templateService->getTemplateByUuid($uuid);

        if (! $template || ! $template->is_active) {
            abort(404);
        }

        return view('web.templates.show', [
            'template' => $template,
        ]);
    }
}

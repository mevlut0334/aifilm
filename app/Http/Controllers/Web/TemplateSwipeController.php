<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\TemplateService;
use Illuminate\Http\Request;

class TemplateSwipeController extends Controller
{
    public function __construct(
        private TemplateService $templateService
    ) {}

    public function show(string $uuid)
    {
        $templates = $this->templateService->getActiveTemplates();

        // Tıklanan template'i başa al
        $sorted = $templates->sortBy(fn($t) => $t->uuid === $uuid ? 0 : 1)->values();

        return view('web.templates.swipe', [
            'templates' => $sorted,
            'currentUuid' => $uuid,
        ]);
    }
}

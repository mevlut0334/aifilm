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

        // Tıklanan template'i tek geçişte başa al
        $target = $templates->firstWhere('uuid', $uuid);
        $others = $templates->filter(fn($t) => $t->uuid !== $uuid)->values();
        $sorted = $target ? $others->prepend($target) : $templates->values();

        return view('web.templates.swipe', [
            'templates'   => $sorted,
            'currentUuid' => $uuid,
        ]);
    }
}

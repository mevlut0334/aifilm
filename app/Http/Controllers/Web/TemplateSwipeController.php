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
        // Orijinal sıra korunuyor — tıklanan video listenin neresindeyse
        // orada kalıyor. Böylece swipe sayfası tam olarak o pozisyondan
        // başlar ve kaydırma ana sayfadaki sırayla (5 -> 6 -> 7 ...) devam eder.
        $templates = $this->templateService->getActiveTemplates()->values();

        return view('web.templates.swipe', [
            'templates'   => $templates,
            'currentUuid' => $uuid,
        ]);
    }
}

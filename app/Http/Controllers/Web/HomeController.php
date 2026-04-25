<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Services\TemplateService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private TemplateService $templateService
    ) {}

    public function index(): View
    {
        $templates = $this->templateService->getActiveTemplates();
        $sliders = Slider::active()->ordered()->get();

        return view('web.home', compact('templates', 'sliders'));
    }
}

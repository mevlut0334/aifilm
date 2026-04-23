<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\SettingRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        private SettingRepository $settingRepository
    ) {}

    public function index(): View
    {
        $settings = $this->settingRepository->all();

        return view('admin.settings.index', [
            'settings' => $settings,
            'registrationTokenGrant' => $this->settingRepository->get('registration_token_grant', 100),
            'customImageTokenCost' => $this->settingRepository->get('custom_image_token_cost', 50),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'registration_token_grant' => 'required|integer|min:0',
            'custom_image_token_cost' => 'required|integer|min:1',
        ]);

        $this->settingRepository->set(
            'registration_token_grant',
            $request->input('registration_token_grant'),
            'integer'
        );

        $this->settingRepository->set(
            'custom_image_token_cost',
            $request->input('custom_image_token_cost'),
            'integer'
        );

        return back()->with('success', __('admin.settings.updated_successfully'));
    }
}

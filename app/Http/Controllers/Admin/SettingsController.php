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
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'registration_token_grant' => 'required|integer|min:0',
        ]);

        $this->settingRepository->set(
            'registration_token_grant',
            $request->input('registration_token_grant'),
            'integer'
        );

        return back()->with('success', __('admin.settings.updated_successfully'));
    }
}

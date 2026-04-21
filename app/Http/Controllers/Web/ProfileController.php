<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ProfileRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function show(): View
    {
        $user = $this->authService->getUser();
        $countryCodes = json_decode(file_get_contents(resource_path('data/country_codes.json')), true);

        return view('web.profile', compact('user', 'countryCodes'));
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $this->authService->getUser();
        $user->update($request->validated());

        return back()->with('success', 'Profile updated successfully');
    }
}

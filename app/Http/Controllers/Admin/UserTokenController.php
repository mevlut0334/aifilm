<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Services\TokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserTokenController extends Controller
{
    public function __construct(
        private TokenService $tokenService,
        private UserRepository $userRepository
    ) {}

    public function addForm(int $userId): View
    {
        $user = $this->userRepository->findById($userId);
        abort_if(! $user, 404);

        return view('admin.users.tokens.add', [
            'user' => $user,
            'balance' => $this->tokenService->getBalance($userId),
        ]);
    }

    public function add(Request $request, int $userId): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        $user = $this->userRepository->findById($userId);
        abort_if(! $user, 404);

        try {
            $this->tokenService->addTokens(
                $userId,
                $request->input('amount'),
                'admin_grant',
                $request->input('note')
            );

            return redirect()->route('admin.users.index')
                ->with('success', __('admin.tokens.added_successfully'));
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function deductForm(int $userId): View
    {
        $user = $this->userRepository->findById($userId);
        abort_if(! $user, 404);

        return view('admin.users.tokens.deduct', [
            'user' => $user,
            'balance' => $this->tokenService->getBalance($userId),
        ]);
    }

    public function deduct(Request $request, int $userId): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        $user = $this->userRepository->findById($userId);
        abort_if(! $user, 404);

        try {
            $this->tokenService->deductTokens(
                $userId,
                $request->input('amount'),
                'admin_deduct',
                $request->input('note')
            );

            return redirect()->route('admin.users.index')
                ->with('success', __('admin.tokens.deducted_successfully'));
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function transactions(int $userId): View
    {
        $user = $this->userRepository->findById($userId);
        abort_if(! $user, 404);

        $transactions = $this->tokenService->getTransactions($userId);

        return view('admin.users.tokens.transactions', [
            'user' => $user,
            'transactions' => $transactions,
            'balance' => $this->tokenService->getBalance($userId),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private TokenService $tokenService
    ) {}

    public function index(): View
    {
        $users = User::with('tokenBalance')
            ->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function show(int $id): View
    {
        $user = User::with(['tokenBalance', 'tokenTransactions'])
            ->findOrFail($id);

        return view('admin.users.show', [
            'user' => $user,
            'balance' => $this->tokenService->getBalance($id),
        ]);
    }
}

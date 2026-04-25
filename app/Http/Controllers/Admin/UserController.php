<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private TokenService $tokenService
    ) {}

    public function index(Request $request): View
    {
        $query = User::with('tokenBalance');

        // E-posta filtreleme
        if ($request->filled('email') && strlen($request->email) >= 3) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        $users = $query->paginate(20)->appends($request->only('email'));

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

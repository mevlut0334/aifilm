<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminManagementController extends Controller
{
    public function index()
    {
        $admins = Admin::orderBy('created_at', 'desc')->get();
        $currentAdminId = Auth::guard('admin')->id();

        return view('admin.admins.index', compact('admins', 'currentAdminId'));
    }

    public function create()
    {
        return view('admin.admins.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Admin::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin başarıyla eklendi.');
    }

    public function destroy($id)
    {
        $currentAdminId = Auth::guard('admin')->id();

        // Kendini silme engeli
        if ($currentAdminId == $id) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'Kendi hesabınızı silemezsiniz.');
        }

        $admin = Admin::findOrFail($id);
        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin başarıyla silindi.');
    }
}

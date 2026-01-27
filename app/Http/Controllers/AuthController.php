<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        // Check credentials first
        if (!Auth::validate($data)) {
            return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة'])->withInput();
        }

        $user = User::where('email', $data['email'])->first();

        // Check active status
        if (!$user->is_active) {
            return back()->with('error', 'عذراً، هذا الحساب موقف مؤقتاً. يرجى مراجعة المسؤول.')->withInput();
        }

        // Login
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function showRegister()
    {
        // Allow open registration only when there are no users — otherwise only admins
        if (\App\Models\User::count() === 0) {
            return view('auth.register');
        }

        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Registration is restricted to administrators');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        // - If there are no users at all: create the first admin user (bootstrap)
        // - Otherwise, only an authenticated admin may create new users
        if (User::count() === 0) {
            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']), 'role' => 'admin']);
            Auth::login($user);
            return redirect('/dashboard');
        }

        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Only admin may register new users');
        }

        // Admin can set role (optional) when creating users via the register form
        $role = $request->input('role', 'staff');
        $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']), 'role' => $role]);
        // do not auto-login when admin creates a user; redirect back to admin UI
        return redirect('/users');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}

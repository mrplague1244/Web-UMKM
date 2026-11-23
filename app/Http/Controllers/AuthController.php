<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Tampilkan Halaman Login Penjual
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('seller.pos');
        }

        $store = config('beverage.store', []);
        return view('auth.login', compact('store'));
    }

    /**
     * Proses Autentikasi Pengguna
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('seller.pos'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi tidak cocok.',
        ])->onlyInput('email');
    }

    /**
     * Tampilkan Halaman Pendaftaran Akun Baru
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('seller.pos');
        }

        $store = config('beverage.store', []);
        return view('auth.register', compact('store'));
    }

    /**
     * Proses Registrasi Akun Baru
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', 'min:6'],
            'role' => 'nullable|in:owner,cashier',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?: 'owner',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('seller.pos')
            ->with('success', 'Akun berhasil didaftarkan! Selamat datang di Sistem Operasional UMKM.');
    }

    /**
     * Keluar dari Sesi (Logout)
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}

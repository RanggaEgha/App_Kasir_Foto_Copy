<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => false,
            'status' => session('status'),
        ]);
    }

    public function store(Request $request) /* RedirectResponse not needed; we return Inertia::location */
    {
        $credentials = $request->validate(
            [
                'email'    => ['required', 'email'],
                'password' => ['required'],
            ],
            [
                'email.required'    => 'Email wajib diisi.',
                'email.email'       => 'Format email tidak valid.',
                'password.required' => 'Password wajib diisi.',
            ]
        );

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        if (! (Auth::user()->is_active ?? false)) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'Akun dinonaktifkan.']);
        }

        $request->session()->regenerate();

        // Flash ucapan welcome
        $user = Auth::user();
        $hour = (int) now()->setTimezone(config('app.timezone'))->format('H');
        $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
        Session::flash('welcome', "{$greet}, {$user->name}! Anda masuk sebagai " . ucfirst($user->role) . ".");

        // Langsung hard-redirect ke halaman sesuai role (menghindari 2x redirect)
        $dest = $user->role === 'admin' ? route('dashboard') : route('pembayaran.create');
        return Inertia::location($dest);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return $this->redirectByRole($request);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        if (Auth::check()) {
            return $this->redirectByRole($request);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Veuillez saisir votre adresse email.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'password.required' => 'Veuillez saisir votre mot de passe.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (! $user->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->withErrors([
                        'email' => 'Ce compte a été désactivé. Contactez un administrateur.',
                    ])
                    ->onlyInput('email');
            }

            $request->session()->regenerate();

            return $this->redirectByRole($request);
        }

        return back()
            ->withErrors([
                'email' => 'Email ou mot de passe incorrect.',
            ])
            ->onlyInput('email');
    }

    private function redirectByRole(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->must_change_password) {
            return redirect()->route('password.change');
        }

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'commercial' => redirect()->route('commercial.dashboard'),
            'client' => redirect()->route('client.dashboard'),

            default => $this->logoutInvalidRole($request),
        };
    }

    private function logoutInvalidRole(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors([
                'email' => 'Votre compte existe, mais son rôle n’est pas reconnu. Veuillez contacter l’administrateur.',
            ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
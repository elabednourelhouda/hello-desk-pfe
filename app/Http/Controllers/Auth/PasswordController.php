<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function edit()
    {
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'Veuillez saisir un nouveau mot de passe.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard')->with('success', 'Mot de passe modifié avec succès.'),
            'commercial' => redirect()->route('commercial.dashboard')->with('success', 'Mot de passe modifié avec succès.'),
            'client' => redirect()->route('client.dashboard')->with('success', 'Mot de passe modifié avec succès.'),
            default => redirect()->route('login'),
        };
    }
}
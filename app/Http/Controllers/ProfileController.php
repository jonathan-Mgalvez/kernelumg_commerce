<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'resumen' => [
                'cotizaciones' => $user->quotes()->where('status', '!=', 'borrador')->count(),
                'aprobadas' => $user->quotes()->where('status', 'aprobada')->count(),
                'pedidos' => $user->orders()->count(),
                'comprado' => $user->orders()->where('status', '!=', 'cancelado')->sum('total'),
            ],
            'ultimoPedido' => $user->orders()->latest()->first(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Los pedidos son registros contables: una cuenta con pedidos no se borra.
        if ($user->orders()->exists()) {
            return back()->withErrors(['password' => 'Tu cuenta tiene pedidos registrados y no se puede eliminar. Comunícate con FerroTek para darla de baja.'], 'userDeletion');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CustomerProfileController extends Controller
{
    public function profile(): View
    {
        $user = Auth::user();
        return view('customer.profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($data);

        return redirect()->route('customer.profile')->with('success', 'Datos de perfil actualizados exitosamente.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->route('customer.profile')->with('success', 'Contraseña modificada correctamente.');
    }

    public function orders(): View
    {
        $orders = Order::where('user_id', Auth::id())
            ->withCount('details')
            ->latest()
            ->paginate(10);

        return view('customer.orders', compact('orders'));
    }

    public function trackOrder(string $trackingCode): View
    {
        $order = Order::where('tracking_code', $trackingCode)
            ->where('user_id', Auth::id())
            ->with(['details.product'])
            ->firstOrFail();

        return view('customer.track', compact('order'));
    }
}
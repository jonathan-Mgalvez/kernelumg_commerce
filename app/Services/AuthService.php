<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function authenticate(array $credentials, bool $remember = false): User
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas no coinciden con nuestros registros.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Su cuenta se encuentra inhabilitada. Contacte con la administración.'],
            ]);
        }

        Auth::login($user, $remember);
        request()->session()->regenerate();
        $this->syncSessionCartToDatabase($user);

        return $user;
    }

    public function registerCustomer(array $data): User
    {
        $customerRole = Role::where('slug', 'customer')->firstOrFail();

        $user = User::create([
            'role_id' => $customerRole->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'is_active' => true,
        ]);

        Auth::login($user);
        request()->session()->regenerate();
        $this->syncSessionCartToDatabase($user);

        return $user;
    }

    public function syncSessionCartToDatabase(User $user): void
    {
        $sessionToken = session()->get('cart_token');

        if (!$sessionToken) {
            return;
        }

        $sessionCart = Cart::where('session_token', $sessionToken)->first();

        if (!$sessionCart) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

        foreach ($sessionCart->items as $sessionItem) {
            $existingItem = CartItem::where('cart_id', $userCart->id)
                ->where('product_id', $sessionItem->product_id)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $sessionItem->quantity,
                ]);
            } else {
                CartItem::create([
                    'cart_id' => $userCart->id,
                    'product_id' => $sessionItem->product_id,
                    'quantity' => $sessionItem->quantity,
                ]);
            }
        }

        $sessionCart->items()->delete();
        $sessionCart->delete();
        session()->forget('cart_token');
    }
}
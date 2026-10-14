<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::withCount('quotes', 'orders')->orderBy('name')->paginate(20),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['sometimes', 'required', Rule::in(array_keys(User::ROLES))],
            'active' => ['sometimes', 'boolean'],
        ]);

        if ($user->is($request->user())) {
            return back()->withErrors(['role' => 'No puedes cambiar tu propio rol ni desactivar tu propia cuenta.']);
        }

        $user->forceFill($data)->save();

        $mensaje = isset($data['active'])
            ? ($data['active'] ? "Cuenta de {$user->name} activada." : "Cuenta de {$user->name} desactivada: ya no podrá iniciar sesión.")
            : "{$user->name} ahora es {$user->role_label}.";

        return back()->with('status', $mensaje);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['role' => 'No puedes eliminar tu propia cuenta desde aquí.']);
        }
        if ($user->orders()->exists()) {
            $user->forceFill(['active' => false])->save();

            return back()->with('status', "{$user->name} tiene pedidos registrados; su cuenta se desactivó en lugar de eliminarse.");
        }
        $user->delete();

        return back()->with('status', 'Usuario eliminado.');
    }
}

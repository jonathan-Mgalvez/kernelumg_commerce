<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class OrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user !== null && in_array($user->role->slug ?? '', ['admin', 'inventory_manager']);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:Pendiente,Procesando,Despachado,Cancelado'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Debe indicar un estado válido para la orden.',
            'status.in' => 'El estado seleccionado no pertenece al flujo de pedidos autorizado.',
        ];
    }
}
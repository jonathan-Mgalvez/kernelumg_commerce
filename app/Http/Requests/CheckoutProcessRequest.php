<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CheckoutProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
    return Auth::check();
    }

    public function rules(): array
    {
        return [
            'shipping_address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'in:Transferencia Bancaria,Pago Contra Entrega,Tarjeta de Credito/Debito'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_address.required' => 'La dirección completa de entrega es obligatoria.',
            'payment_method.required' => 'Debe seleccionar un método de pago válido.',
            'payment_method.in' => 'El método de pago especificado no está autorizado por el sistema.',
        ];
    }
}
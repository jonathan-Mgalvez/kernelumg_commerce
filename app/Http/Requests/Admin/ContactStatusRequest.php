<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ContactStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user !== null && method_exists($user, 'hasRole') && $user->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:No Leído,Leído,Respondido'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
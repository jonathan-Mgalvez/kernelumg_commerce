<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BulkInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user !== null && in_array($user->role->slug ?? '', ['admin', 'inventory_manager']);
    }

    public function rules(): array
    {
        return [
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'integer', 'exists:products,id'],
            'products.*.price' => ['required', 'numeric', 'min:0.01'],
            'products.*.stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
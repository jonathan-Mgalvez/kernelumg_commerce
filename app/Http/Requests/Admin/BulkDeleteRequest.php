<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BulkDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user !== null && in_array($user->role->slug ?? '', ['admin', 'inventory_manager']);
    }

    public function rules(): array
    {
        return [
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', 'integer', 'exists:products,id'],
        ];
    }
}
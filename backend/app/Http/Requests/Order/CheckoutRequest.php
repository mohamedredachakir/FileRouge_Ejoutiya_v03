<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.size' => ['required_with:items', 'string', 'max:50'],
        ];
    }
}

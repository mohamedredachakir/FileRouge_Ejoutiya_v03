<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category' => ['required', Rule::in(['t_shirt', 'hoodie', 'pants', 'sneakers', 'accessories'])],
            'status' => ['required', Rule::in(['active', 'out_of_stock', 'hidden'])],
            'images' => ['nullable', 'array'],
            'images.*' => ['string', 'max:255'],
        ];
    }
}

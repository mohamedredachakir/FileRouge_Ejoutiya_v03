<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'category' => ['sometimes', Rule::in(['t_shirt', 'hoodie', 'pants', 'sneakers', 'accessories'])],
            'status' => ['sometimes', Rule::in(['active', 'out_of_stock', 'hidden'])],
            'sizes' => ['sometimes', 'array'],
            'sizes.*' => ['string'],
            'images' => ['sometimes', 'array'],
            'images.*' => ['nullable', 'sometimes'],
        ];
    }
}

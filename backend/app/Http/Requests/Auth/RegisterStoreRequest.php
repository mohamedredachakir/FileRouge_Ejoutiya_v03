<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'store_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'hero_image' => ['nullable', 'image', 'max:5120'],
        ];
    }
}

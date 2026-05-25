<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username'  => ['required', 'string', 'max:50', 'unique:users,username'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'full_name' => ['required', 'string', 'max:100'],
            'password'  => ['required', 'string', Password::min(8)],
            'role'      => ['required', 'string', 'in:admin,staff'],
        ];
    }
}

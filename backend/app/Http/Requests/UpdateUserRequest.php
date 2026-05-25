<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user');

        return [
            'username'  => ['sometimes', 'string', 'max:50', Rule::unique('users', 'username')->ignore($userId)],
            'email'     => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'full_name' => ['sometimes', 'string', 'max:100'],
            'password'  => ['sometimes', 'string', Password::min(8)],
            'role'      => ['sometimes', 'string', 'in:admin,staff'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

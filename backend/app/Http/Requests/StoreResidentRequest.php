<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResidentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Controlled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purok_id' => ['required', 'exists:puroks,id'],
            'head_of_household_id' => ['nullable', 'exists:residents,id'],
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'string', 'in:Male,Female,Other'],
            'civil_status' => ['required', 'string', 'in:Single,Married,Widowed,Divorced'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'is_voter' => ['required', 'boolean'],
            'is_indigent' => ['required', 'boolean'],
            'is_pwd' => ['required', 'boolean'],
            'is_senior_citizen' => ['required', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }
}

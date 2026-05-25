<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResidentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purok_id' => ['sometimes', 'required', 'exists:puroks,id'],
            'head_of_household_id' => ['sometimes', 'nullable', 'exists:residents,id'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'date_of_birth' => ['sometimes', 'required', 'date'],
            'gender' => ['sometimes', 'required', 'string', 'in:Male,Female,Other'],
            'civil_status' => ['sometimes', 'required', 'string', 'in:Single,Married,Widowed,Divorced'],
            'occupation' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_voter' => ['sometimes', 'required', 'boolean'],
            'is_indigent' => ['sometimes', 'required', 'boolean'],
            'is_pwd' => ['sometimes', 'required', 'boolean'],
            'is_senior_citizen' => ['sometimes', 'required', 'boolean'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }
}

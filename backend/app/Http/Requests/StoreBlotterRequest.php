<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlotterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Middleware handles auth
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'incident_type' => ['required', 'string', 'max:100'],
            'incident_date' => ['required', 'date'],
            'incident_location' => ['required', 'string', 'max:255'],
            'incident_narrative' => ['required', 'string'],
            
            // Validate optional nested parties
            'parties' => ['nullable', 'array'],
            'parties.*.resident_id' => ['nullable', 'exists:residents,id'],
            'parties.*.role' => ['required', 'string', 'in:complainant,respondent,witness'],
            'parties.*.full_name' => ['required', 'string', 'max:255'],
            'parties.*.address' => ['nullable', 'string', 'max:255'],
            'parties.*.contact_number' => ['nullable', 'string', 'max:50'],
            'parties.*.statement' => ['nullable', 'string'],
        ];
    }
}

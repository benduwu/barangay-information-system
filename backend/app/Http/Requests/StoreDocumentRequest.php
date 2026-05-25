<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
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
            'resident_id' => ['required', 'exists:residents,id'],
            'document_type' => ['required', 'string', 'in:clearance,residency,indigency'],
            'purpose' => ['required', 'string', 'max:500'],
        ];
    }
}

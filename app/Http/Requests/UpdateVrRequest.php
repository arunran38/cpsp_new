<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVrRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'vr_ref_no' => 'required|string|max:255',
            'vr_date' => 'required|date|before_or_equal:today',
            'vr_received_at_cpsp_date' => 'nullable|date|before_or_equal:today',
            'vr_remarks' => 'nullable|string',
            'vr_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vr_date.before_or_equal' => 'Date cannot be in the future.',
            'vr_received_at_cpsp_date.before_or_equal' => 'Date cannot be in the future.',
        ];
    }
}

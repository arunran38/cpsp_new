<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePetitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'petition_no' => 'required|unique:petitions,petition_no',
            'date_of_petition_received' => 'required|date|before_or_equal:today',
            'nature_of_petition' => 'required_without:duplicate_link_number',
            'mode_of_petition_received' => 'required_without:duplicate_link_number',
            'description' => 'required_without:duplicate_link_number',
            'proposed_action' => 'nullable|string',
            'mode_others' => 'nullable|string',
            'complainants' => 'nullable|array',
            'complainants.*.name' => 'nullable|string',
            'complainants.*.phone' => 'nullable|string',
            'complainants.*.email' => 'nullable|email',
            'complainants.*.addresses' => 'nullable|array',
            'accused' => 'nullable|array',
            'accused.*.entity_type' => 'required|in:Person,Firm',
            'accused.*.name' => 'required|string',
            'accused.*.designation_id' => 'nullable|exists:designation_lists,id',
            'accused.*.department_id' => 'nullable|exists:department_lists,id',
            'accused.*.phone' => 'nullable|string',
            'accused.*.pen_number' => 'nullable|digits_between:6,7',
            'accused.*.addresses' => 'nullable|array',
            'linked_petition_id' => 'nullable|exists:petitions,petition_id',
            'evidence_files' => 'nullable|array',
            'evidence_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'date_of_petition_received.before_or_equal' => 'Date cannot be in the future.',
            'accused.*.pen_number.digits_between' => 'The PEN number must be either 6 or 7 digits.',
        ];
    }
}

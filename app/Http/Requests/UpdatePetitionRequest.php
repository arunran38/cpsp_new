<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePetitionRequest extends FormRequest
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
        $petitionId = $this->route('petition');
        return [
            'petition_no' => 'required|unique:petitions,petition_no,' . $petitionId . ',petition_id',
            'date_of_petition_received' => 'required|date|before_or_equal:today',
            'nature_of_petition' => 'required',
            'mode_of_petition_received' => 'required',
            'description' => 'required',
            'proposed_action' => 'nullable|string',
            'mode_others' => 'nullable|string',
            'complainants' => 'nullable|array',
            'complainants.*.name' => 'required|string',
            'complainants.*.phone' => 'nullable|string',
            'complainants.*.addresses' => 'nullable|array',
            'accused' => 'nullable|array',
            'accused.*.entity_type' => 'required|in:Person,Firm',
            'accused.*.name' => 'required|string',
            'accused.*.contact_person' => 'nullable|required_if:accused.*.entity_type,Firm|string',
            'accused.*.phone' => 'nullable|string',
            'accused.*.addresses' => 'nullable|array',
            'deleted_attachments' => 'nullable|array',
            'deleted_attachments.*' => 'exists:uploads,upload_id'
        ];
    }

    public function messages(): array
    {
        return [
            'date_of_petition_received.before_or_equal' => 'Date cannot be in the future.',
        ];
    }
}

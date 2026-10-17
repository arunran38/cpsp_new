<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Petition;
use App\Services\HashidService;

class UpdatePetitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Clean complainants: trim strings and remove completely empty complainants
        if ($this->has('complainants') && is_array($this->complainants)) {
            $cleanedComplainants = [];
            foreach ($this->complainants as $index => $comp) {
                if (!is_array($comp)) continue;
                $name = isset($comp['name']) ? trim($comp['name']) : '';
                $phone = isset($comp['phone']) ? trim($comp['phone']) : '';
                $email = isset($comp['email']) ? trim($comp['email']) : '';

                $hasAddr = false;
                if (!empty($comp['addresses']) && is_array($comp['addresses'])) {
                    foreach ($comp['addresses'] as $addr) {
                        if (!empty(trim($addr['address'] ?? ''))) { $hasAddr = true; break; }
                    }
                }

                // If completely blank, drop it
                if ($name === '' && $phone === '' && $email === '' && !$hasAddr) {
                    continue;
                }

                $cleanedComplainants[] = [
                    'name' => $name !== '' ? $name : null,
                    'phone' => $phone !== '' ? $phone : null,
                    'email' => $email !== '' ? $email : null,
                    'addresses' => $comp['addresses'] ?? [],
                ];
            }
            $this->merge(['complainants' => $cleanedComplainants]);
        }

        // Clean accused: trim strings, keep department even without name
        if ($this->has('accused') && is_array($this->accused)) {
            $cleanedAccused = [];
            foreach ($this->accused as $index => $acc) {
                if (!is_array($acc)) continue;
                $name = isset($acc['name']) ? trim($acc['name']) : '';
                $phone = isset($acc['phone']) ? trim($acc['phone']) : '';
                $pen = isset($acc['pen_number']) ? trim($acc['pen_number']) : '';
                $dept = !empty($acc['department_id']) ? $acc['department_id'] : null;
                $desig = !empty($acc['designation_id']) ? $acc['designation_id'] : null;

                $hasAddr = false;
                if (!empty($acc['addresses']) && is_array($acc['addresses'])) {
                    foreach ($acc['addresses'] as $addr) {
                        if (!empty(trim($addr['address'] ?? ''))) { $hasAddr = true; break; }
                    }
                }

                // If completely blank and there are other accused rows, drop extraneous row
                if ($name === '' && $phone === '' && $pen === '' && !$hasAddr && empty($dept) && empty($desig)) {
                    if (count($this->accused) > 1) {
                        continue;
                    }
                }

                $cleanedAccused[] = [
                    'entity_type' => $acc['entity_type'] ?? 'Person',
                    'name' => $name !== '' ? $name : null,
                    'designation_id' => $desig,
                    'department_id' => $dept,
                    'phone' => $phone !== '' ? $phone : null,
                    'pen_number' => $pen !== '' ? $pen : null,
                    'addresses' => $acc['addresses'] ?? [],
                ];
            }
            $this->merge(['accused' => $cleanedAccused]);
        }
    }

    public function rules(): array
    {
        $routePetition = $this->route('petition');
        $petitionId = $routePetition instanceof Petition
            ? $routePetition->petition_id
            : HashidService::decode($routePetition) ?? $routePetition;

        return [
            'receipt_no' => [
                'required',
                Rule::unique('petitions', 'receipt_no')->ignore($petitionId, 'petition_id'),
            ],
            'date_of_petition_received' => 'required|date|before_or_equal:today',
            'nature_of_petition' => 'required',
            'mode_of_petition_received' => 'required',
            'unit_id' => [
                'nullable',
                'exists:units,unit_id',
                Rule::requiredIf(fn() => strtolower((string)$this->input('mode_of_petition_received')) === 'unit'),
            ],
            'date_of_petition_received_at_unit' => [
                'nullable',
                'date',
                'before_or_equal:today',
                Rule::requiredIf(fn() => strtolower((string)$this->input('mode_of_petition_received')) === 'unit'),
            ],
            'description' => 'required',
            'proposed_action' => 'nullable|string',
            'mode_others' => 'nullable|string',
            'complainants' => 'nullable|array',
            'complainants.*.name' => 'nullable|string|max:255',
            'complainants.*.phone' => 'nullable|string',
            'complainants.*.email' => 'nullable|email',
            'complainants.*.addresses' => 'nullable|array',
            'accused' => 'required|array|min:1',
            'accused.*.entity_type' => 'nullable|in:Person,Firm',
            'accused.*.name' => 'nullable|string|max:255',
            'accused.*.designation_id' => 'nullable|exists:designation_lists,id',
            'accused.*.department_id' => 'required|exists:department_lists,id',
            'accused.*.phone' => 'nullable|string',
            'accused.*.pen_number' => 'nullable|digits_between:6,7',
            'accused.*.addresses' => 'nullable|array',
            'deleted_attachments' => 'nullable|array',
            'deleted_attachments.*' => 'exists:uploads,upload_id',
            'evidence_files' => 'nullable|array',
            'evidence_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240'
        ];
    }

    public function attributes(): array
    {
        return [
            'receipt_no' => 'receipt number',
            'date_of_petition_received' => 'date of receipt',
            'nature_of_petition' => 'nature of petition',
            'mode_of_petition_received' => 'mode of petition',
            'unit_id' => 'concerned unit',
            'date_of_petition_received_at_unit' => 'date of petition received at unit',
            'description' => 'description',
            'complainants' => 'complainants',
            'complainants.*.name' => 'complainant full name',
            'complainants.*.phone' => 'complainant phone number',
            'complainants.*.email' => 'complainant email address',
            'accused' => 'suspects',
            'accused.*.name' => 'suspect full name',
            'accused.*.department_id' => 'suspect department',
            'accused.*.designation_id' => 'suspect designation',
            'accused.*.phone' => 'suspect phone number',
            'accused.*.pen_number' => 'suspect PEN number',
        ];
    }

    public function messages(): array
    {
        return [
            'date_of_petition_received.before_or_equal' => 'Date of receipt cannot be in the future.',
            'accused.required' => 'At least one suspect department is required.',
            'accused.min' => 'At least one suspect department is required.',
            'accused.*.department_id.required' => 'The department field is mandatory.',
            'accused.*.department_id.exists' => 'The selected suspect department is invalid.',
            'accused.*.designation_id.exists' => 'The selected suspect designation is invalid.',
            'accused.*.pen_number.digits_between' => 'The PEN number must be either 6 or 7 digits.',
            'unit_id.required' => 'The concerned unit field is required when mode of receipt is Unit.',
            'date_of_petition_received_at_unit.required' => 'The date of petition received at unit field is required when mode of receipt is Unit.',
            'date_of_petition_received_at_unit.before_or_equal' => 'Date of petition received at unit cannot be in the future.',
        ];
    }
}

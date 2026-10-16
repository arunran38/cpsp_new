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

        // Clean accused: trim strings, handle cleared accused
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

                // If name is cleared/empty:
                // Rule: Ensure that a department cannot be entered or selected without an accused person's name.
                // If the accused name is cleared, automatically clear the associated department selection and its ID.
                if ($name === '') {
                    // If no other data either, completely drop it
                    if ($phone === '' && $pen === '' && !$hasAddr) {
                        continue;
                    }
                    $cleanedAccused[] = [
                        'entity_type' => $acc['entity_type'] ?? 'Person',
                        'name' => null,
                        'designation_id' => null,
                        'department_id' => null,
                        'phone' => $phone !== '' ? $phone : null,
                        'pen_number' => $pen !== '' ? $pen : null,
                        'addresses' => $acc['addresses'] ?? [],
                    ];
                } else {
                    $cleanedAccused[] = [
                        'entity_type' => $acc['entity_type'] ?? 'Person',
                        'name' => $name,
                        'designation_id' => $desig,
                        'department_id' => $dept,
                        'phone' => $phone !== '' ? $phone : null,
                        'pen_number' => $pen !== '' ? $pen : null,
                        'addresses' => $acc['addresses'] ?? [],
                    ];
                }
            }
            $this->merge(['accused' => $cleanedAccused]);
        }
    }

    public function rules(): array
    {
        $isInward = $this->boolean('is_inward_entry');

        $rules = [
            'receipt_no' => 'required|unique:petitions,receipt_no',
            'date_of_petition_received' => 'required|date|before_or_equal:today',
            'mode_of_petition_received' => 'required_without:duplicate_link_number',
            'proposed_action' => 'nullable|string',
            'transfer_remarks' => 'nullable|string',
            'seat_id' => $isInward ? 'required|exists:seats,seat_id' : 'nullable|exists:seats,seat_id',
            'is_inward_entry' => 'nullable|boolean',
            'unit_id' => 'nullable|exists:units,unit_id',
            'date_of_petition_received_at_unit' => 'nullable|date|before_or_equal:today',
            'mode_others' => 'nullable|string',
            'linked_petition_id' => 'nullable|exists:petitions,petition_id',
            'evidence_files' => 'nullable|array',
            'evidence_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];

        if ($isInward) {
            $rules['nature_of_petition'] = 'nullable|string';
            $rules['description'] = 'nullable|string';
            $rules['complainants'] = 'nullable|array';
            $rules['complainants.*.name'] = 'nullable|string|max:255';
            $rules['complainants.*.phone'] = 'nullable|string';
            $rules['complainants.*.email'] = 'nullable|email';
            $rules['complainants.*.addresses'] = 'nullable|array';
            $rules['accused'] = 'nullable|array';
            $rules['accused.*.entity_type'] = 'nullable|in:Person,Firm';
            $rules['accused.*.name'] = 'nullable|string|max:255';
            $rules['accused.*.designation_id'] = 'nullable|exists:designation_lists,id';
            $rules['accused.*.department_id'] = 'nullable|required_with:accused.*.name|exists:department_lists,id';
            $rules['accused.*.phone'] = 'nullable|string';
            $rules['accused.*.pen_number'] = 'nullable|digits_between:6,7';
            $rules['accused.*.addresses'] = 'nullable|array';
        } else {
            $rules['nature_of_petition'] = 'required|string';
            $rules['description'] = 'required|string';
            $rules['complainants'] = 'nullable|array';
            $rules['complainants.*.name'] = 'nullable|string|max:255';
            $rules['complainants.*.phone'] = 'nullable|string';
            $rules['complainants.*.email'] = 'nullable|email';
            $rules['complainants.*.addresses'] = 'nullable|array';
            $rules['accused'] = 'nullable|array';
            $rules['accused.*.entity_type'] = 'nullable|in:Person,Firm';
            $rules['accused.*.name'] = 'nullable|string|max:255';
            $rules['accused.*.designation_id'] = 'nullable|exists:designation_lists,id';
            $rules['accused.*.department_id'] = 'nullable|required_with:accused.*.name|exists:department_lists,id';
            $rules['accused.*.phone'] = 'nullable|string';
            $rules['accused.*.pen_number'] = 'nullable|digits_between:6,7';
            $rules['accused.*.addresses'] = 'nullable|array';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'receipt_no' => 'receipt number',
            'date_of_petition_received' => 'date of receipt',
            'nature_of_petition' => 'nature of petition',
            'mode_of_petition_received' => 'mode of petition',
            'description' => 'description',
            'seat_id' => 'concerned seat',
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
            'accused.*.department_id.required_with' => 'The department field is mandatory when suspect name is entered.',
            'accused.*.department_id.exists' => 'The selected suspect department is invalid.',
            'accused.*.designation_id.exists' => 'The selected suspect designation is invalid.',
            'accused.*.pen_number.digits_between' => 'The PEN number must be either 6 or 7 digits.',
            'seat_id.required' => 'Concerned seat is mandatory for inward registration.',
        ];
    }
}

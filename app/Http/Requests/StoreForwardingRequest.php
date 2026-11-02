<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreForwardingRequest extends FormRequest
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
            'petition_id' => 'required|exists:petitions,petition_id',
            'action' => 'required|in:Forward_To_Unit,Sent_to_Govt,Sent_to_SPC,Internal_Vigilance,Sent_to_Internal_Vigilance,Close',
            'govt_recommendation_type' => 'required_if:action,Sent_to_Govt|nullable|in:17 A Sanction,Report with Recommendations,No Recommendations',
            'director_remarks' => 'required|string',
            'to_unit_id' => 'required_if:action,Forward_To_Unit|nullable|exists:units,unit_id',
            'directorate_order_number' => 'required_if:action,Sent_to_Govt,Sent_to_SPC,Internal_Vigilance,Sent_to_Internal_Vigilance,Close|nullable|string|max:255',
            'final_order_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'forwarded_date' => [
                'required',
                'date',
                'before_or_equal:today',
                function ($attribute, $value, $fail) {
                    $petitionId = \App\Services\HashidService::decode($this->petition_id) ?? $this->petition_id;
                    $petition = \App\Models\Petition::find($petitionId);
                    if ($this->file_created_date) {
                        $fileDate = \Carbon\Carbon::parse($this->file_created_date)->format('Y-m-d');
                        if ($value < $fileDate) {
                            $fail('Action date cannot be earlier than file date (' . \Carbon\Carbon::parse($this->file_created_date)->format('d-m-Y') . ').');
                        }
                    } elseif ($petition && $petition->date_of_petition_received) {
                        $regDate = \Carbon\Carbon::parse($petition->date_of_petition_received)->format('Y-m-d');
                        if ($value < $regDate) {
                            $fail('Action date cannot be earlier than petition registration date (' . \Carbon\Carbon::parse($petition->date_of_petition_received)->format('d-m-Y') . ').');
                        }
                    }
                },
            ],
            'file_no' => 'required|string|unique:petitions,file_no,' . $this->petition_id . ',petition_id',
            'file_created_date' => 'nullable|date|before_or_equal:today',
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
            'forwarded_date.before_or_equal' => 'Forwarded date cannot be in the future.',
            'file_created_date.before_or_equal' => 'File created date cannot be in the future.',
            'to_unit_id.required_if' => 'Please select a unit to forward the petition to.',
            'govt_recommendation_type.required_if' => 'Recommendation Type is required.',
        ];
    }
}

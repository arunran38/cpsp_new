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
            'action' => 'required|in:Forward_To_Unit,Sent_to_Govt,Close',
            'director_remarks' => 'required|string',
            'to_unit_id' => 'required_if:action,Forward_To_Unit|nullable|exists:units,unit_id',
            'final_order_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'forwarded_date' => 'required|date|before_or_equal:today',
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
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDecisionRequest extends FormRequest
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
            'final_decision' => 'required|in:VC,VE,PE,SC,CV,IV,Closed,Sent to Govt,Sent to SPC,ICell',
            'govt_recommendation_type' => 'required_if:final_decision,Sent to Govt|nullable|in:17 A Sanction,Report with Recommendations,No Recommendations',
            'directorate_order_number' => 'nullable|string|max:255',
            'final_remarks' => 'nullable|string',
            'final_order_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'decision_date' => 'required|date|before_or_equal:today',
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
            'decision_date.before_or_equal' => 'Decision date cannot be in the future.',
            'govt_recommendation_type.required_if' => 'Recommendation Type is required.',
        ];
    }
}


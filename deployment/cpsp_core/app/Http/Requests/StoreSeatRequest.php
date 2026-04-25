<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_ids' => 'required|array',
            'unit_ids.*' => 'exists:units,unit_id',
            'seat_name' => 'required|string|max:255|unique:seats,seat_name',
            'is_active' => 'boolean',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $seatId = $this->route('seat');
        return [
            'unit_ids' => 'required|array',
            'unit_ids.*' => 'exists:units,unit_id',
            'seat_name' => 'required|string|max:255|unique:seats,seat_name,' . $seatId . ',seat_id',
            'is_active' => 'boolean',
        ];
    }
}

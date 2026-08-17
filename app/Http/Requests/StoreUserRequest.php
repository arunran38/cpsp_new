<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => 'required|string|max:100',
            'pen' => 'required|string|max:7|unique:users,pen|regex:/^[0-9]{6,7}$/',
            'email' => 'required|string|email|max:255|unique:users,email',
            'mobile_number' => 'required|string|max:10|regex:/^[0-9]{10}$/',
            'role' => 'required|string|max:255',
            'user_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'designation' => 'required|string|max:255',
            'other_designation' => 'required_if:designation,Others|nullable|string|max:255',
            'password' => 'required|string|min:8',
        ];
    }
}

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
            'name' => 'required|string|max:255',
            'pen' => 'required|string|max:255|unique:users,pen',
            'email' => 'required|string|email|max:255|unique:users,email',
            'mobile_number' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'user_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'designation' => 'required|string|max:255',
            'other_designation' => 'required_if:designation,Others|nullable|string|max:255',
            'password' => 'required|string|min:3',
        ];
    }
}

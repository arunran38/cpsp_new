<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        // Decrypt the ID from the route for the unique rule
        $userId = null;
        try {
            $userId = decrypt($this->route('user'));
        } catch (\Exception $e) {
            // Handle cases where decryption fails if necessary
        }

        return [
            'name' => 'required|string|max:255',
            'pen' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $userId . ',user_id',
            'mobile_number' => 'nullable|string|max:255',
            'role' => 'required|string|max:255',
            'user_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'designation' => 'required|string|max:255',
            'other_designation' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Transferred',
            'password' => 'nullable|string|min:3',
        ];
    }
}

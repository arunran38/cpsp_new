<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

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
        $userParam = $this->route('user');
        $userId = $userParam instanceof User
            ? $userParam->user_id
            : (is_numeric($userParam) ? $userParam : decrypt($userParam));

        return [
            'name' => 'required|string|max:100',
            'pen' => ['required', 'string', 'max:7', 'regex:/^[0-9]{6,7}$/', Rule::unique('users', 'pen')->ignore($userId, 'user_id')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId, 'user_id')],
            'mobile_number' => 'required|string|max:10|regex:/^[0-9]{10}$/',
            'user_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'designation' => 'required|string|max:255',
            'other_designation' => 'required_if:designation,Others|nullable|string|max:255',
            'status' => 'required|in:Active,Transferred',
            'password' => 'nullable|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the user\'s full name.',
            'name.max' => 'The name may not be longer than 100 characters.',
            'pen.required' => 'Please enter a PEN.',
            'pen.unique' => 'This PEN is already registered.',
            'pen.regex' => 'The PEN must be 6 or 7 digits.',
            'email.required' => 'Please enter an email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'mobile_number.required' => 'Please enter a mobile number.',
            'mobile_number.regex' => 'The mobile number must be 10 digits.',
            'user_photo.image' => 'The profile photo must be an image.',
            'user_photo.mimes' => 'The profile photo must be a JPG or PNG image.',
            'user_photo.max' => 'The profile photo may not be larger than 2 MB.',
            'designation.required' => 'Please select a designation.',
            'other_designation.required_if' => 'Please enter the other designation.',
            'status.required' => 'Please select an account status.',
            'status.in' => 'Please select a valid account status.',
            'password.min' => 'The new password must be at least 8 characters.',
        ];
    }
}

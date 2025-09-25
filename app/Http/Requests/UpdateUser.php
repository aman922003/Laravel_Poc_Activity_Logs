<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Here you can add permission checks (e.g., only admins)
        // For now, allow everyone who is authenticated
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Get user ID from route (PUT /users/{id})
        $userId = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId), // email must be unique except for current user
            ],
            'password' => [
                'nullable',     // not required unless updating
                'string',
                'min:4',        // at least 4 characters
                'regex:/[A-Z]/',    // at least one uppercase
                'regex:/[a-z]/',    // at least one lowercase
                'regex:/[0-9]/',    // at least one digit
                'regex:/[@$!%*#?&]/' // at least one special char
            ],
            'contact_number' => [
                'required',
                'digits:10', // must be 10 digits
            ],
            'address' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email is already in use.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
            'contact_number.required' => 'Contact number is required.',
            'contact_number.digits_between' => 'Contact number must be between 10 and 15 digits.',
            'address.required' => 'Address is required.',
        ];
    }
}

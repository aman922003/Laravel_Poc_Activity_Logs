<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For now, allow all authenticated users to create
        // You can add role-based check here if needed
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email', // email must be unique
            ],
            'password' => [
                'required',
                'string',
                'min:4',            // at least 4 characters
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
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email is already taken.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
            'contact_number.required' => 'Contact number is required.',
            'contact_number.digits_between' => 'Contact number must be between 10 and 15 digits.',
            'address.required' => 'Address is required.',
        ];
    }
}

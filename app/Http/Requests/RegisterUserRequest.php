<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * These rules ensure that incoming registration data is valid.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:users',
            'password'       => 'required|string|min:4',
            'contact_number' => 'nullable|digits:10', // exactly 10 digits
            'address'        => 'nullable|string|max:255',
        ];
    }
}

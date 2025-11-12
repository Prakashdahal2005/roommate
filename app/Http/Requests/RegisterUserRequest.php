<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    // public function authorize(): bool
    // {
    //     return false;
    // }
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'age' => 'required|integer|min:18|max:100',
            'terms' => 'accepted',
        ];
    }
}

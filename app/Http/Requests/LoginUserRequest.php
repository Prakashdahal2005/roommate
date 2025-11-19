<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules()
{
    $adminEmail = "admin@example.com";

    if ($this->input('email') === $adminEmail) {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    return [
        'email' => 'required|email|exists:users,email',
        'password' => 'required|string|min:8',
    ];
}

}

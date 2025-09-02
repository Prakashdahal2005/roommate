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
             'display_name' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|max:2048', // max 2MB
            'bio' => 'nullable|string|max:1000',
            'age' => 'required|integer|min:18|max:100',
            'gender' => 'required|in:male,female,other',
            'budget_min' => 'required|integer|min:0',
            'budget_max' => 'required|integer|gte:budget_min',
            'move_in_date' => 'nullable|date|after_or_equal:today',
            'cleanliness' => 'required|in:very_clean,clean,average,messy',
            'schedule' => 'required|in:morning_person,night_owl,flexible',
            'smokes' => 'sometimes|boolean',
            'pets_ok' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ];
    }
}

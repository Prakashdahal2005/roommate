<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     return false;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'display_name' => 'sometimes|string|max:255',
            'profile_picture' => 'sometimes|nullable|image|max:2048',
            'bio' => 'sometimes|nullable|string',
            'gender' => 'sometimes|in:male,female,other',
            'budget_min' => 'sometimes|numeric|min:0',
            'budget_max' => 'sometimes|numeric|gte:budget_min',
            'move_in_date' => 'sometimes|date',
            'cleanliness' => 'sometimes|string',
            'schedule' => 'sometimes|string',
            'smokes' => 'sometimes|boolean',
            'pets_ok' => 'sometimes|boolean',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // must be true or validation won't run
    }

    /**
     * Define validation rules.
     */
    public function rules(): array
    {
        return [
            'display_name'    => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|max:2048',
            'bio'             => 'nullable|string|max:1000',
            'age'             => 'nullable|integer|min:0',
            'gender'          => 'nullable|in:male,female,other',
            'budget_min'      => 'nullable|integer|min:0',
            'budget_max'      => 'nullable|integer|gte:budget_min',
            'move_in_date'    => 'nullable|date|after_or_equal:today',
            'cleanliness'     => 'nullable|string|max:255',
            'schedule'        => 'nullable|string|max:255',
            'smokes'          => 'nullable|boolean',
            'pets_ok'         => 'nullable|boolean',
        ];
    }

    /**
     * Add custom validation after basic rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Get all input fields except CSRF token
            $data = $this->except(['_token']);

            // Determine if any field has a meaningful value
            $hasAnyValue = collect($data)->contains(function ($value) {
                // File input check
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    return true;
                }

                // Checkbox or other truthy values
                if (is_bool($value) || $value === '0' || $value === '1') {
                    return true;
                }

                // Any non-empty string or number
                return filled($value);
            });

            // If no field filled → validation error
            if (! $hasAnyValue) {
                $validator->errors()->add('profile', 'Please fill at least one field before submitting.');
            }
        });
    }
}

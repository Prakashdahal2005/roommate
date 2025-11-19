<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // allow authenticated users to update
    }

    public function rules(): array
    {
        return [
            'display_name'     => 'sometimes|nullable|string|max:255',
            'profile_picture'  => 'sometimes|nullable|image|max:2048',
            'bio'              => 'sometimes|nullable|string',
            'gender'           => 'sometimes|nullable|in:male,female,other',
            'budget_min'       => 'sometimes|nullable|numeric|min:0',
            'budget_max'       => 'sometimes|nullable|numeric|gte:budget_min',
            'move_in_date'     => 'sometimes|nullable|date',
            'cleanliness'      => 'sometimes|nullable|in:very_clean,clean,average,messy',
            'schedule'         => 'sometimes|nullable|in:morning_person,night_owl,flexible',
            'smokes'           => 'sometimes|nullable|boolean',
            'pets_ok'          => 'sometimes|nullable|boolean',
            'latitude'         => 'sometimes|nullable|numeric|between:-90,90',
            'longitude'        => 'sometimes|nullable|numeric|between:-180,180',
            'is_active'        => 'sometimes|nullable|boolean',
            'completion_score' => 'sometimes|nullable|numeric|min:0|max:1',
            'cluster_id'       => 'sometimes|nullable|exists:clusters,id',
        ];
    }
}

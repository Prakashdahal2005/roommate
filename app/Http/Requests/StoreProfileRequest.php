<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreProfileRequest extends FormRequest
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
        'display_name' => 'nullable|string|max:255|required_without_all:profile_picture,bio,gender,budget_min,budget_max,move_in_date,cleanliness,schedule,smokes,pets_ok',
        'profile_picture' => 'nullable|image|max:2048|required_without_all:display_name,bio,gender,budget_min,budget_max,move_in_date,cleanliness,schedule,smokes,pets_ok',
        'bio' => 'nullable|string|max:1000|required_without_all:display_name,profile_picture,gender,budget_min,budget_max,move_in_date,cleanliness,schedule,smokes,pets_ok',
        'gender' => 'nullable|in:male,female,other|required_without_all:display_name,profile_picture,bio,budget_min,budget_max,move_in_date,cleanliness,schedule,smokes,pets_ok',
        'budget_min' => 'nullable|integer|min:0|required_without_all:display_name,profile_picture,bio,gender,budget_max,move_in_date,cleanliness,schedule,smokes,pets_ok',
        'budget_max' => 'nullable|integer|gte:budget_min|required_without_all:display_name,profile_picture,bio,gender,budget_min,move_in_date,cleanliness,schedule,smokes,pets_ok',
        'move_in_date' => 'nullable|date|after_or_equal:today|required_without_all:display_name,profile_picture,bio,gender,budget_min,budget_max,cleanliness,schedule,smokes,pets_ok',
        'cleanliness' => 'nullable|in:very_clean,clean,average,messy|required_without_all:display_name,profile_picture,bio,gender,budget_min,budget_max,move_in_date,schedule,smokes,pets_ok',
        'schedule' => 'nullable|in:morning_person,night_owl,flexible|required_without_all:display_name,profile_picture,bio,gender,budget_min,budget_max,move_in_date,cleanliness,smokes,pets_ok',
        'smokes' => 'nullable|boolean|required_without_all:display_name,profile_picture,bio,gender,budget_min,budget_max,move_in_date,cleanliness,schedule,pets_ok',
        'pets_ok' => 'nullable|boolean|required_without_all:display_name,profile_picture,bio,gender,budget_min,budget_max,move_in_date,cleanliness,schedule,smokes',
    ];
}


   
}

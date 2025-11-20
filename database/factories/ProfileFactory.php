<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    public function definition(): array
    {
        // Generate realistic budget range where min < max
        $budgetMin = $this->faker->numberBetween(500, 2500);
        $budgetMax = $this->faker->numberBetween($budgetMin + 100, 4000);

        // // Generate profile picture
        // $imageContents = file_get_contents('https://i.pravatar.cc/200?img=' . rand(1, 70));
        // $imageName = 'profiles/' . uniqid() . '.jpg';
        // Storage::disk('public')->put($imageName, $imageContents);

        return [
            'user_id' => User::factory(),
            'display_name' => $this->faker->userName(),
            'profile_picture' => null,
            'bio' => $this->faker->paragraphs($this->faker->numberBetween(1, 3), true),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'budget_min' => $budgetMin,
            'budget_max' => $budgetMax,
            'cleanliness' => $this->faker->randomElement(['very_clean', 'clean', 'average', 'messy']),
            'schedule' => $this->faker->randomElement(['morning_person', 'night_owl', 'flexible']),
            'smokes' => $this->faker->boolean(25),
            'pets_ok' => $this->faker->boolean(40),
        ];
    }
}

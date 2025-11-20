<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Carbon;

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

        // Kathmandu approximate bounding box
        $latMin = 27.65;
        $latMax = 27.75;
        $lngMin = 85.30;
        $lngMax = 85.35;

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
            'move_in_lat' => $this->faker->randomFloat(6, $latMin, $latMax),
            'move_in_lng' => $this->faker->randomFloat(6, $lngMin, $lngMax),
            'move_in_date' => Carbon::now()->addDays($this->faker->numberBetween(0, 30))->toDateString(),
        ];
    }
}

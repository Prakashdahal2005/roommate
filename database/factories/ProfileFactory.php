<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    public function definition(): array
    {
        // Generate a realistic budget range where min is always less than max
        $budgetMin = $this->faker->numberBetween(500, 2500);
        $budgetMax = $this->faker->numberBetween($budgetMin + 100, 4000);

        return [
            'user_id' => User::factory(), // Creates a new User for each Profile
            'display_name' => $this->faker->userName(),
            'profile_picture' => $this->faker->optional(0.7)->imageUrl(200, 200, 'people', true), // 70% chance of having a picture
            'bio' => $this->faker->optional(0.8)->paragraphs($this->faker->numberBetween(1, 3), true), // 80% chance of having a bio
            'age' => $this->faker->numberBetween(18, 75),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            
            // Living Preferences - Matches your migration exactly
            'budget_min' => $budgetMin,
            'budget_max' => $budgetMax,
            'move_in_date' => $this->faker->optional(0.9)->dateTimeBetween('+1 week', '+1 year')?->format('Y-m-d'), // 90% chance of having a date
            
            // Lifestyle - Matches your ENUM values exactly
            'cleanliness' => $this->faker->randomElement(['very_clean', 'clean', 'average', 'messy']),
            'schedule' => $this->faker->randomElement(['morning_person', 'night_owl', 'flexible']),
            'smokes' => $this->faker->boolean(25), // 25% chance of being true
            'pets_ok' => $this->faker->boolean(40), // 40% chance of being true
            
            // Status
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            
            // cluster_id is intentionally omitted since it will be assigned dynamically later
        ];
    }
}
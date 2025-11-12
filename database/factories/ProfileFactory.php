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
        // Decide completeness level: 70% mostly complete, 20% partially, 10% minimal
        $completenessRoll = $this->faker->numberBetween(1, 100);
        if ($completenessRoll <= 70) {
            $completeness = 'mostly';
        } elseif ($completenessRoll <= 90) {
            $completeness = 'partial';
        } else {
            $completeness = 'minimal';
        }

        // Helper to decide if a field should be filled based on completeness
        $fillField = function($chanceMostly, $chancePartial, $chanceMinimal) use ($completeness) {
            $chance = match($completeness) {
                'mostly' => $chanceMostly,
                'partial' => $chancePartial,
                'minimal' => $chanceMinimal,
            };
            return $this->faker->boolean($chance);
        };

        // Generate realistic budget range where min < max
        $budgetMin = $this->faker->numberBetween(500, 2500);
        $budgetMax = $this->faker->numberBetween($budgetMin + 100, 4000);

        return [
            'user_id' => User::factory(), // always create a user

            'display_name' => $fillField(90, 60, 20) ? $this->faker->userName() : null,
            'profile_picture' => $fillField(70, 40, 10) ? $this->faker->imageUrl(200, 200, 'people', true) : null,
            'bio' => $fillField(70, 40, 10) ? $this->faker->paragraphs($this->faker->numberBetween(1, 3), true) : null,
            'gender' => $fillField(90, 60, 20) ? $this->faker->randomElement(['male', 'female', 'other']) : null,

            'budget_min' => $fillField(90, 60, 20) ? $budgetMin : null,
            'budget_max' => $fillField(90, 60, 20) ? $budgetMax : null,
            'move_in_date' => $fillField(80, 50, 10) ? $this->faker->dateTimeBetween('+1 week', '+1 year')->format('Y-m-d') : null,

            'cleanliness' => $fillField(80, 50, 10) ? $this->faker->randomElement(['very_clean', 'clean', 'average', 'messy']) : null,
            'schedule' => $fillField(80, 50, 10) ? $this->faker->randomElement(['morning_person', 'night_owl', 'flexible']) : null,
            'smokes' => $fillField(80, 50, 10) ? $this->faker->boolean(25) : null,
            'pets_ok' => $fillField(80, 50, 10) ? $this->faker->boolean(40) : null,

            'is_active' => $this->faker->boolean(80),
        ];
    }
}

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
        // Decide completeness level: 70% mostly complete, 20% partially, 10% minimal
        $completenessRoll = $this->faker->numberBetween(1, 100);
        $completeness = match (true) {
            $completenessRoll <= 70 => 'mostly',
            $completenessRoll <= 90 => 'partial',
            default => 'minimal',
        };

        // Helper to decide if a field should be filled based on completeness
        $fillField = function ($chanceMostly, $chancePartial, $chanceMinimal) use ($completeness) {
            $chance = match ($completeness) {
                'mostly' => $chanceMostly,
                'partial' => $chancePartial,
                'minimal' => $chanceMinimal,
            };
            return $this->faker->boolean($chance);
        };

        // Generate realistic budget range where min < max
        $budgetMin = $this->faker->numberBetween(500, 2500);
        $budgetMax = $this->faker->numberBetween($budgetMin + 100, 4000);

        // Generate profile picture if applicable
        $profile_picture = null;
        $fillField = fn() => $this->faker->boolean(70);

        if ($fillField()) {
            $imageContents = file_get_contents('https://i.pravatar.cc/200?img=' . rand(1, 70));
            $imageName = 'profiles/' . uniqid() . '.jpg';
            Storage::disk('public')->put($imageName, $imageContents);
            $profile_picture = $imageName;
        } else {
            $profile_picture = null;
        }


        return [
            'user_id' => User::factory(),
            'display_name' => $fillField(90, 60, 20) ? $this->faker->userName() : null,
            'profile_picture' => $profile_picture,
            'bio' => $fillField(70, 40, 10) ? $this->faker->paragraphs($this->faker->numberBetween(1, 3), true) : null,
            'gender' => $fillField(90, 60, 20) ? $this->faker->randomElement(['male', 'female', 'other']) : null,
            'budget_min' => $fillField(95, 60, 20) ? $budgetMin : null,
            'budget_max' => $fillField(95, 60, 20) ? $budgetMax : null,
            'cleanliness' => $fillField(80, 50, 10) ? $this->faker->randomElement(['very_clean', 'clean', 'average', 'messy']) : null,
            'schedule' => $fillField(80, 50, 10) ? $this->faker->randomElement(['morning_person', 'night_owl', 'flexible']) : null,
            'smokes' => $fillField(80, 50, 10) ? $this->faker->boolean(25) : null,
            'pets_ok' => $fillField(80, 50, 10) ? $this->faker->boolean(40) : null,
            'is_active' => $this->faker->boolean(80),
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'display_name',
        'profile_picture',
        'bio',
        'gender',
        'budget_min',
        'budget_max',
        'cleanliness',
        'schedule',
        'smokes',
        'pets_ok',
        'move_in_date',
        'move_in_lat',
        'move_in_lng',
        'completion_score',
    ];

    protected $casts = [
        'smokes' => 'boolean',
        'pets_ok' => 'boolean',
        'completion_score' => 'float',
        'move_in_date' => 'date',
        'move_in_lat' => 'float',
        'move_in_lng' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($profile) {
            $profile->completion_score = $profile->calculateCompletionScore();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calculateCompletionScore(): float
    {
        // Adjusted weights to include location and move-in date
        $rawWeights = [
            0 => 0.15,  // age
            1 => 0.15,  // gender
            2 => 0.10,  // budget_min
            3 => 0.10,  // budget_max
            4 => 0.10,  // cleanliness
            5 => 0.05,  // schedule
            6 => 0.03,  // smokes
            7 => 0.02,  // pets_ok
            8 => 0.10,  // move_in_date
            9 => 0.10,  // move_in_lat/lng (location)
            10 => 0.005, // display_name
            11 => 0.005, // bio
            12 => 0.005, // profile_picture
        ];

        $fields = [
            'age',
            'gender',
            'budget_min',
            'budget_max',
            'cleanliness',
            'schedule',
            'smokes',
            'pets_ok',
            'move_in_date',
            'move_in_lat', // we’ll consider lat/lng together as a single "location" score
            'display_name',
            'bio',
            'profile_picture',
        ];

        $score = 0;

        foreach ($fields as $index => $field) {
            $value = null;

            if ($field === 'age') {
                $value = $this->user ? $this->user->age : null;
            } elseif ($field === 'move_in_lat') {
                // Consider both lat and lng together for scoring
                $value = ($this->move_in_lat !== null && $this->move_in_lng !== null) ? 1 : null;
            } else {
                $value = $this->$field ?? null;
            }

            if ($value !== null && $value !== '') {
                $score += $rawWeights[$index];
            }
        }

        return round(min(1, max(0, $score)), 3);
    }
}

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
        'completion_score',
    ];

    protected $casts = [
        'smokes' => 'boolean',
        'pets_ok' => 'boolean',
        'completion_score' => 'float',
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
        $rawWeights = [
            0 => 0.20, // age
            1 => 0.25, // gender
            2 => 0.15, // budget_min
            3 => 0.15, // budget_max
            4 => 0.15, // cleanliness
            5 => 0.05, // schedule
            6 => 0.03, // smokes
            7 => 0.02, // pets_ok
            8 => 0.005, // display_name (tiny effect)
            9 => 0.005, // bio (tiny effect)
            10 => 0.005, // profile_picture (tiny effect)
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
            'display_name',
            'bio',
            'profile_picture',
        ];

        $score = 0;

        foreach ($fields as $index => $field) {
            $value = null;

            if ($field === 'age') {
                $value = $this->user ? $this->user->age : null;
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

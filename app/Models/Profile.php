<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
    ];
    protected $casts = [
        'smokes' => 'boolean',
        'pets_ok' => 'boolean',
        'is_active' => 'boolean',
        'completion_score' => 'float',
    ];

    protected function completionScore(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (float) $value,
            set: fn($value) => max(0, min(1, (float) $value)),
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

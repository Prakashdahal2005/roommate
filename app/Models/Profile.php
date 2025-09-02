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
        'age',
        'gender',
        'budget_min',
        'budget_max',
        'move_in_date',
        'cleanliness',
        'schedule',
        'smokes',
        'pets_ok',
        'is_active'
    ];
    protected $casts = [
    'move_in_date' => 'date',   // this will automatically convert string to Carbon
    'smokes' => 'boolean',
    'pets_ok' => 'boolean',
    'is_active' => 'boolean',
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

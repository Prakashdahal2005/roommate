<?php
namespace App\Contracts;
use App\Models\Profile;

interface RoommateMatchServiceInterface
{
    public function findMatches(Profile $user,int $limit=50):array;
}
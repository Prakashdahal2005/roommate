<?php
namespace App\Contracts;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface RoommateMatchServiceInterface
{
    public function findMatches(Profile $user,int $limit=50,?Request $request=null):Collection;
}
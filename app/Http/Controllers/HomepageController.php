<?php

namespace App\Http\Controllers;

use App\Contracts\RoommateMatchServiceInterface;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HomepageController extends Controller
{
        public function index(RoommateMatchServiceInterface $roommateMatchService)
    {
        if(Auth::check())
        {
            $profiles = $roommateMatchService->findMatches(Auth::user()->profile,50);
        }
        else
        {
            $profiles = Profile::inRandomOrder()
            ->limit(30)
            ->get();

        }
        return view('index',compact('profiles'));
    }
}

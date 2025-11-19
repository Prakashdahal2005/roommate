<?php

namespace App\Http\Controllers;

use App\Contracts\RoommateMatchServiceInterface;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomepageController extends Controller
{
    public function index(Request $request, RoommateMatchServiceInterface $roommateMatchService)
    {
        if (Auth::check()) {
            $profiles = $roommateMatchService->findMatches(Auth::user()->profile, 50, $request);
        } else {
            $profiles = $this->getGuestProfiles($request);
        }

        return view('index', compact('profiles'));
    }

    private function getGuestProfiles(Request $request)
    {
        $query = Profile::query()->with('user'); // ensure age is available

        // ----- FIXED: age filtering via user relation -----
        if ($request->filled('age_min')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('age', '>=', $request->age_min);
            });
        }
        
        if ($request->filled('age_max')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('age', '<=', $request->age_max);
            });
        }

        // gender is stored on profile — keep as-is
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // budget filtering is also from profile — keep as-is
        if ($request->filled('budget_max')) {
            $query->where('budget_max', '<=', $request->budget_max);
        }

        return $query->inRandomOrder()->limit(30)->get();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Contracts\RoommateMatchServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show(Profile $profile)
    {
        return view('profile.show',compact('profile')); 
    }
    public function edit()
    {
        $profile = Auth::user()->profile;
        return view('profile.edit', compact('profile'));
    }
    public function update(Request $request)
    {
        $profile = Auth::user()->profile;

        $data = $request->validate([
            'display_name' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|max:2048',
            'bio' => 'nullable|string',
            'age' => 'required|integer|min:18|max:100',
            'gender' => 'required|in:male,female,other',
            'budget_min' => 'required|numeric|min:0',
            'budget_max' => 'required|numeric|gte:budget_min',
            'move_in_date' => 'required|date',
            'cleanliness' => 'required|string',
            'schedule' => 'required|string',
            'smokes' => 'boolean',
            'pets_ok' => 'boolean',
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('profiles', 'public');
        }

        $profile->update($data);

        return redirect()->route('profile.show',$profile)->with('success', 'Profile updated successfully!');
    }
}

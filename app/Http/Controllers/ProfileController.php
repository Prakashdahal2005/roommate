<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use App\Contracts\RoommateMatchServiceInterface;
use App\Http\Requests\StoreProfileRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show(Profile $profile)
    {
        return view('profiles.show', compact('profile'));
    }


    public function edit()
    {
        $profile = Auth::user()->profile;
        return view('profiles.edit', compact('profile'));
    }
    public function update(UpdateProfileRequest $updateProfileRequest)
    {
        $profile = Auth::user()->profile;

        $data = $updateProfileRequest->validated();

        // Handle profile picture upload safely
        if ($updateProfileRequest->hasFile('profile_picture')) {
            $data['profile_picture'] = $updateProfileRequest->file('profile_picture')->store('profiles', 'public');
        }
        try{
            $profile->update($data);
            return redirect()->route('profiles.show', $profile)->with('success', 'Profile updated successfully!');
    }
    catch(Exception $e)
    {
        return back()->with('error', 'Error on updating profile');
    }
        }
        

        
}

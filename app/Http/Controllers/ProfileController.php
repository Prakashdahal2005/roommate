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
    public function create()
    {
        return view('profiles.create');
    }

    public function store(StoreProfileRequest $storeProfileRequest)
    {
        $data = $storeProfileRequest->validated();
        // Handle profile picture upload
        if ($storeProfileRequest->hasFile('profile_picture')) {
            $data['profile_picture'] = $storeProfileRequest->file('profile_picture')->store('profiles', 'public');
        }

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $user->profile()->create($data);

            return redirect()->route('home')->with('profile-create-success', 'Profile created successfully');
        } catch (Exception $e) {
            return back()->withErrors(['profile-creation' => 'Failed to create profile, please try again'])->withInput();
        }
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

        // Handle profile picture upload
        if ($data['profile_picture']) {
            $data['profile_picture'] = $updateProfileRequest->file('profile_picture')->store('profiles', 'public');
        }

        $profile->update($data);

        return redirect()->route('profiles.show', $profile)->with('success', 'Profile updated successfully!');
    }
}

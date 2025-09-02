<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $profiles = Profile::select('display_name', 'profile_picture')->get();
        return view('profile.index',compact('profiles'));
    }
    public function show(Profile $profile)
    {
        //
    }
    public function edit(Profile $profile)
    {
        //
    }
    public function update(Request $request, Profile $profile)
    {
        //
    }
    public function matches()
    {
        
    }
}

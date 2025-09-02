<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }
    public function register(RegisterUserRequest $request)
    {
        $data = $request->validated();
Log::info('Starting registration process', ['email' => $data['email']]);

DB::transaction(function() use ($data) {
    $user = User::create([
        'email' => $data['email'],
        'password' => bcrypt($data['password'])
    ]);
    Log::info('User created', ['user_id' => $user->id]);

    $profilePicturePath = null;
    if (isset($data['profile_picture'])) {
        $profilePicturePath = $data['profile_picture']->store('profiles', 'public');
    }

    // Provide default values for optional fields
    $user->profile()->create([
        'display_name' => $data['display_name'],
        'profile_picture' => $profilePicturePath,
        'bio' => $data['bio'] ?? null,
        'age' => $data['age'],
        'gender' => $data['gender'],
        'budget_min' => $data['budget_min'],
        'budget_max' => $data['budget_max'],
        'move_in_date' => $data['move_in_date'] ?? null,
        'cleanliness' => $data['cleanliness'],
        'schedule' => $data['schedule'],
        'smokes' => $data['smokes'] ?? false,
        'pets_ok' => $data['pets_ok'] ?? false,
        'is_active' => $data['is_active'] ?? true,
    ]);
    Log::info('Profile created for user', ['user_id' => $user->id]);
});

Log::info('Registration completed successfully');
return redirect()->route('login');
    }
    public function showLogin()
    {
        return view('auth.login');
    }
    public function login(LoginUserRequest $request)
    {
        $credentials = $request->only('email','password');
        if(Auth::attempt($credentials))
        {
            return redirect()->intended(route('home'));
        }
        return redirect()->back()->withErrors(['login'=>'Invalid credentials']);

    }
    public function logout()
    {
        Auth::logout();
        return redirect()->route('home');

    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
         $data = Arr::except($data, ['terms']);
        try{
                $user = User::create($data);
                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->route('profiles.create');
        }
        catch(Exception $e)
        {
            return back()->withErrors(['registration-error'=>'Failed to register, please try again later']);
        }

        
    }
    public function showLogin()
    {
        return view('auth.login');
    }
    public function login(LoginUserRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('home'));
        }
        return redirect()->back()->withErrors(['login' => 'Invalid credentials']);
    }
    public function logout()
    {
        Auth::logout();
        return redirect()->route('home');
    }
}

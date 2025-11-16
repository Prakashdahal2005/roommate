<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Register</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    @error('registration-error')
    <div style="color: red;">{{ $message }}</div>
    @enderror
    <div class="auth-wrapper">
        <div class="auth-card" role="main" aria-labelledby="register-title" style="position:relative;">
            <button id="theme-toggle" class="btn btn-outline" type="button" aria-label="Toggle theme" title="Toggle theme" style="position:absolute; top:12px; right:12px; padding:8px 12px; border-radius:10px; font-size:16px;">🌙</button>
            <div class="auth-side" aria-hidden="true">
                <h2>Find the right roommate</h2>
                <p>Create a profile, browse matches and start conversations — just like on the home page.</p>

                <ul style="margin-top:1rem; padding-left:1.15rem; color:#374151;">
                    <li>Create a public profile</li>
                    <li>Set preferences & filters</li>
                    <li>Message verified users</li>
                </ul>

                <p style="margin-top:1rem; font-size:.85rem; color:#6B7280;">Already have an account? Sign in instead.</p>
            </div>

            <div class="auth-form" role="form" aria-labelledby="register-title">
                <div class="auth-header">
                    <img src="{{ asset('images/logo.svg') }}" alt="Roommate logo" class="auth-logo" />
                    <h1 id="register-title" class="auth-title">Create account</h1>
                    <p class="auth-subtitle">Sign up to discover matches</p>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    @if (session('status'))
                    <div class="form-status" role="status" aria-live="polite">{{ session('status') }}</div>
                    @endif

                    <div class="form-group">
                        <label for="email" class="sr-only">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            class="input" placeholder="Email address" />
                        @error('email') <p class="profile-meta" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" type="password" name="password" required
                            class="input" placeholder="Password" />
                        @error('password') <p class="profile-meta" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="sr-only">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            class="input" placeholder="Confirm password" />
                    </div>
                    <div class="form-group">
                        <label for="age" class="sr-only">Age</label>
                        <input id="age" type="number" min="18" name="age" required 
                            class="input" placeholder="Age" />
                    </div>

                    <div class="form-actions" style="align-items:center;">
                        <div style="font-size:.90rem; color:#6B7280;">
                            <label style="display:flex; gap:8px; align-items:center;">
                                <input type="checkbox" name="terms" style="width:16px;height:16px;" {{ old('terms') ? 'checked' : '' }} />
                                <span>I agree to the <a href="#" class="helper-link">terms</a></span>
                            </label>
                        </div>

                        <div>
                            @if (Route::has('login'))
                            <a class="helper-link" href="{{ route('login') }}">Sign in</a>
                            @endif
                        </div>
                    </div>

                    <div style="margin-top:1rem; display:flex; gap:8px; justify-content:space-between; align-items:center;">
                        <button type="submit" class="btn btn-primary" style="min-width:140px;">Create account</button>
                        <a href="{{ url('/') }}" class="btn btn-outline" style="padding:10px 12px;">Back to home</a>
                    </div>


                    <div class="rule" aria-hidden="true" style="margin-top:1rem; visibility:hidden;"></div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
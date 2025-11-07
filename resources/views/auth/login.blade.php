<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card" role="main" aria-labelledby="login-title">
            <div class="auth-side" aria-hidden="true">
                <h2>Find the right roommate</h2>
                <p>Browse profiles, filter by preferences and message matches — just like the home page experience.</p>

                <ul style="margin-top:1rem; padding-left:1.15rem; color:#374151;">
                    <li>Verified profiles</li>
                    <li>Smart matching</li>
                    <li>Secure messaging</li>
                </ul>

                <p style="margin-top:1rem; font-size:.85rem; color:#6B7280;">New here? Create an account to get personalized suggestions.</p>
            </div>

            <div class="auth-form" role="form" aria-labelledby="login-title">
                <div class="auth-header">
                    <img src="{{ asset('images/logo.svg') }}" alt="Roommate logo" class="auth-logo" />
                    <h1 id="login-title" class="auth-title">Welcome back</h1>
                    <p class="auth-subtitle">Sign in to continue to Roommate</p>
                </div>

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    @if (session('status'))
                        <div class="form-status" role="status" aria-live="polite">{{ session('status') }}</div>
                    @endif

                    <div class="form-group">
                        <label for="email" class="sr-only">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="input" placeholder="Email address" />
                        @error('email')
                            <p class="profile-meta" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" type="password" name="password" required
                            class="input" placeholder="Password" />
                        @error('password')
                            <p class="profile-meta" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <label style="display:flex; gap:8px; align-items:center; font-size:.95rem;">
                            <input type="checkbox" name="remember" style="width:16px;height:16px;" {{ old('remember') ? 'checked' : '' }} />
                            <span style="color:#6B7280;">Remember me</span>
                        </label>

                        <div>
                            @if (Route::has('password.request'))
                                <a class="helper-link" href="{{ route('password.request') }}">Forgot?</a>
                            @endif
                        </div>
                    </div>

                    <div style="margin-top:1rem; display:flex; gap:8px; justify-content:space-between; align-items:center;">
                        <button type="submit" class="btn btn-primary" style="min-width:140px;">Sign in</button>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-outline" style="padding:10px 12px;">Create account</a>
                        @endif
                    </div>

                    <!-- Optional: keep divider for spacing balance -->
                    <div class="rule" aria-hidden="true" style="margin-top:1rem; visibility:hidden;"></div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

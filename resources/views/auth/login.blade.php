<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form action="{{ route('login.submit') }}" method="POST">
        @csrf
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

<<<<<<< Updated upstream
        <button type="submit">Login</button>
    </form>
    Dont have an account? <a href="{{ route('register') }}">Register here</a>
=======
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

                    <!-- social sign-in removed -->
                </form>
            </div>
        </div>
    </div>
>>>>>>> Stashed changes
</body>
</html>

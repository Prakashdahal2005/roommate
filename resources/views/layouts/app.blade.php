<head>
    <title>@yield('title')</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        nav { border-bottom: 1px solid #e5e7eb; background: #0A84FF; }
        nav .container { display: flex; align-items: center; justify-content: space-between; padding-block: .75rem; }
        nav ul { display: flex; gap: 12px; list-style: none; padding: 0; margin: 0; }
        nav a { text-decoration: none; color: #ffffff; font-weight: 600; }
        nav a:hover { color: #E5E5EA; }
        .toolbar { display:flex; justify-content:flex-end; padding: .5rem 0; }
        .theme-toggle { width: 40px; height: 40px; border-radius: 9999px; border: 1px solid #E5E5EA; background:#ffffff; color:#1C1C1E; display:inline-flex; align-items:center; justify-content:center; font-size: 18px; cursor:pointer; }
        .theme-toggle:hover { background:#F2F2F7; }
        [data-theme="dark"] .theme-toggle { background:#2C2C2E; color:#EBEBF5; border-color:#3A3A3C; }
        [data-theme="dark"] .theme-toggle:hover { background:#3A3A3C; }
    </style>
</head>
<nav>
    <div class="container">
        <ul>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('about') }}">About</a></li>
            <li><a href="{{ route('contact') }}">Contact</a></li>
        </ul>
        <ul>
            @guest
                <li><a href="{{ route('login') }}">Login</a></li>
                <li><a href="{{ route('register') }}">Register</a></li>
            @endguest
            @auth
                <li><strong><a href="{{ route('profile.edit') }}">Your profile</a></strong></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline">Logout ({{ auth()->user()->email ?? 'User' }})</button>
                    </form>
                </li>
            @endauth
        </ul>
    </div>
    
</nav>
<main class="container">
@yield('content')
</main>
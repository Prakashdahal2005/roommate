
<head>
    <title>@yield('title')</title>
</head>
<nav>
    <ul>
        @guest
            <!-- Show for guests only -->
            <li><a href="{{ route('login') }}">Login</a></li>
            <li><a href="{{ route('register') }}">Register</a></li>
         @endguest
         @auth
            <!-- Show for authenticated users only -->
            <li>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit">Logout ({{ $username }})</button>
                </form>
                <strong><a href="{{ route('profile.edit') }}">Your profile</a></strong>
            </li>
        @endauth
    </ul>
</nav>

@yield('content')
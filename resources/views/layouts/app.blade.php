<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Home') | Laravel POS</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header><div class="nav-wrap"><a class="brand" href="{{ route('home') }}">Laravel POS</a>
<nav aria-label="Main navigation">
    <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
    <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>About</a>
    <a href="{{ route('customers.index') }}" @if(request()->routeIs('customers.*')) aria-current="page" @endif>Customers</a>
    <a href="{{ route('users.index') }}" @if(request()->routeIs('users.*')) aria-current="page" @endif>Users</a>
    @auth
    <span class="signed-in">{{ auth()->user()->username }}</span>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-button" type="submit">Log out</button></form>
    @else
    <a href="{{ route('login') }}">Log in</a>
    <a href="{{ route('register') }}">Register</a>
    @endauth
</nav></div></header>
<main>
    @if(session('success'))
        <div class="success" role="status">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="error-summary" role="alert">{{ session('error') }}</div>
    @endif
    @if(session('status'))
        <div class="success" role="status">{{ session('status') }}</div>
    @endif
    @yield('content')
</main>
<footer>IT0049 · TFA8 · John Brent D. Villegas · TX33</footer>
</body>
</html>

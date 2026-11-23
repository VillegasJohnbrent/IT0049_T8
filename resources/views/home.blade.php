@extends('layouts.app')
@section('title', 'Home')
@section('content')
<p class="eyebrow">POINT OF SALE DIRECTORY</p>
<h1>Welcome to Laravel POS</h1>
<p class="intro">View, add and update customer and staff records.</p>
@guest
<p><a class="button" href="{{ route('login') }}">Log in</a> <a class="cancel-link" href="{{ route('register') }}">Create an account</a></p>
<p class="intro">Log in to manage customer and user records.</p>
@endguest
@auth
<div class="cards">
    <a class="card" href="{{ route('customers.index') }}"><span>Customers</span><strong>{{ $customerCount }}</strong><span>View customer directory &rarr;</span></a>
    <a class="card" href="{{ route('users.index') }}"><span>Users</span><strong>{{ $userCount }}</strong><span>View staff directory &rarr;</span></a>
</div>
@endauth
@endsection

@extends('layouts.app')
@section('title', 'Log in')
@section('content')
<h1>Log in</h1>
@if ($errors->any())
<div class="error-summary" role="alert">Please check the fields below.</div>
@endif
<form class="panel record-form" method="POST" action="{{ route('login') }}">
@csrf

<div class="field">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
    @error('email')<p class="field-error" id="email-error">{{ $message }}</p>@enderror
</div>
<div class="field">
    <label for="password">Password</label>
    <input id="password" name="password" type="password" required autocomplete="current-password" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
    @error('password')<p class="field-error" id="password-error">{{ $message }}</p>@enderror
</div>
<label class="remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Remember me</label>
<p><a href="{{ route('password.request') }}">Forgot password?</a> · <a href="{{ route('register') }}">Register</a></p>
<button type="submit">Log in</button>
</form>
@endsection

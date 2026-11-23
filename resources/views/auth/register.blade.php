@extends('layouts.app')
@section('title', 'Register')
@section('content')
<h1>Register</h1>
@if ($errors->any())
<div class="error-summary" role="alert">Please check the fields below.</div>
@endif
<form class="panel record-form" method="POST" action="{{ route('register') }}">
@csrf

<div class="field">
    <label for="full_name">Full name</label>
    <input id="full_name" name="full_name" type="text" value="{{ old('full_name') }}" required autocomplete="name" @error('full_name') aria-invalid="true" aria-describedby="full_name-error" @enderror>
    @error('full_name')<p class="field-error" id="full_name-error">{{ $message }}</p>@enderror
</div>
<div class="field">
    <label for="username">Username</label>
    <input id="username" name="username" type="text" value="{{ old('username') }}" required autocomplete="username" @error('username') aria-invalid="true" aria-describedby="username-error" @enderror>
    @error('username')<p class="field-error" id="username-error">{{ $message }}</p>@enderror
</div>
<div class="field">
    <label for="email">Email (lowercase)</label>
    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
    @error('email')<p class="field-error" id="email-error">{{ $message }}</p>@enderror
</div>
<div class="field">
    <label for="password">Password (at least 8 characters)</label>
    <input id="password" name="password" type="password" required autocomplete="new-password" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
    @error('password')<p class="field-error" id="password-error">{{ $message }}</p>@enderror
</div>
<div class="field">
    <label for="password_confirmation">Confirm password</label>
    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" @error('password_confirmation') aria-invalid="true" aria-describedby="password_confirmation-error" @enderror>
    @error('password_confirmation')<p class="field-error" id="password_confirmation-error">{{ $message }}</p>@enderror
</div>
<p><a href="{{ route('login') }}">Already registered? Log in</a></p>
<button type="submit">Register</button>
</form>
@endsection

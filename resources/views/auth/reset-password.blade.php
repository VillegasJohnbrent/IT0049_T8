@extends('layouts.app')
@section('title', 'Reset password')
@section('content')
<h1>Reset password</h1>
@if ($errors->any())
<div class="error-summary" role="alert">Please check the fields below.</div>
@endif
<form class="panel record-form" method="POST" action="{{ route('password.store') }}">
@csrf
<input type="hidden" name="token" value="{{ $request->route('token') }}">
<div class="field">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autocomplete="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
    @error('email')<p class="field-error" id="email-error">{{ $message }}</p>@enderror
</div>
<div class="field">
    <label for="password">New password</label>
    <input id="password" name="password" type="password" required autocomplete="new-password" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
    @error('password')<p class="field-error" id="password-error">{{ $message }}</p>@enderror
</div>
<div class="field">
    <label for="password_confirmation">Confirm password</label>
    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" @error('password_confirmation') aria-invalid="true" aria-describedby="password_confirmation-error" @enderror>
    @error('password_confirmation')<p class="field-error" id="password_confirmation-error">{{ $message }}</p>@enderror
</div>

<button type="submit">Reset password</button>
</form>
@endsection

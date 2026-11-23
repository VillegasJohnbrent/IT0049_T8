@extends('layouts.app')
@section('title', 'Send reset link')
@section('content')
<h1>Send reset link</h1>
@if ($errors->any())
<div class="error-summary" role="alert">Please check the fields below.</div>
@endif
<form class="panel record-form" method="POST" action="{{ route('password.email') }}">
@csrf

<div class="field">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
    @error('email')<p class="field-error" id="email-error">{{ $message }}</p>@enderror
</div>

<button type="submit">Send reset link</button>
</form>
@endsection

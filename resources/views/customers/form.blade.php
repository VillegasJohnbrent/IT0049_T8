@extends('layouts.app')
@section('title', $customer->exists ? 'Edit Customer' : 'Create Customer')
@section('content')
<h1>{{ $customer->exists ? 'Edit Customer' : 'Create Customer' }}</h1>
<p class="intro">Enter the customer's details below.</p>
@if ($errors->any())
<div class="error-summary" role="alert">Please check the fields below.</div>
@endif
<form class="panel record-form" method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}">
    @csrf
    @if ($customer->exists)
        @method('PUT')
    @endif
    <div class="field">
        <label for="full_name">Full name *</label>
        <input id="full_name" name="full_name" type="text" maxlength="255" value="{{ old('full_name', $customer->full_name) }}" required @error('full_name') aria-invalid="true" aria-describedby="full_name-error" @enderror>
        @error('full_name')<p class="field-error" id="full_name-error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="email">Email *</label>
        <input id="email" name="email" type="email" maxlength="255" value="{{ old('email', $customer->email) }}" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
        @error('email')<p class="field-error" id="email-error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="phone">Phone (optional)</label>
        <input id="phone" name="phone" type="tel" maxlength="30" value="{{ old('phone', $customer->phone) }}" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
        @error('phone')<p class="field-error" id="phone-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-actions">
        <button type="submit">{{ $customer->exists ? 'Save changes' : 'Add customer' }}</button>
        <a class="cancel-link" href="{{ route('customers.index') }}">Cancel</a>
    </div>
</form>
@endsection

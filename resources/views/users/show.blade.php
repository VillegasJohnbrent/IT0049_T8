@extends('layouts.app')
@section('title', 'User Details')
@section('content')
<h1>User Details</h1>
<section class="panel details">
<img class="avatar current-avatar" src="{{ $user->avatar ? Storage::disk('public')->url($user->avatar) : asset('images/avatar-placeholder.svg') }}" alt="Profile picture" width="80" height="80">
<dl>
<dt>Username</dt><dd>{{ $user->username ?? '—' }}</dd>
<dt>Full name</dt><dd>{{ $user->full_name ?? '—' }}</dd>
<dt>Email</dt><dd>{{ $user->email ?? '—' }}</dd>
</dl>
<a class="button" href="{{ route('users.edit', $user) }}">Edit</a>
<a class="cancel-link" href="{{ route('users.index') }}">Back to list</a>
</section>
@endsection

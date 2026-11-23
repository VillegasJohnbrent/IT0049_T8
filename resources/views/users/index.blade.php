@extends('layouts.app')
@section('title', 'Users')
@section('content')
<p class="eyebrow">DIRECTORY</p>
<h1>User Accounts</h1>
<p class="intro">{{ $users->count() }} records</p>
<p><a class="button" href="{{ route('users.create') }}">Create User</a></p>
<div class="table-wrap"><table><caption class="sr-only">Users directory</caption>
<thead><tr><th scope="col">Id</th><th scope="col">Avatar</th><th scope="col">Username</th><th scope="col">Full Name</th><th scope="col">Action</th></tr></thead>
<tbody>
@forelse ($users as $user)
<tr><td>{{ $user->id }}</td><td><img class="avatar" src="{{ $user->avatar ? Storage::disk('public')->url($user->avatar) : asset('images/avatar-placeholder.svg') }}" alt="Profile picture for {{ $user->full_name }}" width="48" height="48" loading="lazy"></td><td>{{ $user->username }}</td><td>{{ $user->full_name }}</td><td class="row-actions"><a href="{{ route('users.show', $user) }}">View</a><a href="{{ route('users.edit', $user) }}" aria-label="Edit {{ $user->full_name }}">Edit</a>
<form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user?');">
@csrf @method('DELETE')
<button class="delete-button" type="submit">Delete</button>
</form></td></tr>
@empty
<tr><td colspan="5">No users found.</td></tr>
@endforelse
</tbody></table></div>
@endsection

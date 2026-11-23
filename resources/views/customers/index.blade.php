@extends('layouts.app')
@section('title', 'Customers')
@section('content')
<p class="eyebrow">DIRECTORY</p>
<h1>Customers</h1>
<p class="intro">{{ $customers->count() }} records</p>
<p><a class="button" href="{{ route('customers.create') }}">Create Customer</a></p>
<div class="table-wrap"><table><caption class="sr-only">Customers directory</caption>
<thead><tr><th scope="col">Id</th><th scope="col">Full Name</th><th scope="col">Email</th><th scope="col">Phone</th><th scope="col">Action</th></tr></thead>
<tbody>
@forelse ($customers as $customer)
<tr><td>{{ $customer->id }}</td><td>{{ $customer->full_name }}</td><td>{{ $customer->email }}</td><td>{{ $customer->phone ?? '—' }}</td><td class="row-actions"><a href="{{ route('customers.show', $customer) }}">View</a><a href="{{ route('customers.edit', $customer) }}" aria-label="Edit {{ $customer->full_name }}">Edit</a>
<form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer?');">
@csrf @method('DELETE')
<button class="delete-button" type="submit">Delete</button>
</form></td></tr>
@empty
<tr><td colspan="5">No customers found.</td></tr>
@endforelse
</tbody></table></div>
@endsection

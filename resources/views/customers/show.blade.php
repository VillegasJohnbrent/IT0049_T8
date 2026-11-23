@extends('layouts.app')
@section('title', 'Customer Details')
@section('content')
<h1>Customer Details</h1>
<section class="panel details">
<dl>
<dt>Full name</dt><dd>{{ $customer->full_name ?? '—' }}</dd>
<dt>Email</dt><dd>{{ $customer->email ?? '—' }}</dd>
<dt>Phone</dt><dd>{{ $customer->phone ?? '—' }}</dd>
</dl>
<a class="button" href="{{ route('customers.edit', $customer) }}">Edit</a>
<a class="cancel-link" href="{{ route('customers.index') }}">Back to list</a>
</section>
@endsection

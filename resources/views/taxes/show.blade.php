@extends('layouts.admin')

@section('content')
<div class="container">
    <h3>Tax Details</h3>

    <p><strong>Name:</strong> {{ $tax->name }}</p>
    <p><strong>Rate:</strong> {{ $tax->rate }}%</p>
    <p><strong>Status:</strong> {{ $tax->is_active ? 'Active' : 'Inactive' }}</p>
</div>
@endsection
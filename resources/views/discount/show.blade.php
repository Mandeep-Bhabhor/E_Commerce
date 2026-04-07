@extends('layouts.admin')

@section('content')
<div class="container">
    <h3>Discount Details</h3>

    <p><strong>Code:</strong> {{ $discount->code }}</p>
    <p><strong>Type:</strong> {{ ucfirst($discount->type) }}</p>
    <p><strong>Value:</strong> {{ $discount->value }}</p>
    <p><strong>Start:</strong> {{ $discount->start_date }}</p>
    <p><strong>End:</strong> {{ $discount->end_date }}</p>
</div>
@endsection
@extends('products.customer_layout')

@section('content')
<div class="container">
    <h3>Create Discount</h3>

    <form action="{{ route('discounts.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Code</label>
            <input type="text" name="code" class="form-control">
        </div>

        <div class="mb-3">
            <label>Type</label>
            <select name="type" class="form-control">
                <option value="percentage">Percentage</option>
                <option value="amount">Amount</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Value</label>
            <input type="number" step="0.01" name="value" class="form-control">
        </div>

        <div class="mb-3">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control">
        </div>

        <div class="mb-3">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control">
        </div>

        <button class="btn btn-success">Save</button>
    </form>
</div>
@endsection
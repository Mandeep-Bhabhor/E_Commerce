@extends('layouts.admin')

@section('content')
    <div class="container">
        <h3>Edit Discount</h3>

        <form action="{{ route('discounts.update', $discount->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Code</label>
                <input type="text" name="code" value="{{ $discount->code }}" class="form-control">
            </div>

            <div class="mb-3">
                <label>Type</label>
                <select name="type" class="form-control">
                    <option value="percentage" {{ $discount->type == 'percentage' ? 'selected' : '' }}>Percentage</option>
                    <option value="amount" {{ $discount->type == 'amount' ? 'selected' : '' }}>Amount</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Value</label>
                <input type="number" step="0.01" name="value" value="{{ $discount->value }}" class="form-control">
            </div>
            <div class="mb-3">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $discount->end_date }}">
                @error('end_date')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <button class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection

@extends('products.customer_layout')

@section('content')
    <div class="container">
        <h3>Edit Tax</h3>

        <form action="{{ route('taxes.update', $tax->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" value="{{ $tax->name }}" class="form-control">
            </div>

            <div class="mb-3">
                <label>Rate (%)</label>
                <input type="number" step="0.01" name="rate" value="{{ $tax->rate }}" class="form-control">
            </div>

            <div class="mb-3">
                <label>Status</label>
                <select name="is_active" class="form-control">
                    <option value="1" {{ $tax->is_active ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$tax->is_active ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ $tax->start_date }}">
                @error('start_date')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $tax->end_date }}">
                @error('end_date')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <button class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection

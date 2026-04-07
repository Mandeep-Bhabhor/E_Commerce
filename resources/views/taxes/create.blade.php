@extends('layouts.admin')

@section('content')
<div class="container">
    <h3>Create Tax</h3>

    <form action="{{ route('taxes.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="mb-3">
            <label>Rate (%)</label>
            <input type="number" step="0.01" name="rate" class="form-control" value="{{ old('rate') }}">
            @error('rate')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="is_active" class="form-control">
                <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
            @error('start_date')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="mb-3">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
            @error('end_date')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <button class="btn btn-success">Save</button>
    </form>
</div>
@endsection
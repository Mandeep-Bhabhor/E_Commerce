@extends('products.customer_layout')

@section('content')
<div class="container">
    <h3>Taxes</h3>

    <a href="{{ route('taxes.create') }}" class="btn btn-primary mb-3">Add Tax</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Rate (%)</th>
                <th>Status</th>
                 <th>Start</th>
                <th>End</th>
                <th width="180">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($taxes as $tax)
            <tr>
                <td>{{ $tax->name }}</td>
                <td>{{ $tax->rate }}%</td>
                <td>{{ $tax->is_active ? 'Active' : 'Inactive' }}</td>
                    <td>{{ $tax->start_date }}</td>
                <td>{{ $tax->end_date }}</td>
                <td>
                    <a href="{{ route('taxes.show',$tax->id) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('taxes.edit',$tax->id) }}" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
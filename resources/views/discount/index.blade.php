@extends('products.customer_layout')

@section('content')
<div class="container">
    <h3>Discounts</h3>

    <a href="{{ route('discounts.create') }}" class="btn btn-primary mb-3">Add Discount</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Code</th>
                <th>Type</th>
                <th>Value</th>
                <th>Start</th>
                <th>End</th>
                <th width="200">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($discounts as $discount)
            <tr>
                <td>{{ $discount->code }}</td>
                <td>{{ ucfirst($discount->type) }}</td>
                <td>{{ $discount->value }}</td>
                <td>{{ $discount->start_date }}</td>
                <td>{{ $discount->end_date }}</td>
                <td>
                    <a href="{{ route('discounts.show',$discount->id) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('discounts.edit',$discount->id) }}" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
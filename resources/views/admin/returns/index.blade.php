@extends('products.customer_layout')

@section('content')

<div class="container mt-4">

    <h3 class="mb-4">
        <i class="fa fa-undo"></i> Return Requests
    </h3>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            Pending Return Requests
        </div>

        <div class="card-body">

            <table class="table table-bordered table-striped align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>Order</th>
                        <th>Product</th>
                        <th>User</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Delivered At</th>
                        <th>Requested At</th>
                        <th width="200">Action</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($items as $item)

                    <tr>

                        <td>#{{ $item->order->order_no }}</td>

                        <td>
                            {{ $item->product->name ?? '-' }}
                        </td>

                        <td>
                            {{ $item->order->user->email }}
                        </td>

                        <td>{{ $item->qty }}</td>

                        <td>₹ {{ $item->price }}</td>

                        <td>
                            {{ $item->delivered_at }}
                        </td>

                        <td>
                            {{ $item->return_requested_at }}
                        </td>

                        <td>

                            {{-- APPROVE --}}
                            <form action="{{ route('admin.return.approve',$item->id) }}" 
                                  method="POST"
                                  class="d-inline">
                                @csrf
                                @method('PUT')

                                <button class="btn btn-success btn-sm">
                                    Approve
                                </button>
                            </form>

                            {{-- REJECT --}}
                            <form action="{{ route('admin.return.reject',$item->id) }}" 
                                  method="POST"
                                  class="d-inline">
                                @csrf
                                @method('PUT')

                                <button class="btn btn-danger btn-sm">
                                    Reject
                                </button>
                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="text-center">
                            No return requests
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection
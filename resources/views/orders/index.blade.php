@extends('layouts.customer')

@section('content')

<div class="container mt-4">

    <h3 class="mb-4">
        <i class="fa fa-box"></i> My Orders
    </h3>

    @if($orders->count() == 0)
        <div class="text-center mt-5">
            <h5>No orders yet</h5>
            <p class="text-muted">
                You resisted buying things. Society is confused.
            </p>
        </div>
    @else

    <div class="table-responsive">
        <table class="table table-bordered align-middle">

            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Address</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th width="140">Action</th>
                </tr>
            </thead>

            <tbody>

                @foreach($orders as $order)
                    <tr>

                        <td>
                            <strong>#{{ $order->id }}</strong>
                        </td>

                        <td>
                            {{ $order->address->city }},
                            {{ $order->address->state }}
                        </td>

                        <td>
                            ₹ {{ $order->grand_total }}
                        </td>

                        <td>
                            <span class="badge bg-dark">
                                {{ ucfirst($order->order_status) }}
                            </span>
                        </td>

                        <td>
                            {{ $order->created_at->format('d M Y') }}
                        </td>

                        <td>
                            <a href="{{ route('orders.show', $order->id) }}"
                               class="btn btn-sm btn-outline-dark w-100">
                                View Details
                            </a>
                        </td>

                    </tr>
                @endforeach

            </tbody>

        </table>
    </div>

    @endif

</div>

@endsection

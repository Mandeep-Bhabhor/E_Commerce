@extends('products.customer_layout')

@section('content')

    <div class="container mt-4">

        <h3 class="mb-4">
            <i class="fa fa-box"></i> Order Placed
        </h3>

        {{-- SUCCESS MESSAGE --}}
        @session('success')
            <div class="alert alert-success">{{ $value }}</div>
        @endsession


        {{-- ORDER INFO --}}
        <div class="card mb-4">
            <div class="card-header">
                Order Details
            </div>

            <div class="card-body">

                <p class="mb-1"><strong>Order ID:</strong> #{{ $order->id }}</p>
                <p class="mb-1"><strong>Status:</strong> {{ ucfirst($order->order_status) }}</p>
                <p class="mb-0"><strong>Total Amount:</strong> ₹ {{ $order->grand_total }}</p>
                <p class="mb-0"><strong>Tax Rate:</strong> ₹ {{ $order->tax_rate }}</p>
                <p class="mb-0"><strong>Tax Amount:</strong> ₹ {{ $order->tax_amount }}</p>
                <p class="mb-0"><strong>Disount Amount:</strong> ₹ {{ $order->discount_amount }}</p>

            </div>
        </div>


        {{-- ADDRESS --}}
        <div class="card mb-4">
            <div class="card-header">
                Delivery Address
            </div>

            <div class="card-body">

                <p class="mb-1"><strong>{{ ucfirst($order->address->type) }} Address</strong></p>
                <p class="mb-1">{{ $order->address->address }}</p>
                <p class="mb-0">
                    {{ $order->address->city }},
                    {{ $order->address->state }} -
                    {{ $order->address->pincode }}
                </p>

            </div>
        </div>


        {{-- ORDER ITEMS --}}
        <div class="card">
            <div class="card-header">
                Ordered Products
            </div>

            <div class="card-body">

                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Return</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->color->name ?? '-' }}</td>
                                <td>{{ $item->size->name ?? '-' }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>₹ {{ $item->price }}</td>
                                <td>₹ {{ $item->total }}</td>
                                <td>
                                    <span class="badge bg-dark">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($item->status == 'delivered' && !$item->return_requested)
                                        <form method="POST" action="{{ route('order.item.return', $item->id) }}">
                                            @csrf
                                            <button class="btn btn-warning btn-sm">Return Item</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Payment Status
            </div>

            <div class="card-body">

                <p>
                    <strong>Method:</strong>
                    {{ ucfirst($order->payment_method) }}
                </p>

                <p>
                    <strong>Status:</strong>
                    <span
                        class="badge 
                @if ($order->payment_status === 'paid') bg-success
                @elseif($order->payment_status === 'pending') bg-warning
                @elseif($order->payment_status === 'failed') bg-danger
                @else bg-secondary @endif
            ">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </p>

                @if ($order->paid_at)
                    <p class="mt-2">
                        <strong>Paid On:</strong>
                        {{ $order->paid_at->format('d M Y, h:i A') }}
                    </p>
                @endif

            </div>
        </div>





    @endsection

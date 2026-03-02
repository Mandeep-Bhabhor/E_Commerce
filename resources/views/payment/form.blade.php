@extends('products.customer_layout')

@section('content')

<div class="container mt-4">

    <h3 class="mb-4">
        <i class="fa fa-credit-card"></i> Payment
    </h3>

    {{-- ORDER INFO --}}
    <div class="card mb-4">
        <div class="card-header">
            Order Details
        </div>

        <div class="card-body">
            <p><strong>Order No:</strong> {{ $order->order_no }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
    </div>


    {{-- ORDER ITEMS --}}
    <div class="card mb-4">
        <div class="card-header">
            Items
        </div>

        <div class="card-body">

            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Color</th>
                        <th>Size</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->color->name ?? '-' }}</td>
                            <td>{{ $item->size->name ?? '-' }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>₹ {{ $item->price }}</td>
                            <td>₹ {{ $item->total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="text-end">
                <h4>Total Amount: ₹ {{ $order->grand_total }}</h4>
            </div>

        </div>
    </div>


    {{-- PAYMENT OPTIONS --}}
    <div class="card">
        <div class="card-header">
            Select Payment Method
        </div>

        <div class="card-body">

            <form action="{{ route('razorpay.payment.store') }}" method="POST">
                @csrf

                <input type="hidden" name="order_id" value="{{ $order->id }}">

                <div class="mb-3">

                    <label class="me-4">
                        <input type="radio" name="payment_method" value="cod" checked>
                        Cash on Delivery
                    </label>

                    <label class="me-4">
                        <input type="radio" name="payment_method" value="online">
                        Pay Online (Razorpay)
                    </label>

                </div>

                <button class="btn btn-success w-100">
                    Continue Payment
                </button>

            </form>

        </div>
    </div>

</div>

@endsection

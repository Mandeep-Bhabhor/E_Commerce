@extends('products.customer_layout')

@section('content')
    <div class="container mt-4">

        <h3 class="mb-4">
            <i class="fa fa-credit-card"></i> Checkout
        </h3>

        {{-- ADDRESS --}}
        <div class="card mb-4">
            <div class="card-header">Delivery Address</div>
            <div class="card-body">
                @if ($address)
                    <p class="mb-1"><strong>{{ ucfirst($address->type) }} Address</strong></p>
                    <p class="mb-1">{{ $address->address }}</p>
                    <p class="mb-0">
                        {{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}
                    </p>
                @else
                    <div class="alert alert-danger">No address selected.</div>
                @endif
            </div>
        </div>

        {{-- CART SUMMARY --}}
        <div class="card mb-4">
            <div class="card-header">Order Summary</div>
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
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($cartItems as $item)
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

                <form method="GET" action="{{ route('checkout.index') }}">

                    <select name="discount_id" onchange="this.form.submit()" class="form-select w-auto">

                        <option value="">Select Discount</option>

                        @foreach ($discounts as $discount)
                            <option value="{{ $discount->id }}"
                                {{ $selectedDiscountId == $discount->id ? 'selected' : '' }}>

                                {{ $discount->code }}

                            </option>
                        @endforeach

                    </select>

                </form>
                {{-- PRICE BREAKDOWN --}}
                <div class="border-top pt-3 mt-3 text-end">

                    <p class="mb-1">
                        <strong>Subtotal:</strong> ₹ {{ $subtotal }}
                    </p>

                    <p class="mb-1">
                        <strong>Tax ({{ $taxRate }}%):</strong> ₹ {{ $taxAmount }}
                    </p>

                    <p class="mb-1">
                        <strong>Discount:</strong> ₹ {{ $discountAmount }}
                    </p>

                    <h4 class="mt-2">
                        Grand Total: ₹ {{ $grandTotal }}
                    </h4>

                </div>

            </div>
        </div>

        {{-- PAYMENT METHOD --}}
        <div class="card">
            <div class="card-header">Payment Method</div>

            <div class="card-body">

                <form action="{{ route('order.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">

                        <label class="me-4">
                            <input type="radio" name="payment_method" value="cod" checked>
                            Cash on Delivery
                        </label>
                        <input type="hidden" name="discount_id" value="{{ $selectedDiscountId }}">
                        <label class="me-4">
                            <input type="radio" name="payment_method" value="online">
                            Pay Online (Razorpay)
                        </label>

                    </div>

                    <button class="btn btn-success w-100">
                        Continue
                    </button>

                </form>

            </div>
        </div>

    </div>
@endsection

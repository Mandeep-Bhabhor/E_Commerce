@extends('layouts.customer')

@section('content')
@if (session('error'))
    <div class="alert alert-danger fw-bold">
        🚨 {{ session('error') }}
    </div>
@endif
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
                    <div class="alert alert-danger">No address selected. Please add an address to continue.</div>
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

                    <h4 class="mt-2 text-primary">
                        Grand Total: ₹ {{ $grandTotal }}
                    </h4>

                </div>

            </div>
        </div>

        {{-- PAYMENT METHOD --}}
        <div class="card border-primary mb-5">
            <div class="card-header bg-primary text-white">Payment Method</div>

            <div class="card-body">

                <form action="{{ route('order.store') }}" method="POST">
                    @csrf

                    {{-- Hidden input to pass the selected discount to the controller --}}
                    <input type="hidden" name="discount_id" value="{{ $selectedDiscountId }}">

                    <div class="mb-4 mt-2">

                        {{-- Option 1: Cash on Delivery --}}
                        <div class="form-check form-check-inline me-4">
                            <input class="form-check-input" type="radio" name="payment_method" id="pay_cod"
                                value="cod" checked>
                            <label class="form-check-label fw-bold" for="pay_cod">
                                <i class="fa fa-truck text-muted me-1"></i> Cash on Delivery
                            </label>
                        </div>

                        {{-- Option 2: Razorpay --}}
                        <div class="form-check form-check-inline me-4">
                            <input class="form-check-input" type="radio" name="payment_method" id="pay_razorpay"
                                value="razorpay">
                            <label class="form-check-label fw-bold" for="pay_razorpay">
                                <i class="fa fa-credit-card text-info me-1"></i> Razorpay (Cards/UPI)
                            </label>
                        </div>

                        {{-- Option 3: PayPal --}}
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="payment_method" id="pay_paypal"
                                value="paypal">
                            <label class="form-check-label fw-bold" for="pay_paypal">
                                <i class="fa fa-paypal text-primary me-1"></i> PayPal (International)
                            </label>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold"
                        @if (!$address) disabled @endif>
                        Place Order & Continue
                    </button>

                </form>

            </div>
        </div>

    </div>
@endsection

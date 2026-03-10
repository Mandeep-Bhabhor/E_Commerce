@extends('products.layout')

@section('content')
    <div class="container mt-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h3 class="mb-0 me-3">Order Details</h3>

            <form action="{{ route('order.updateStatus', $order->id) }}" method="POST">
                @csrf
                @method('PUT')

                <select name="status" class="form-select form-select-sm order-status w-auto" data-id="{{ $order->id }}"
                    {{ in_array($order->order_status, ['delivered', 'cancelled']) ? 'disabled' : '' }}>

                    {{-- placed --}}
                    <option value="placed" {{ $order->order_status == 'placed' ? 'selected' : '' }}>
                        Placed
                    </option>

                    {{-- delivered --}}
                    <option value="delivered" {{ $order->order_status == 'delivered' ? 'selected' : '' }}>
                        Delivered
                    </option>

                    {{-- cancelled only if no item delivered --}}
                    @if (!$order->items->contains('status', 'delivered'))
                        <option value="cancelled" {{ $order->order_status == 'cancelled' ? 'selected' : '' }}>
                            Cancelled
                        </option>
                    @endif

                </select>
            </form>
        </div>
        {{-- ORDER BASIC INFO --}}
        <div class="card mb-4">
            <div class="card-header">
                Order Info
            </div>
            <div class="card-body">

                <p><strong>Order ID:</strong> #{{ $order->id }}</p>
                <p><strong>Order NO:</strong> #{{ $order->order_no }}</p>
                <p><strong>Status:</strong> {{ ucfirst($order->order_status) }}</p>
                <p><strong>Sub Total:</strong> ₹ {{ $order->subtotal }}</p>
                <p><strong>Tax Amount:</strong> ₹ {{ $order->tax_amount }}</p>
                <p><strong>Tax Rate:</strong> ₹ {{ $order->tax_rate }}</p>
                <p><strong>Total Amount:</strong> ₹ {{ $order->grand_total }}</p>

                <p><strong>Placed At:</strong> {{ $order->created_at->format('d M Y, h:i A') }}</p>

            </div>
        </div>


        {{-- USER INFO --}}
        <div class="card mb-4">
            <div class="card-header">
                Customer Info
            </div>
            <div class="card-body">

                <p><strong>Name:</strong> {{ $order->user->name }}</p>
                <p><strong>Email:</strong> {{ $order->user->email }}</p>

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
        <div class="card mb-4">
            <div class="card-header">
                Order Items
            </div>

            <div class="card-body">

                <table class="table table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Product</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th width="100">Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>

                                <td>{{ $item->product->name ?? '-' }}</td>

                                <td>{{ $item->color->name ?? '-' }}</td>

                                <td>{{ $item->size->name ?? '-' }}</td>

                                <td>{{ $item->qty }}</td>

                                <td>₹ {{ $item->price }}</td>

                                <td>₹ {{ $item->total }}</td>

                                <td>
                                    <form action="{{ route('order.items.updateStatus', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <select name="status" class="form-select form-select-sm item-status"
                                            data-id="{{ $item->id }}">
                                            <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>
                                                Pending
                                            </option>
                                            <option value="delivered" {{ $item->status == 'delivered' ? 'selected' : '' }}>
                                                Delivered</option>
                                            <option value="cancelled" {{ $item->status == 'cancelled' ? 'selected' : '' }}>
                                                Cancelled</option>
                                        </select>
                                    </form>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>

        <div class="text-end">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-dark">
                Back to Orders
            </a>
        </div>

    </div>
    <script>
        $(document).ready(function() {

            // Setup CSRF globally (cleaner way)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            $('.item-status').change(function() {

                let status = $(this).val();
                let itemId = $(this).data('id');
                let selectElement = $(this);

                console.log('Updating to:', status);

                $.ajax({
                    url: "{{ route('order.items.updateStatus','id') }}".replace('id',itemId),
                    type: 'POST', // use POST instead of PUT
                    data: {
                        _method: 'PUT', // Laravel spoofing
                        status: status
                    },
                    success: function(response) {
                        console.log('Status updated successfully');
                        location.reload();

                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        alert('Update failed');
                        // revert dropdown to previous value if failed
                        location.reload();
                    }
                });

            });

        });

        $(document).ready(function() {

            // Setup CSRF globally (cleaner way)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            $('.order-status').change(function() {

                let status = $(this).val();
                let orderId = $(this).data('id');
                let selectElement = $(this);

                console.log('Updating to:', status);

                $.ajax({
                    url: "{{ route('order.updateStatus', ':id') }}".replace(':id', orderId),
                    type: 'POST', // use POST instead of PUT
                    data: {
                        _method: 'PUT', // Laravel spoofing
                        status: status
                    },
                    success: function(response) {
                        console.log('Status updated successfully');
                        location.reload();

                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        alert('Update failed');

                        // revert dropdown to previous value if failed
                        location.reload();
                    }
                });

            });

        });
    </script>
@endsection

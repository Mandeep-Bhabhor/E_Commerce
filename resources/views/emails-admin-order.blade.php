<h2>New Order Placed</h2>

<p><strong>Order ID:</strong> {{ $order->id }}</p>
<p><strong>Total:</strong> ₹ {{ $order->grand_total }}</p>

<p><strong>Customer:</strong> {{ $order->user->name }}</p>
<p><strong>Email:</strong> {{ $order->user->email }}</p>

<hr>

<h3>Delivery Address</h3>

<p>
    {{ $order->address->address }} <br>
    {{ $order->address->city }}, {{ $order->address->state }} <br>
    PIN: {{ $order->address->pincode }}
</p>

<hr>

<h3>Ordered Products</h3>

<table border="1" cellpadding="8" cellspacing="0" width="100%">
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
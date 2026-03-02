<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Status Updated</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">

    <div style="max-width:600px; margin:auto; background:white; padding:20px; border-radius:6px;">

        <h2 style="margin-top:0;">Order Status Updated</h2>

        <p>
            The status of an order has been changed.
        </p>

        <hr>

        <p><strong>Order ID:</strong> #{{ $order->id }}</p>
        <p><strong>Order Number:</strong> {{ $order->order_no }}</p>

        <p>
            <strong>Previous Status:</strong>
            <span style="color:#888;">
                {{ ucfirst($oldStatus) }}
            </span>
        </p>

        <p>
            <strong>New Status:</strong>
            <span style="color:#2c7be5;">
                {{ ucfirst($newStatus) }}
            </span>
        </p>

        <p><strong>Total Amount:</strong> ₹ {{ $order->grand_total }}</p>

        <hr>

        <h4>Customer Details</h4>

        <p><strong>Name:</strong> {{ $order->user->name }}</p>
        <p><strong>Email:</strong> {{ $order->user->email }}</p>

        <hr>

        <h4>Delivery Address</h4>

        <p>{{ $order->address->address }}</p>
        <p>
            {{ $order->address->city }},
            {{ $order->address->state }} -
            {{ $order->address->pincode }}
        </p>

        <hr>

        <h4>Ordered Items</h4>

        <table width="100%" border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;">
            <thead style="background:#eee;">
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>₹ {{ $item->price }}</td>
                        <td>₹ {{ $item->total }}</td>
                        <td>{{ ucfirst($item->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <br>

      

    </div>

</body>
</html>
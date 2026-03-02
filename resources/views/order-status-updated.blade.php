<h2>Order Status Updated</h2>

<p>Hello {{ $item->order->user->name }},</p>

<p>Your order item status has been updated.</p>

<p><strong>Product:</strong> {{ $item->product->name }}</p>
<p><strong>Old Status:</strong> {{ ucfirst($oldStatus) }}</p>
<p><strong>Updated Status:</strong> {{ ucfirst($item->status) }}</p>

<p>Order ID: {{ $item->order->id }}</p>

<p>Thank you.</p>
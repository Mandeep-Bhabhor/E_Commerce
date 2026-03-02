<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Return Request</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f6f6f6; padding:20px;">

<table width="600" align="center" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:6px;padding:20px">

<tr>
<td>

<h2 style="color:#333;">Return Request Received</h2>

<p>
A customer has requested to return a product.
</p>

<hr>

<h3>Order Information</h3>

<p><strong>Order ID:</strong> #{{ $item->order->id }}</p>
<p><strong>Order Number:</strong> {{ $item->order->order_no }}</p>

<hr>

<h3>Customer Information</h3>

<p><strong>Name:</strong> {{ $item->order->user->name }}</p>
<p><strong>Email:</strong> {{ $item->order->user->email }}</p>

<hr>

<h3>Product Details</h3>

<p><strong>Product:</strong> {{ $item->product->name }}</p>

<p><strong>Color:</strong>
{{ $item->color->name ?? '-' }}
</p>

<p><strong>Size:</strong>
{{ $item->size->name ?? '-' }}
</p>

<p><strong>Quantity:</strong> {{ $item->qty }}</p>

<p><strong>Price:</strong> ₹ {{ $item->price }}</p>

<p><strong>Total:</strong> ₹ {{ $item->total }}</p>

<hr>

<h3>Delivery Information</h3>

<p>
<strong>Delivered At:</strong>
{{ $item->delivered_at }}
</p>

<p>
<strong>Return Requested At:</strong>
{{ $item->return_requested_at }}
</p>

<hr>

<p>
Please review this request in the admin panel and approve or reject the return.
</p>

<br>

<a href="{{ url('/admin/returns') }}"
style="
display:inline-block;
padding:10px 20px;
background:#007bff;
color:white;
text-decoration:none;
border-radius:4px;
">
View Return Requests
</a>

<br><br>

<p style="color:#777;font-size:12px;">
This is an automated email from your store system.
</p>

</td>
</tr>

</table>

</body>
</html>
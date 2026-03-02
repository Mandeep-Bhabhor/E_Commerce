<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Return Request Update</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:30px;">

<table width="600" align="center" style="background:white; padding:25px; border-radius:6px;">

<tr>
<td>

<h2 style="margin-bottom:20px;">Return Request Update</h2>

<p>Hello {{ $item->order->user->name }},</p>

<p>
Your return request for the following product has been reviewed by our team.
</p>

<hr>

<h3>Product Details</h3>

<p><strong>Order ID:</strong> #{{ $item->order->id }}</p>

<p><strong>Product:</strong> {{ $item->product->name }}</p>

<p><strong>Color:</strong> {{ $item->color->name ?? '-' }}</p>

<p><strong>Size:</strong> {{ $item->size->name ?? '-' }}</p>

<p><strong>Quantity:</strong> {{ $item->qty }}</p>

<p><strong>Price:</strong> ₹ {{ $item->price }}</p>

<hr>

<h3>Return Status</h3>

@if($item->refund_status == 'processed')

<p style="color:green; font-weight:bold;">
Your return request has been approved and refund is processed.
</p>

<p><strong>Refund Amount:</strong> ₹ {{ $item->refund_amount }}</p>

<p><strong>Refund Date:</strong> {{ $item->returned_at }}</p>

@elseif($item->refund_status == 'rejected')

<p style="color:red; font-weight:bold;">
Unfortunately your return request was rejected.
</p>

<p>
If you believe this is incorrect, please contact support.
</p>

@else

<p>
Your return request is currently under review.
</p>

@endif

<hr>

<p>
You can view the order details from your account.
</p>

<a href="{{ url('/orders/'.$item->order->id) }}"
style="background:#28a745; color:white; padding:10px 18px; text-decoration:none; border-radius:4px;">
View Order
</a>

<br><br>

<p style="font-size:12px; color:#777;">
Thank you for shopping with us.
</p>

</td>
</tr>

</table>

</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Razorpay Payment</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>

<div class="container text-center mt-5">
    <h3>Processing Payment...</h3>
</div>

<script>
    var options = {
        key: "{{ $razorpayKey }}",
        amount: "{{ $razorpayOrder['amount'] }}",
        currency: "INR",
        order_id: "{{ $razorpayOrder['id'] }}",
        name: "Excelsior Technologies",
        description: "Payment for Order #{{ $order->id }}",
        prefill: {
            name: "{{ auth()->user()->name }}",
            email: "{{ auth()->user()->email }}"
        },
        theme: {
            color: "#ff7529"
        },
        handler: function (response) {

            fetch("{{ route('razorpay.success') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(response)
            })
            .then(res => res.json())
            .then(data => {
                window.location.href = data.redirect_url;
            });
        }
    };

    var rzp = new Razorpay(options);
    rzp.open();
</script>

</body>
</html>

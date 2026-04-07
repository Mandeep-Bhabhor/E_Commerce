<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Razorpay\Api\Api;

use function Symfony\Component\Clock\now;

class RazorPayPaymentController extends Controller
{
    // Optional landing page
    public function index(): View
    {
        return view('razorpay');
    }

    /**
     * Show payment form before Razorpay checkout
     */
    public function paymentForm(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);

        return view('payment.form', compact('order'));
    }

    /**
     * Create Razorpay order & open checkout
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $razorpayOrder = $api->order->create([
            'receipt' => 'order_'.$order->id,
            'amount' => $order->grand_total * 100, // paise
            'currency' => 'INR',
            'payment_capture' => 1,
        ]);

        // Save Razorpay reference
        $order->update([
            'gateway_order_id' => $razorpayOrder['id'],
            'payment_method' => 'online',
            'payment_status' => 'pending',
        ]);

        $razorpayKey = config('services.razorpay.key');

        return view('razorpay', compact('order', 'razorpayOrder', 'razorpayKey'));
    }

    /**
     * HANDLE PAYMENT SUCCESS
     * This is where real confirmation happens
     */
    public function success(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required',
        ]);

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $attributes = [
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
        ];

        try {
            // VERIFY SIGNATURE
            $api->utility->verifyPaymentSignature($attributes);

            // FIND ORDER
            $order = Order::where('razorpay_order_id', $request->razorpay_order_id)->firstOrFail();

            // UPDATE PAYMENT DETAILS
            $order->update([
                'transaction_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'payment_status' => 'paid',
                'paid_at' => now()
            ]);

            return response()->json([
                'redirect_url' => route('orders.show', $order->id),
            ]);

        } catch (\Exception $e) {

            return redirect()->route('orders.show', $request->razorpay_order_id)
                ->with('error', 'Payment verification failed.');
        }
    }
}

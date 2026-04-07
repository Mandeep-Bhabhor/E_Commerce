<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalController extends Controller
{
    // 1. Send the user to PayPal
    public function processPayment($orderId)
    {
        $order = Order::findOrFail($orderId);

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        
        // 🔥 FORCE THE GLOBAL PAYPAL CLIENT TO USE USD
        $provider->setCurrency('USD'); 

        // 1. Convert INR to USD (Assume 1 USD = 83 INR for testing)
        $exchangeRate = 83; 
        $usdAmount = round($order->grand_total / $exchangeRate, 2);

        // 2. Create the PayPal Order
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('paypal.success', ['order' => $order->id]),
                "cancel_url" => route('paypal.cancel', ['order' => $order->id]),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "USD", 
                        "value" => number_format($usdAmount, 2, '.', '') 
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }
        }

        return redirect()->route('checkout.index')->with('error', 'Something went wrong with PayPal.');
    }

    // 2. Handle successful return from PayPal
    public function success(Request $request, $orderId)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        
        $provider->setCurrency('USD'); 

        $response = $provider->capturePaymentOrder($request['token']);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            
            $order = Order::findOrFail($orderId);
            
            // Mark order as paid
            $order->update([
                'payment_status' => 'paid',
                'transaction_id' => $response['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
                'gateway_order_id' => $response['id'] ?? null,
            ]);

            return redirect()->route('orders.show', $order->id)->with('success', 'Payment successful! Order placed.');
        }

        return redirect()->route('checkout.index')->with('error', 'Payment was declined or failed.');
    }

    // 3. Handle cancellation
    public function cancel($orderId)
    {
        return redirect()->route('checkout.index')->with('error', 'You have cancelled the payment.');
    }
}
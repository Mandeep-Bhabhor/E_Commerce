<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $query = Order::with(['items.product', 'address'])
            ->where('user_id', $user->id);

        // 🔍 Search logic
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'LIKE', "%{$search}%")
                    ->orWhere('payment_method', 'LIKE', "%{$search}%")
                    ->orWhere('order_status', 'LIKE', "%{$search}%")
                    ->orWhere('payment_status', 'LIKE', "%{$search}%")
                    ->orWhereHas('items.product', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $orders = $query->latest()->paginate($perPage);

        return response()->json($orders, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_order(
        Request $request,
        WhatsAppService $whatsapp
    ) {
        // 1. Validation
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:cod', // Locked to COD for now
            'discount_id' => 'nullable|exists:discounts,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.color_id' => 'nullable|exists:colors,id',
            'items.*.size_id' => 'nullable|exists:sizes,id',
        ]);

        $userId = $request->user_id;
        $addressId = $request->address_id;
        $paymentMethod = $request->payment_method;

        // 2. Address Verification
        $address = Address::where('id', $addressId)->where('user_id', $userId)->first();
        if (! $address) {
            return response()->json(['message' => 'Invalid address selected for this user'], 403);
        }

        // 3. Process Items & Calculate Subtotal
        $orderItemsData = [];
        $subtotal = 0;

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $itemPrice = $product->price;
            $itemTotal = $itemPrice * $item['qty'];

            $subtotal += $itemTotal;

            $orderItemsData[] = [
                'product_id' => $item['product_id'],
                'color_id' => $item['color_id'] ?? null,
                'size_id' => $item['size_id'] ?? null,
                'qty' => $item['qty'],
                'price' => $itemPrice,
                'total' => $itemTotal,
            ];
        }

        // 4. Discount Logic
        $discountAmount = 0;
        if ($request->discount_id) {
            $discount = Discount::where('id', $request->discount_id)
                ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
                ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                ->first();

            if ($discount) {
                $discountAmount = ($discount->type === 'percentage')
                    ? ($subtotal * $discount->value) / 100
                    : $discount->value;
            }
        }

        // 5. Tax Logic
        $tax = Tax::where('is_active', true)
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->first();

        $taxRate = $tax->rate ?? 0;
        $taxAmount = ($subtotal - $discountAmount) * $taxRate / 100;
        $grandTotal = round($subtotal - $discountAmount + $taxAmount);

        // 6. Database Transaction
        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => $userId,
                'address_id' => $addressId,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                // UPDATED: Always pending for COD until cash is actually collected
                'payment_status' => 'pending',
                'payment_method' => $paymentMethod,
                'order_status' => 'placed',
            ]);

            // Generate Order Number
            $order->update(['order_no' => str_pad($order->id, 4, '0', STR_PAD_LEFT)]);

            // 7. Create Order Items
            foreach ($orderItemsData as $itemData) {
                $order->items()->create(array_merge($itemData, ['status' => 'pending']));
            }

            DB::commit();
            $this->notifyAdmin($order);
            /*
|--------------------------------------------------------------------------
| Load Relationships
|--------------------------------------------------------------------------
*/

            $order->load('items.product');

            /*
|--------------------------------------------------------------------------
| Product List
|--------------------------------------------------------------------------
*/

            $productList = "";

            foreach ($order->items as $item) {

                $productName =
                    $item->product->name ?? 'Product';

                $productList .=
                    "• {$productName}\n";

                $productList .=
                    "Qty: {$item->qty}\n";

                $productList .=
                    "Price: ₹{$item->price}\n\n";
            }

            /*
|--------------------------------------------------------------------------
| WhatsApp Message
|--------------------------------------------------------------------------
*/

            $user = User::find($userId);

            $message = "
Hello {$user->name},
This is From API 

Your order has been placed successfully.

Order No: #{$order->order_no}

Order Status: {$order->order_status}

Items:
{$productList}

Subtotal: ₹{$order->subtotal}

Tax: ₹{$order->tax_amount}

Discount: ₹{$order->discount_amount}

Grand Total: ₹{$order->grand_total}

Payment Method: {$order->payment_method}

Thank you for shopping with us.
";

            /*
|--------------------------------------------------------------------------
| Static Test Image
|--------------------------------------------------------------------------
*/

            $imageUrl = 'https://images.unsplash.com/photo-1506744038136-46273834b3fb';


            /*
|--------------------------------------------------------------------------
| Send WhatsApp
|--------------------------------------------------------------------------
*/

            if (!empty($user->phone_number)) {

                $whatsapp->sendMessage(
                    $user->phone_number,
                    $message,
                    $imageUrl
                );
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Order placed successfully (COD)',
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'data' => $order->load('items'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['status' => 'error', 'message' => 'Order creation failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    private function notifyAdmin($order)
    {
        // 1. Find the Admin (User ID 1)
        $admin = User::find(3);

        // 2. If they exist and have a token, build the message
        if ($admin && $admin->fcm_token) {
            try {
                // Setup Firebase
                $firebase = (new Factory)->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
                $messaging = $firebase->createMessaging();

                // Build the Notification (Now with dynamic Order info!)
                // Build the Notification (Using the modern Firebase v7+ syntax!)
                $message = CloudMessage::new()
                    ->toToken($admin->fcm_token)
                    ->withNotification(Notification::create(
                        '🚨 New Order Received!',
                        "Order #{$order->id} was just placed. Check the dashboard!"
                    ));

                // Fire it off!
                $messaging->send($message);
            } catch (\Exception $e) {
                // Log errors silently so the customer's checkout doesn't crash
                Log::error('Firebase Notification Failed: ' . $e->getMessage());
            }
        }
    }
}

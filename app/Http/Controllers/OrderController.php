<?php

namespace App\Http\Controllers;

use App\Events\MainOrderStatus;
use App\Services\WhatsAppService;
use App\Events\OrderPlaced;
use App\Events\OrderStatusUpdated as EventsOrderStatusUpdated;
use App\Events\ReturnStatusUpdated;
use App\Events\UserReturnProduct as EventsUserReturnProduct;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tax;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Symfony\Component\HttpFoundation\Request;

use function Symfony\Component\Clock\now;

class OrderController extends Controller
{
    //
    public function store_order(Request $request, WhatsAppService $whatsapp)
    {
        $userId = Auth::id();
        $user = Auth::user();

        $paymentMethod = $request->payment_method;
        $addressId = session('checkout_address_id');

        if (!$addressId) {
            return back()->with('error', 'Please select address first');
        }

        $address = Address::where('id', $addressId)
            ->where('user_id', $userId)
            ->first();

        if (!$address) {
            return back()->with('error', 'Invalid address selected');
        }

        $cartItems = Cart::where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Cart is empty');
        }

        /*
    |--------------------------------------------------------------------------
    | Calculate Subtotal
    |--------------------------------------------------------------------------
    */

        $subtotal = $cartItems->sum(function ($item) {
            return $item->qty * $item->price;
        });

        /*
    |--------------------------------------------------------------------------
    | Discount
    |--------------------------------------------------------------------------
    */

        $discountAmount = 0;

        if ($request->discount_id) {

            $discount = Discount::where('id', $request->discount_id)
                ->where(function ($q) {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                })
                ->first();

            if ($discount) {

                if ($discount->type === 'percentage') {

                    $discountAmount =
                        ($subtotal * $discount->value) / 100;
                }

                if ($discount->type === 'amount') {

                    $discountAmount = $discount->value;
                }
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Tax
    |--------------------------------------------------------------------------
    */

        $tax = Tax::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->first();

        $taxRate = $tax->rate ?? 0;

        $taxAmount =
            ($subtotal - $discountAmount) * $taxRate / 100;

        /*
    |--------------------------------------------------------------------------
    | Final Total
    |--------------------------------------------------------------------------
    */

        $shippingAmount = 0;

        $grandTotal = round(
            $subtotal -
                $discountAmount +
                $taxAmount +
                $shippingAmount
        );

        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | Create Order
        |--------------------------------------------------------------------------
        */

            $order = Order::create([

                'user_id' => $userId,
                'address_id' => $addressId,

                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,

                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,

                'grand_total' => $grandTotal,

                'payment_status' => 'pending',
                'payment_method' => $paymentMethod,
                'order_status' => 'placed',
            ]);

            $order->order_no =
                str_pad($order->id, 4, '0', STR_PAD_LEFT);

            $order->save();

            /*
        |--------------------------------------------------------------------------
        | Store Order Items
        |--------------------------------------------------------------------------
        */

            $this->store_order_items(
                $order->id,
                $cartItems
            );

            /*
        |--------------------------------------------------------------------------
        | Clear Cart
        |--------------------------------------------------------------------------
        */

            Cart::where('user_id', $userId)->delete();

            DB::commit();

            /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

            $order->load('items.product');

            /*
        |--------------------------------------------------------------------------
        | Events & Notifications
        |--------------------------------------------------------------------------
        */

            event(new OrderPlaced($order));

            $this->notifyAdmin($order);

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

            $message = "
Hello {$user->name},

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
        | Product Image
        |--------------------------------------------------------------------------
        */
            $imageUrl = null;

            if ($order->items->isNotEmpty()) {

                $firstItem = $order->items->first();

                if (
                    $firstItem->product &&
                    !empty($firstItem->product->image)
                ) {

                    $images = $firstItem->product->image;

                    // Take first image from array
                    $image = $images[0] ?? null;

                    if ($image) {

                        // $imageUrl = url(
                        //     'public/storage/products/' . $image
                        // );

                        $imageUrl = 'https://images.unsplash.com/photo-1506744038136-46273834b3fb';

                        //dd($imageUrl);
                    }
                }
            }
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

            /*
        |--------------------------------------------------------------------------
        | Redirect By Payment Method
        |--------------------------------------------------------------------------
        */

            if ($paymentMethod === 'cod') {

                $order->update([
                    'payment_status' => 'paid'
                ]);

                return redirect()->route(
                    'orders.show',
                    $order->id
                );
            }

            if ($paymentMethod === 'online') {

                return redirect()->route(
                    'razorpay.payment.form',
                    [
                        'order_id' => $order->id,
                    ]
                );
            }

            if ($paymentMethod === 'paypal') {

                return redirect()->route(
                    'paypal.process',
                    [
                        'order' => $order->id,
                    ]
                );
            }
        } catch (\Exception $e) {

            DB::rollback();

            dd($e->getMessage());
        }

        return back()->with(
            'error',
            'Invalid payment method'
        );
    }

    public function store_order_items($orderId, $cartItems): void
    {
        foreach ($cartItems as $item) {

            OrderItem::create([
                'order_id' => $orderId,
                'product_id' => $item->product_id,
                'color_id' => $item->color_id,
                'size_id' => $item->size_id,
                'qty' => $item->qty,
                'price' => $item->price,
                'total' => $item->qty * $item->price,
                'status' => 'pending',
            ]);
        }
    }

    public function orders($id)
    {
        $order = Order::with([
            'address',
            'items.product',
            'items.color',
            'items.size',
        ])->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('carts.orders', compact('order'));
    }

    public function index()
    {
        $orders = Order::with('address')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function adminIndex()
    {
        $orders = Order::with([
            'user',
            'address',
        ])
            ->latest()->paginate();

        return view('admin.orders.index', compact('orders'));
    }

    public function adminShow($id)
    {
        $order = Order::with([
            'user',
            'address',
            'items.product',
            'items.color',
            'items.size',
        ])->findOrFail($id);

        $order = Order::with('items')->findOrFail($id);

        $hasDeliveredItem = $order->items
            ->where('status', 'delivered')
            ->count() > 0;

        // return view('orders.show', compact('order', 'hasDeliveredItem'));

        return view('admin.orders.show', compact('order', 'hasDeliveredItem'));
    }

    public function adminSearch(Request $request)
    {
        $query = trim($request->input('q'));

        if (! $query) {
            return response()->json([]);
        }

        $orders = Order::with(['user', 'address'])
            ->where(function ($q) use ($query) {

                // order number
                $q->where('order_no', 'LIKE', "%{$query}%")

                    // status
                    ->orWhere('order_status', 'LIKE', "%{$query}%");

                // date search
                try {
                    $date = Carbon::parse($query)->format('Y-m-d');
                    $q->orWhereDate('created_at', $date);
                } catch (\Exception $e) {
                    // not a date → skip
                }

                // user search
                $q->orWhereHas('user', function ($userQuery) use ($query) {
                    $userQuery->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('email', 'LIKE', "%{$query}%");
                });
            })
            ->latest()
            ->limit(20)
            ->get();

        return response()->json($orders);
    }

    public function updateItemStatus(Request $request, $itemId, WhatsAppService $whatsapp)
    {
        $request->validate([
            'status' => 'required|in:placed,delivered,cancelled',
        ]);

        $item = OrderItem::findOrFail($itemId);
        $oldStatus = $item->status;
        $newStatus = $request->status;

        // ❌ prevent reverting
        if (in_array($oldStatus, ['delivered', 'cancelled'])) {
            return response()->json(['error' => 'Final status cannot be changed'], 422);
        }

        if ($newStatus === 'placed') {
            return response()->json(['error' => 'Cannot revert to placed'], 422);
        }

        $item->update(['status' => $newStatus]);

        // 🔥 AUTO UPDATE MAIN ORDER
        $order = $item->order;

        $remaining = $order->items()
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->count();

        if ($newStatus === 'delivered') {
            $item->update(['delivered_at' => now()]);
        }
        if ($remaining === 0) {
            $order->update(['order_status' => 'delivered']);
        }
        event(new EventsOrderStatusUpdated($item, $oldStatus));
        /////

        $user = $order->user;

        $productName = $item->product?->name;
      

        $message = "
Hello {$user->name},

Your order Item Status has been changed.

Order No: #{$order->order_no}

Item Name : {$productName}

Old Item Status: {$oldStatus}

Updated Status: {$item->status}

";



        if (!empty($user->phone_number)) {

            $whatsapp->sendMessage(
                $user->phone_number,
                $message,
            );
        }



        return response()->json(['success' => true]);
    }

    public function updateOrderStatus(Request $request, $id,WhatsAppService $whatsapp)
    {
        $request->validate([
            'status' => 'required|in:placed,delivered,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->order_status;
        $newStatus = $request->status;

        // ❌ block reverse transitions
        if (in_array($oldStatus, ['delivered', 'cancelled'])) {
            return response()->json(['error' => 'Final status cannot be changed'], 422);
        }

        // ❌ cannot go back to placed
        if ($newStatus === 'placed') {
            return response()->json(['error' => 'Cannot revert to placed'], 422);
        }

        // update main order
        $order->update(['order_status' => $newStatus]);

        // sync items
        $order->items()->update(['status' => $newStatus, 'delivered_at' => now()]);
        event(new MainOrderStatus($order, $oldStatus, $newStatus));
   $user = $order->user;

        $productName = $item->product?->name;
      

        $message = "
Hello {$user->name},

Your order Item Status has been changed.

Order No: #{$order->order_no}

Item Name : {$productName}

Old Item Status: {$oldStatus}

Updated Status: {$item->status}

";



        if (!empty($user->phone_number)) {

            $whatsapp->sendMessage(
                $user->phone_number,
                $message,
            );
        }

        return response()->json(['success' => true]);
    }

    public function requestReturn($id)
    {
        $item = OrderItem::findOrFail($id);

        if ($item->status !== 'delivered') {
            return back()->with('error', 'Item not delivered yet');
        }

        if ($item->return_requested) {
            return back()->with('error', 'Return already requested');
        }

        // check return window (7 days)
        $deliveryDate = Carbon::parse($item->delivered_at);

        if (Carbon::now()->gt($deliveryDate->addDays(7))) {
            return back()->with('error', 'Return window expired');
        }

        $item->update([
            'return_requested' => true,
            'return_requested_at' => now(),
        ]);
        event(new EventsUserReturnProduct($item));

        return back()->with('success', 'Return request sent');
    }

    public function returnRequests()
    {
        $items = OrderItem::with(['order.user', 'product'])
            ->where('return_requested', true)
            ->whereNull('refund_status')
            ->get();

        return view('admin.returns.index', compact('items'));
    }

    public function approveReturn($id)
    {

        $item = OrderItem::findOrFail($id);
        // $oldStatus = $item->
        $item->update([
            'is_returned' => true,
            'returned_at' => Carbon::now(),
            'refund_amount' => $item->price,
            'refund_status' => 'processed',
        ]);
        event(new ReturnStatusUpdated($item));

        return back()->with('success', 'Return approved successfully');
    }

    public function rejectReturn($id)
    {
        $item = OrderItem::findOrFail($id);
        $item->update([
            'refund_status' => 'rejected',
        ]);
        event(new ReturnStatusUpdated($item));

        return back()->with('success', 'Return request rejected');
    }

    /**
     * Send a Firebase Push Notification to the Admin
     */
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

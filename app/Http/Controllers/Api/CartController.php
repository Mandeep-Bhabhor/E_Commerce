<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function index($user_id)
{
    // 1. Fetch cart items with relationships
    $cartItems = Cart::with(['product', 'color', 'size'])
        ->where('user_id', $user_id)
        ->get();

    // 2. Check if the cart is empty (Optional but helpful for frontend)
    if ($cartItems->isEmpty()) {
        return response()->json([
            'status' => 'success',
            'message' => 'Cart is empty',
            'data' => [],
            'total' => 0
        ], 200);
    }

    // 3. Calculate total
    $total = $cartItems->sum('total');

    // 4. Return JSON instead of a View
    return response()->json([
        'status' => 'success',
        'data' => $cartItems,
        'total' => $total
    ], 200);
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
  public function store(Request $request)
{
    $request->validate([
        'user_id'    => 'required|exists:users,id',
        'product_id' => 'required|exists:products,id',
        'qty'        => 'required|integer|min:1',
        'color_id'   => 'nullable|exists:colors,id',
        'size_id'    => 'nullable|exists:sizes,id',
    ]);

    $product = Product::findOrFail($request->product_id);

    // Check if this specific item is already in the user's cart
    $cart = Cart::where('user_id', $request->user_id)
        ->where('product_id', $request->product_id)
        ->where('color_id', $request->color_id)
        ->where('size_id', $request->size_id)
        ->first();

    if ($cart) {
        // Update existing item
        $cart->qty += $request->qty;
        $cart->total = $cart->qty * $product->price;
        $cart->save();
    } else {
        // Create new cart entry
        $cart = Cart::create([
            'user_id'    => $request->user_id,
            'product_id' => $request->product_id,
            'qty'        => $request->qty,
            'price'      => $product->price,
            'total'      => $request->qty * $product->price,
            'color_id'   => $request->color_id,
            'size_id'    => $request->size_id,
        ]);
    }

    return response()->json(['message' => 'Item added to cart', 'data' => $cart], 201);
}
    /**
     * Display the specified resource.
     */
   public function show($id)
{
    $cart = Cart::with(['product', 'color', 'size'])->findOrFail($id);

    return response()->json(['status' => 'success', 'data' => $cart]);
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
   public function update(Request $request, $id)
{
    $request->validate(['qty' => 'required|integer|min:1']);

    $cart = Cart::findOrFail($id);
    $product = Product::findOrFail($cart->product_id);

    $cart->qty = $request->qty;
    $cart->total = $request->qty * $product->price;
    $cart->save();

    return response()->json(['message' => 'Cart updated', 'data' => $cart]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
      $cart = Cart::findOrFail($id);
    $cart->delete();

    return response()->json(['message' => 'Item removed from cart']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Authenticated user
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Inputs
        $search = $request->input('search');
        $perPage = $request->input('per_page', 5);
        $page = $request->input('page', 1);

        // Fix pagination for POST
        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        // Base Query
        $query = Cart::with(['product', 'color', 'size'])
            ->where('user_id', $user->id);

        if ($search) {

            // Matching color IDs
            $colorIds = Color::where('name', 'LIKE', "%{$search}%")
                ->pluck('id')
                ->toArray();

            // Matching size IDs
            $sizeIds = Size::where('name', 'LIKE', "%{$search}%")
                ->pluck('id')
                ->toArray();

            $query->where(function ($main) use ($search, $colorIds, $sizeIds) {

                // Product relation search
                $main->whereHas('product', function ($q) use ($search, $colorIds, $sizeIds) {

                    // Product name/detail search
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('detail', 'LIKE', "%{$search}%");

                    // Product JSON colors
                    foreach ($colorIds as $id) {
                        $q->orWhereJsonContains('color', (string) $id);
                    }

                    // Product JSON sizes
                    foreach ($sizeIds as $id) {
                        $q->orWhereJsonContains('size', (string) $id);
                    }
                });

                // Cart selected color
                if (!empty($colorIds)) {
                    $main->orWhereIn('color_id', $colorIds);
                }

                // Cart selected size
                if (!empty($sizeIds)) {
                    $main->orWhereIn('size_id', $sizeIds);
                }
            });
        }

        // Pagination
        $cartItems = $query->paginate($perPage);

        // Full cart total
        $total = Cart::where('user_id',$user->id)->sum('total');

        // Empty cart
        if ($cartItems->count() == 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Cart is empty',
                'data' => [],
                'total' => 0
            ], 200);
        }

        // Response
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
        // Get authenticated user
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Validation
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty'        => 'required|integer|min:1',
            'color_id'   => 'nullable|exists:colors,id',
            'size_id'    => 'nullable|exists:sizes,id',
        ]);

        // Find product
        $product = Product::findOrFail($request->product_id);

        // Check if item already exists in cart
        $cart = Cart::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->where('color_id', $request->color_id)
            ->where('size_id', $request->size_id)
            ->first();

        if ($cart) {

            // Update existing cart item
            $cart->qty += $request->qty;
            $cart->total = $cart->qty * $product->price;
            $cart->save();
        } else {

            // Create new cart item
            $cart = Cart::create([
                'user_id'    => $user->id,
                'product_id' => $request->product_id,
                'qty'        => $request->qty,
                'price'      => $product->price,
                'total'      => $request->qty * $product->price,
                'color_id'   => $request->color_id,
                'size_id'    => $request->size_id,
            ]);
        }

        return response()->json([
            'message' => 'Item added to cart',
            'data' => $cart
        ], 201);
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


    public function cart_truncate(Request $request)
    {

        // Security Check
        $secretKey = env('SCHEDULER_KEY');

        if ($request->header('X-API-KEY') !== $secretKey) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Action
        Cart::truncate();
        Log::info('API Trigger: Carts were cleared manually by Admin.');

        // Response
        return response()->json(['message' => 'All carts have been successfully emptied!'], 200);
    }
}

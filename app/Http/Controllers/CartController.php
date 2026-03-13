<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    //
    public function index()
    {
        $userId = Auth::id();

        $cartItems = Cart::with(['product', 'color', 'size'])
            ->where('user_id', $userId)
            ->get();

        $total = $cartItems->sum('total');

        return view('carts.index', compact('cartItems', 'total'));
    }

    public function update(Request $request, $id)
{
    $cart = Cart::findOrFail($id);

    $qty = $request->qty ?? 1;
    if ($qty < 1) $qty = 1;

    $cart->qty = $qty;
    $cart->total = $cart->price * $qty;
    $cart->save();

     $grandTotal = Cart::where('user_id', auth()->id())
        ->sum('total');


    return response()->json([
        'qty' => $cart->qty,
        'total' => $cart->total,
        'grand_total' => $grandTotal
    ]);
}


    public function remove($id)
    {
        Cart::where('user_id', Auth::id())
            ->where('id', $id)
            ->delete();

        return back()->with('success', 'Item removed');
    }

    public function product_listing(): View
    {
        $products = Product::Where('status', 'active')->latest()->paginate(5);
        foreach ($products as $product) {

            $sizeIds = $product->size ?? [];
            $productSizes = Size::whereIn('id', $sizeIds)->get();

            // colors
            $colorIds = $product->color ?? [];
            $productColors = Color::whereIn('id', $colorIds)->get();
            // // categories
            // $categoryIds = json_decode($product->category, true) ?? [];
            // $product->category_names = Category::whereIn('id', $categoryIds)->pluck('name')->toArray();

        }

        return view('products.list', compact('products', 'productColors', 'productSizes'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function liveSearch(Request $request)
    {
        $query = $request->input('q');

        $products = Product::where('status', 'active')
            ->where('name', 'LIKE', "%{$query}%")
            ->latest()
            ->get();

        $products->map(function ($product) {

            $product->images = json_decode($product->image, true) ?? [];

            $sizeIds = json_decode($product->size, true) ?? [];
            $product->sizes = Size::whereIn('id', $sizeIds)->get(['id', 'name']);

            $colorIds = json_decode($product->color, true) ?? [];
            $product->colors = Color::whereIn('id', $colorIds)->get(['id', 'name']);

            return $product;
        });

        return response()->json($products);
    }

    public function store(Request $req)
    {
        $userId = Auth::id();

        // check product exists & active
        $product = Product::where('id', $req->product_id)
            ->where('status', 'active')
            ->firstOrFail();

        // default qty = 1
        $qty = $req->qty ?? 1;

        // check if already in cart
        $cartItem = Cart::where('user_id', $userId)
            ->where('product_id', $req->product_id)
            ->where('color_id', $req->color_id)
            ->where('size_id', $req->size_id)
            ->first();

        if ($cartItem) {

            // increase qty
            $cartItem->qty += $qty;

            // update total
            $cartItem->total = $cartItem->qty * $cartItem->price;

            $cartItem->save();

        } else {

            $price = $product->price;
            $total = $price * $qty;

            Cart::create([
                'user_id' => $userId,
                'product_id' => $req->product_id,
                'color_id' => $req->color_id,
                'size_id' => $req->size_id,
                'qty' => $qty,
                'price' => $price,
                'total' => $total,
            ]);
        }

        return back()->with('success', 'Product added to cart');
    }

    public function destroy(){
        $userId = Auth::id();

        $clear = Cart::where('user_id',$userId)->get();
       Cart::destroy($clear);
        return back()->with('success', 'Cart cleared');
    }
}

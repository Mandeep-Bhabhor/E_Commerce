<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{

   public function index(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    $search = $request->input('search');
    $perPage = $request->input('per_page', 5);

    $query = Wishlist::with('product')
        ->where('user_id', $user->id);

    if ($search) {
        $query->whereHas('product', function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%");
        });
    }

    return response()->json($query->paginate($perPage));
}
    // Add to wishlist
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $user = Auth::user();

        // Check if already exists
        $exists = Wishlist::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Product already in wishlist'
            ], 200);
        }

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id
        ]);

        return response()->json([
            'message' => 'Added to wishlist'
        ], 201);
    }

    // Remove from wishlist
    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $user = Auth::user();

        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'message' => 'Product not found in wishlist'
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'message' => 'Removed from wishlist'
        ], 200);
    }
}

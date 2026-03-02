<?php

namespace App\Http\Controllers;

use App\Http\Requests\WishlistStoreRequest;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    //
    public function index()
    {
        $wishlists = Wishlist::where('user_id', Auth::id())
            ->with('product')
            ->latest()
            ->get();

        return view('wishlists.index', compact('wishlists'));
    }

    public function store(WishlistStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $userId = Auth::id();
        $productId = $data['product_id'];

        $chk = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();

        if ($chk) {
            Wishlist::where('user_id', $userId)
                ->where('product_id', $productId)
                ->delete();

            return back();
        }

        $data['user_id'] = $userId;

        Wishlist::create($data);

        return redirect()->route('products.list')
            ->with('success', 'Added to wishlist successfully.');
    }

    public function destroy($id)
    {
        Wishlist::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return back()->with('success', 'Removed from wishlist');
    }
}

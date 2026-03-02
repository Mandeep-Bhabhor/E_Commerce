<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressStoreRequest;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Tax;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $addresses = Address::where('user_id', auth()->id())->get();

        return view('address.create', compact('addresses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddressStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // merge address lines
        $data['address'] = trim(
            $data['address_line1'].' '.($data['address_line2'] ?? '')
        );

        $data['user_id'] = auth()->id();

        // remove extra fields not in DB
        unset($data['address_line1'], $data['address_line2']);

        // CHECK: only one address per type per user
        $exists = Address::where('user_id', $data['user_id'])
            ->where('type', $data['type'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'type' => "You already have a {$data['type']} address.",
            ])->withInput();
        }

        // create and capture model
        $address = Address::create($data);

        // store selected address for checkout
        session(['checkout_address_id' => $address->id]);

        return redirect()->route('checkout.index')
            ->with('success', 'Address saved successfully');
    }

    public function selectAddress(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);

        session(['checkout_address_id' => $request->address_id]);

        return redirect()->route('checkout.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Address $address)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Address $address)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Address $address)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        //
    }

    public function check_index(Request $request)
    {
        $userId = auth()->id();

        // get selected discount
        $selectedDiscountId = $request->discount_id;

        $discounts = Discount::where(function ($q) {
            $q->whereNull('start_date')
                ->orWhereDate('start_date', '<=', now());
        })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', now());
            })
            ->get();

        $cartItems = Cart::with([
            'product',
            'color',
            'size',
        ])->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Cart is empty');
        }

        $addressId = session('checkout_address_id');

        $address = null;

        if ($addressId) {
            $address = Address::where('id', $addressId)
                ->where('user_id', $userId)
                ->first();
        }

        // subtotal
        $subtotal = $cartItems->sum(fn ($item) => $item->qty * $item->price);

        // discount calculation
        $discountAmount = 0;

        if ($selectedDiscountId) {

            $discount = Discount::find($selectedDiscountId);

            if ($discount) {

                if ($discount->type === 'percentage') {
                    $discountAmount = ($subtotal * $discount->value) / 100;
                }

                if ($discount->type === 'amount') {
                    $discountAmount = $discount->value;
                }
            }
        }
        // tax
        $tax = Tax::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->first();

        $taxRate = $tax->rate ?? 0;
        $taxAmount = (($subtotal - $discountAmount) * $taxRate) / 100;

        $grandTotal = $subtotal - $discountAmount + $taxAmount;

        return view('carts.checkout', [
            'cartItems' => $cartItems,
            'address' => $address,
            'subtotal' => $subtotal,
            'taxRate' => $taxRate,
            'taxAmount' => $taxAmount,
            'discountAmount' => $discountAmount,
            'grandTotal' => $grandTotal,
            'discounts' => $discounts,
            'selectedDiscountId' => $selectedDiscountId,
        ]);
    }
}

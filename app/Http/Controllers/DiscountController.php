<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\UpdateDiscountRequest;
use App\Models\Discount;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $discounts = Discount::latest()->paginate(10);

        return view('discount.index', compact('discounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('discount.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDiscountRequest $request): RedirectResponse
    {
        Discount::create($request->validated());

        return redirect()
            ->route('discounts.index')
            ->with('success', 'Discount created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $discount = Discount::findOrFail($id);

        return view('discount.show', compact('discount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $discount = Discount::findOrFail($id);

        return view('discount.edit', compact('discount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiscountRequest $request, string $id): RedirectResponse
    {
       // dd($request);
        $discount = Discount::findOrFail($id);

        $discount->update($request->validated());

        return redirect()
            ->route('discounts.index')
            ->with('success', 'Discount updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $discount = Discount::findOrFail($id);

        $discount->delete();

        return redirect()
            ->route('discounts.index')
            ->with('success', 'Discount deleted successfully.');
    }
}

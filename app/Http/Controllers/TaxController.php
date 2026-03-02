<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaxRequest;
use App\Http\Requests\UpdateTaxRequest;
use App\Models\Tax;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $taxes = Tax::latest()->paginate(10);

        return view('taxes.index', compact('taxes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('taxes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaxRequest $request): RedirectResponse
    {
        Tax::create($request->validated());

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Tax created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $tax = Tax::findOrFail($id);

        return view('taxes.show', compact('tax'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $tax = Tax::findOrFail($id);

        return view('taxes.edit', compact('tax'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaxRequest $request, string $id): RedirectResponse
    {
        $tax = Tax::findOrFail($id);

        $tax->update($request->validated());

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Tax updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $tax = Tax::findOrFail($id);

        $tax->delete();

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Tax deleted successfully.');
    }
}

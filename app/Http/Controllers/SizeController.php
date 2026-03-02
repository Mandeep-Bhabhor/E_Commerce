<?php

namespace App\Http\Controllers;

use App\Http\Requests\SizeStoreRequest;
use App\Http\Requests\SizeUpdateRequest;
use App\Models\Size;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        //
        $sizes = Size::where('status','active')->latest()->paginate(5);

        return view('sizes.index', compact('sizes'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('sizes.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SizeStoreRequest $request): RedirectResponse
    {
        Size::create($request->validated());

        return redirect()->route('sizes.index')
            ->with('success', 'Size created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Size $size): View
    {
        return view('sizes.show', compact('size'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Size $size): View
    {
        return view('sizes.edit', compact('size'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SizeUpdateRequest $request, Size $size):RedirectResponse
    {
        //

        $size->update($request->validated());

        return redirect()->route('sizes.index')
            ->with('success', 'Size updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Size $size):RedirectResponse
    {
        //

         $size->update([
            'status'=> 'deleted',
        ]);


        return redirect()->route('sizes.index')
            ->with('success', 'Size deleted successfully');
    }
    public function search(Request $request)
    {
        $query = $request->input('q');
        if (! $query) {
            return response()->json([]);
        }

        $sizes = Size::where('name', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

       

        return response()->json($sizes);
    }
}

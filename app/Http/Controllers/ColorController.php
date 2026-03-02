<?php

namespace App\Http\Controllers;

use App\Http\Requests\ColorStoreRequest;
use App\Http\Requests\ColorUpdateRequest;
use App\Models\Color;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        //
        $colors = Color::where('status','active')->latest()->paginate(5);

        return view('colors.index', compact('colors'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('colors.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ColorStoreRequest $request): RedirectResponse
    {
        Color::create($request->validated());

        return redirect()->route('colors.index')
            ->with('success', 'Color created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Color $color): View
    {
        return view('colors.show', compact('color'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Color $color): View
    {
        return view('colors.edit', compact('color'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ColorUpdateRequest $request, Color $color):RedirectResponse
    {
        //

        $color->update($request->validated());

        return redirect()->route('colors.index')
            ->with('success', 'Color updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Color $color):RedirectResponse
    {
        //

         $color->update([
            'status'=> 'deleted',
        ]);


        return redirect()->route('colors.index')
            ->with('success', 'Color deleted successfully');
    }
     public function search(Request $request)
    {
        $query = $request->input('q');
        if (! $query) {
            return response()->json([]);
        }

        $colors = Color::where('name', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

       

        return response()->json($colors);
    }
}

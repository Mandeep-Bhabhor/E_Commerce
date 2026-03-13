<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $query = Color::query();

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }
        $colors = $query->latest()->paginate(2);

        return $colors;
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
        //
        $data = $request->validate([
            'name' => 'required', 'string',

        ]);
        if ($data) {
            Color::create($data);
        }

        return response()->json([
            'msg' => 'created successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $color = Color::findOrFail($id);

        return $color;
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
    public function update(Request $request, Color $color)
    {
        //
        $data = $request->validate([
            'name' => 'required', 'string',

        ]);
        if ($data) {
            $color->update([
                'name' => $data['name'],
            ]);
        }

        return response()->json([
            'msg' => 'updated  successfully',
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Color $color)
    {
        //
        $color->update([
            'status' => 'deleted',
        ]);

        // 2. Return a JSON response
        return response()->json([
            'message' => 'Product status updated to deleted.',
            'color' => $color, // Optional: send the updated object back
        ], 200);
    }
}

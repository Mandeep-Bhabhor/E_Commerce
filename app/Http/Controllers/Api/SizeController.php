<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $query = Size::query();

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }
        $sizes = $query->latest()->paginate(2);

        return $sizes;
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
            Size::create($data);
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
        $size = Size::findOrFail($id);

        return $size;
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
    public function update(Request $request, Size $size)
    {
        //
        $data = $request->validate([
            'name' => 'required', 'string',

        ]);
        if ($data) {
            $size->update([
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
    public function destroy(Size $size)
    {
        //
        $size->update([
            'status' => 'deleted',
        ]);

        // 2. Return a JSON response
        return response()->json([
            'message' => 'Size status updated to deleted.',
            'size' => $size, // Optional: send the updated object back
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $query = Category::query();

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }
        $Categories = $query->latest()->paginate(2);

        return $Categories;
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
            Category::create($data);
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
        $Category = Category::findOrFail($id);

        return $Category;
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
    public function update(Request $request, Category $Category)
    {
        //
        $data = $request->validate([
            'name' => 'required', 'string',

        ]);
        if ($data) {
            $Category->update([
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
    public function destroy(Category $Category)
    {
        //
        $Category->update([
            'status' => 'deleted',
        ]);

        // 2. Return a JSON response
        return response()->json([
            'message' => 'Category status updated to deleted.',
            'Category' => $Category, // Optional: send the updated object back
        ], 200);
    }
}

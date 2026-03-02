<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        //
        $categories = Category::where('status','active')->latest()->paginate(5);

        return view('categories.index', compact('categories'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('categories.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryStoreRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): View
    {
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, Category $category):RedirectResponse
    {
        //

        $category->update($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category):RedirectResponse
    {
        //

        $category->update([
            'status'=> 'deleted',
        ]);


        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully');
    }

     public function search(Request $request)
    {
        $query = $request->input('q');
        if (! $query) {
            return response()->json([]);
        }

        $categories = Category::where('name', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

       

        return response()->json($categories);
    }
}

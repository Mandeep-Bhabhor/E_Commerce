<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchItem = $request->input('search');
        $query = Product::query();

        if ($request->filled('search')) {
            // 1. Find the IDs from other tables first so we can search the JSON columns
            $sizeIds = Size::where('name', 'LIKE', "%{$searchItem}%")->pluck('id')->toArray();
            $colorIds = Color::where('name', 'LIKE', "%{$searchItem}%")->pluck('id')->toArray();
            $catIds = Category::where('name', 'LIKE', "%{$searchItem}%")->pluck('id')->toArray();

            $query->where(function ($q) use ($searchItem, $sizeIds, $colorIds, $catIds) {
                // Search in Product Text Columns
                $q->where('name', 'LIKE', "%{$searchItem}%")
                    ->orWhere('detail', 'LIKE', "%{$searchItem}%")
                    ->orWhere('price', 'LIKE', "%{$searchItem}%");

                // Search in JSON Columns using the IDs we found
                foreach ($sizeIds as $id) {
                    $q->orWhereJsonContains('size', (string) $id);
                }
                foreach ($colorIds as $id) {
                    $q->orWhereJsonContains('color', (string) $id);
                }
                foreach ($catIds as $id) {
                    $q->orWhereJsonContains('category', (string) $id);
                }
            });
        }

        // Filter out deleted
        $query->where('status', '!=', 'deleted');

        // 2. Paginate the results
        $products = $query->latest()->paginate(10);

        // 3. Manually Transform the paginated data (to add names)
        $products->getCollection()->transform(function ($product) {
            // 1. Image is already an array because of $casts
            $product->images = $product->image;

            // 2. size, color, category are also already arrays
            $sizeIds = $product->size ?? [];
            $product->size_names = Size::whereIn('id', $sizeIds)->pluck('name');

            $colorIds = $product->color ?? [];
            $product->color_names = Color::whereIn('id', $colorIds)->pluck('name');

            $categoryIds = $product->category ?? [];
            $product->category_names = Category::whereIn('id', $categoryIds)->pluck('name');

            return $product;
        });

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming request
        $data = $request->validate([
            'name' => ['required', 'unique:products,name'],
            'detail' => 'required',
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:10000'],
            'price' => ['required', 'numeric', 'min:0'],
            'sizes' => ['required', 'array', 'min:1'],
            'sizes.*' => ['integer', 'exists:sizes,id'],
            'colors' => ['required', 'array', 'min:1'],
            'colors.*' => ['integer', 'exists:colors,id'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ]);

        // 2. Handle the Image Uploads
        $imageNames = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
                $image->storeAs('products', $imageName, 'public');
                $imageNames[] = $imageName;
            }
        }

        // 3. Prepare the data for insertion
        // Note: If you have $casts in your Model, you don't need json_encode()!
        $productData = [
            'name' => $data['name'],
            'detail' => $data['detail'],
            'price' => $data['price'],
            'image' => json_encode($imageNames),
            'category' => json_encode($data['categories']),
            'color' => json_encode($data['colors']),
            'size' => json_encode($data['sizes']),
            'status' => 'active',
        ];

        // 4. Create the Product and return a JSON response
        $product = Product::create($productData);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $product = Product::findOrFail($id);

        return $product;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // 1. Validation (Use 'sometimes' for PATCH)
        $data = $request->validate([
            'name' => 'sometimes|unique:products,name,'.$product->id,
            'sizes' => 'sometimes|array',
            'categories' => 'sometimes|array',
            'colors' => 'sometimes|array',
            'removed_images' => 'sometimes|string', // JSON string of indexes
            'replace_images.*' => 'image|mimes:jpg,jpeg,png|max:10000',
            'new_images.*' => 'image|mimes:jpg,jpeg,png|max:10000',
        ]);

        // 2. Prepare Current Images
        // If you use $casts in the Model, $product->image is already an array!
        $imageNames = is_array($product->image) ? $product->image : json_decode($product->image, true) ?? [];

        // 3. Handle REMOVALS
        if ($request->filled('removed_images')) {
            $removeIndexes = json_decode($request->removed_images, true);
            foreach ($removeIndexes as $index) {
                if (isset($imageNames[$index])) {
                    Storage::disk('public')->delete('products/'.$imageNames[$index]);
                    unset($imageNames[$index]);
                }
            }
            $imageNames = array_values($imageNames);
        }

        // 4. Handle REPLACEMENTS (at specific indexes)
        if ($request->hasFile('replace_images')) {
            foreach ($request->file('replace_images') as $index => $file) {
                if (isset($imageNames[$index])) {
                    Storage::disk('public')->delete('products/'.$imageNames[$index]);
                }
                $newName = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $file->storeAs('products', $newName, 'public');
                $imageNames[$index] = $newName;
            }
        }

        // 5. Handle NEW additions
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $image) {
                $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
                $image->storeAs('products', $imageName, 'public');
                $imageNames[] = $imageName;
            }
        }

        // 6. Update the Product
        $product->update([
            'name' => $request->name ?? $product->name,
            'size' => isset($request->sizes) ? json_encode($request->sizes) : $product->size,
            'category' => isset($request->categories) ? json_encode($request->categories) : $product->category,
            'color' => isset($request->colors) ? json_encode($request->colors) : $product->color,
            'image' => json_encode(array_values($imageNames)),
        ]);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        // 1. Update the status column
        $product->update([
            'status' => 'deleted',
        ]);

        // 2. Return a JSON response
        return response()->json([
            'message' => 'Product status updated to deleted.',
            'product' => $product, // Optional: send the updated object back
        ], 200);
    }

    public function search() {}
}

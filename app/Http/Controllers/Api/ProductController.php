<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Writer\XLSX\Writer;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });
 ///base query
        $query = Product::query();

        // Search logic
        if ($search) {

            // Find matching IDs from related tables
            $sizeIds = Size::where('name', 'LIKE', "%{$search}%")
                ->pluck('id')
                ->toArray();

            $colorIds = Color::where('name', 'LIKE', "%{$search}%")
                ->pluck('id')
                ->toArray();

            $catIds = Category::where('name', 'LIKE', "%{$search}%")
                ->pluck('id')
                ->toArray();

            $query->where(function ($q) use ($search, $sizeIds, $colorIds, $catIds) {

                // Search in product columns
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('detail', 'LIKE', "%{$search}%")
                    ->orWhere('price', 'LIKE', "%{$search}%");

                // Search in JSON size column
                foreach ($sizeIds as $id) {
                    $q->orWhereJsonContains('size',$id);
                }

                // Search in JSON color column
                foreach ($colorIds as $id) {
                    $q->orWhereJsonContains('color', $id);
                }

                // Search in JSON category column
                foreach ($catIds as $id) {
                    $q->orWhereJsonContains('category', $id);
                }
            });
        }

        // Exclude deleted products
        $query->where('status', '!=', 'deleted');

        // Pagination
        $products = $query->latest()->paginate($perPage);

        // Transform paginated collection
        $products->getCollection()->transform(function ($product) {

            // Images
            $product->images = $product->image;

            // Size Names
            $sizeIds = $product->size ?? [];
            $product->size_names = Size::whereIn('id', $sizeIds)
                ->pluck('name');

            // Color Names
            $colorIds = $product->color ?? [];
            $product->color_names = Color::whereIn('id', $colorIds)
                ->pluck('name');

            // Category Names
            $categoryIds = $product->category ?? [];
            $product->category_names = Category::whereIn('id', $categoryIds)
                ->pluck('name');

            return $product;
        });

        return response()->json([
            'status' => 'success',
            'data' => $products
        ], 200);
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
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
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
            'name' => 'sometimes|unique:products,name,' . $product->id,
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
                    Storage::disk('public')->delete('products/' . $imageNames[$index]);
                    unset($imageNames[$index]);
                }
            }
            $imageNames = array_values($imageNames);
        }

        // 4. Handle REPLACEMENTS (at specific indexes)
        if ($request->hasFile('replace_images')) {
            foreach ($request->file('replace_images') as $index => $file) {
                if (isset($imageNames[$index])) {
                    Storage::disk('public')->delete('products/' . $imageNames[$index]);
                }
                $newName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('products', $newName, 'public');
                $imageNames[$index] = $newName;
            }
        }

        // 5. Handle NEW additions
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
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



    public function apiExport()
    {
        $products = Product::all();

        $filePath = storage_path('app/products.xlsx');

        $writer = new \OpenSpout\Writer\XLSX\Writer();
        $writer->openToFile($filePath);

        // Header
        $writer->addRow(
            \OpenSpout\Common\Entity\Row::fromValues([
                'Name',
                'Detail',
                'Image',
                'Category',
                'Color',
                'Size',
                'Price',
                'Status'
            ])
        );

        foreach ($products as $product) {

            $writer->addRow(
                \OpenSpout\Common\Entity\Row::fromValues([
                    $product->name,
                    $product->detail,
                    json_encode($product->image),

                    implode(',', Category::whereIn('id', $product->category)->pluck('name')->toArray()),
                    implode(',', Color::whereIn('id', $product->color)->pluck('name')->toArray()),
                    implode(',', Size::whereIn('id', $product->size)->pluck('name')->toArray()),

                    $product->price,
                    $product->status,
                ])
            );
        }

        $writer->close();

        return response()->download($filePath)->deleteFileAfterSend(true);
    }


    public function apiImport(Request $request)
    {
        // 1. Validate — return clear JSON errors, not a redirect
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv', 'max:20480'], // 20MB max
        ]);

        // 2. Get the uploaded file safely
        $file = $request->file('file');

        if (!$file || !$file->isValid()) {
            return response()->json([
                'message' => 'File upload failed or file is invalid.',
                'error'   => $file ? $file->getErrorMessage() : 'No file received.',
            ], 422);
        }

        $fullPath  = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        // 3. Pick the right reader
        if ($extension === 'csv') {
            $reader = new \OpenSpout\Reader\CSV\Reader();
        } else {
            $reader = new \OpenSpout\Reader\XLSX\Reader();
        }

        $reader->open($fullPath);

        $count       = 0;
        $skipped     = 0;
        $errors      = [];

        foreach ($reader->getSheetIterator() as $sheet) {

            $isHeader = true;

            foreach ($sheet->getRowIterator() as $rowIndex => $row) {

                // Skip header row
                if ($isHeader) {
                    $isHeader = false;
                    continue;
                }

                $cells = $row->toArray();

                // Skip completely empty rows
                if (empty(array_filter($cells, fn($c) => $c !== '' && $c !== null))) {
                    $skipped++;
                    continue;
                }

                // 4. Map columns safely with null coalescing
                $name         = trim((string) ($cells[0] ?? ''));
                $detail       = trim((string) ($cells[1] ?? ''));
                $imageCell    = trim((string) ($cells[2] ?? ''));
                $categoryCell = trim((string) ($cells[3] ?? ''));
                $colorCell    = trim((string) ($cells[4] ?? ''));
                $sizeCell     = trim((string) ($cells[5] ?? ''));
                $price        = (float) ($cells[6] ?? 0);
                $status       = trim((string) ($cells[7] ?? 'active'));

                // 5. Skip rows with no name (required field)
                if (empty($name)) {
                    $skipped++;
                    $errors[] = "Row {$rowIndex}: skipped — name is empty.";
                    continue;
                }

                // 6. Skip duplicate names to prevent unique constraint crash
                if (\App\Models\Product::where('name', $name)->exists()) {
                    $skipped++;
                    $errors[] = "Row {$rowIndex}: skipped — product '{$name}' already exists.";
                    continue;
                }

                // 7. Resolve related IDs from names
                $categoryIds = !empty($categoryCell)
                    ? \App\Models\Category::whereIn('name', array_map('trim', explode(',', $categoryCell)))->pluck('id')->toArray()
                    : [];

                $colorIds = !empty($colorCell)
                    ? \App\Models\Color::whereIn('name', array_map('trim', explode(',', $colorCell)))->pluck('id')->toArray()
                    : [];

                $sizeIds = !empty($sizeCell)
                    ? \App\Models\Size::whereIn('name', array_map('trim', explode(',', $sizeCell)))->pluck('id')->toArray()
                    : [];

                // 8. Parse images safely
                $images = [];
                if (!empty($imageCell)) {
                    $decoded = json_decode($imageCell, true);
                    $images  = is_array($decoded) ? $decoded : [];
                }

                // 9. Create product
                // NOTE: If your Product model has $casts = ['image' => 'array', ...],
                // pass raw arrays — Eloquent will handle JSON encoding automatically.
                // Do NOT json_encode() manually when $casts is set.
                try {
                    \App\Models\Product::create([
                        'name'     => $name,
                        'detail'   => $detail,
                        'image'    => $images,      // array — let $casts handle encoding
                        'category' => $categoryIds, // array
                        'color'    => $colorIds,    // array
                        'size'     => $sizeIds,     // array
                        'price'    => $price,
                        'status'   => in_array($status, ['active', 'inactive', 'deleted']) ? $status : 'active',
                    ]);
                    $count++;
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Row {$rowIndex} ('{$name}'): " . $e->getMessage();
                }
            }
        }

        $reader->close();

        return response()->json([
            'message'        => 'Import completed.',
            'total_imported' => $count,
            'total_skipped'  => $skipped,
            'errors'         => $errors, // helpful for debugging bad rows
        ]);
    }
}

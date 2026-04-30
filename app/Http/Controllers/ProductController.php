<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $products = Product::where('status', 'active')->latest()->paginate(5);

        foreach ($products as $product) {
            $sizeIds = $product->size ?? [];
            $product->size_names = Size::whereIn('id', $sizeIds)->pluck('name')->toArray();

            //  $product->productSizes = Size::whereIn('id', $sizeIds)->get();

            $colorIds = $product->color ?? [];
            $product->color_names = Color::whereIn('id', $colorIds)->pluck('name')->toArray();

            //        $product->productColors = Color::whereIn('id', $colorIds)->get();

            // Category names
            $categoryIds = $product->category ?? [];
            $product->category_names = Category::whereIn('id', $categoryIds)->pluck('name')->toArray();
        }

        // dd($products);
        return view('products.index', compact('products'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $sizes = Size::all();
        $colors = Color::all();
        $categories = Category::all();

        return view('products.create', compact('sizes', 'colors', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $imageNames = [];

        // ✅ FIX: Just assign the raw arrays!
        // Laravel will automatically JSON encode them behind the scenes.
        $data['size'] = array_map('intval', $request->sizes ?? []);
        $data['category'] = array_map('intval', $request->categories ?? []);
        $data['color'] = array_map('intval', $request->colors ?? []);

        // HANDLE MULTIPLE IMAGES
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {

                $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
                $image->storeAs('products', $imageName, 'public');

                $imageNames[] = $imageName;
            }
        }

        // ✅ FIX: Pass the array of images as well.
        // (Make sure 'image' => 'array' is in your Product model's $casts list too!)
        $data['image'] = $imageNames;

        $data['status'] = 'active';

        Product::create($data);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): View
    {

        $sizeIds = $product->size ?? [];
        $product->size_names = Size::whereIn('id', $sizeIds)->pluck('name')->toArray();

        // colors
        $colorIds = $product->color ?? [];
        $product->color_names = Color::whereIn('id', $colorIds)->pluck('name')->toArray();

        // categories
        $categoryIds = $product->category ?? [];
        $product->category_names = Category::whereIn('id', $categoryIds)->pluck('name')->toArray();

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $sizes = Size::all();
        $colors = Color::all();
        $categories = Category::all();

        return view('products.edit', compact('product', 'colors', 'sizes', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        // Store raw arrays, Laravel will JSON encode automatically
        $data['size'] = array_map('intval', $request->sizes ?? []);
        $data['category'] = array_map('intval', $request->categories ?? []);
        $data['color'] = array_map('intval', $request->colors ?? []);

        // Existing images from DB
        $imageNames = $product->image;

        if (! is_array($imageNames)) {
            $imageNames = [];
        }

        // Remove selected images
        if ($request->filled('removed_images')) {
            $removeIndexes = $request->removed_images;

            foreach ($removeIndexes as $index) {
                if (isset($imageNames[$index])) {
                    Storage::disk('public')->delete('products/'.$imageNames[$index]);
                    unset($imageNames[$index]);
                }
            }

            $imageNames = array_values($imageNames);
        }

        // Replace images at same index
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

        // Add new images
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $image) {

                $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
                $image->storeAs('products', $imageName, 'public');

                $imageNames[] = $imageName;
            }
        }

        // Save as raw array
        $data['image'] = array_values($imageNames);

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        // if ($product->image) {
        //     $oldImages = json_decode($product->image, true);
        //     if (is_array($oldImages)) {
        //         foreach ($oldImages as $oldimage) {
        //             Storage::disk('public')->delete('products/'.$oldimage);

        //         }
        //     }

        // }
        // DELETE PRODUCT RECORD
        $product->update([
            'status' => 'deleted',
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully');
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        if (! $query) {
            return response()->json([]);
        }

        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        $products->transform(function ($product) {

            $product->images = json_decode($product->image, true);

            $sizeIds = json_decode($product->size, true) ?? [];
            $product->size_names = Size::whereIn('id', $sizeIds)->pluck('name');

            $colorIds = json_decode($product->color, true) ?? [];
            $product->color_names = Color::whereIn('id', $colorIds)->pluck('name');

            $categoryIds = json_decode($product->category, true) ?? [];

            $product->category_names = Category::whereIn('id', $categoryIds)->pluck('name');

            return $product;
        });

        return response()->json($products);
    }

    public function new_route()
    {
        return view('new_route');
    }

    public function export()
    {
        $products = Product::all();

        $filePath = storage_path('app/products.xlsx');

        $writer = new Writer;
        $writer->openToFile($filePath);

        // Header
        $writer->addRow(new Row([
            Cell::fromValue('ID'),
            Cell::fromValue('Name'),
            Cell::fromValue('Detail'),
            Cell::fromValue('Image'),
            Cell::fromValue('Category'),
            Cell::fromValue('Color'),
            Cell::fromValue('Size'),
            Cell::fromValue('Price'),
            Cell::fromValue('Status'),
        ]));

        foreach ($products as $product) {

            $categoryIds = $product->category ?? [];
            $colorIds = $product->color ?? [];
            $sizeIds = $product->size ?? [];

            $categoryNames = Category::whereIn('id', $categoryIds)->pluck('name')->toArray();
            $colorNames = Color::whereIn('id', $colorIds)->pluck('name')->toArray();
            $sizeNames = Size::whereIn('id', $sizeIds)->pluck('name')->toArray();

            $writer->addRow(new Row([
                Cell::fromValue($product->id),
                Cell::fromValue($product->name),
                Cell::fromValue($product->detail),

                Cell::fromValue(json_encode($product->image)),

                Cell::fromValue(implode(',', $categoryNames)),
                Cell::fromValue(implode(',', $colorNames)),
                Cell::fromValue(implode(',', $sizeNames)),

                Cell::fromValue($product->price),
                Cell::fromValue($product->status),
            ]));
        }

        $writer->close();

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);

        $file = $request->file('file');

        // ✅ USE REAL PATH (THIS WAS YOUR BUG)
        $fullPath = $file->getRealPath();

        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv') {
            $reader = new \OpenSpout\Reader\CSV\Reader;
        } else {
            $reader = new \OpenSpout\Reader\XLSX\Reader;
        }

        $reader->open($fullPath);

        foreach ($reader->getSheetIterator() as $sheet) {

            $isHeader = true;

            foreach ($sheet->getRowIterator() as $row) {

                $cells = $row->toArray();

                if ($isHeader) {
                    $isHeader = false;

                    continue;
                }

                // DEBUG (keep this once)
                // dd($cells);
                // CORRECT (NO ID IN FILE)
                $name = $cells[0] ?? '';
                $detail = $cells[1] ?? '';
                $imageCell = $cells[2] ?? '';
                $categoryCell = $cells[3] ?? '';
                $colorCell = $cells[4] ?? '';
                $sizeCell = $cells[5] ?? '';
                $price = (float) ($cells[6] ?? 0);
                $status = $cells[7] ?? 'active';

                $categoryNames = array_filter(array_map('trim', explode(',', $categoryCell)));
                $colorNames = array_filter(array_map('trim', explode(',', $colorCell)));
                $sizeNames = array_filter(array_map('trim', explode(',', $sizeCell)));

                $categoryIds = Category::whereIn('name', $categoryNames)->pluck('id')->toArray();
                $colorIds = Color::whereIn('name', $colorNames)->pluck('id')->toArray();
                $sizeIds = Size::whereIn('name', $sizeNames)->pluck('id')->toArray();

                $imagePaths = json_decode($imageCell, true);
                $imageNames = [];

                if (is_array($imagePaths)) {
                    foreach ($imagePaths as $path) {

                        if (file_exists($path)) {
                            $ext = pathinfo($path, PATHINFO_EXTENSION);
                            $imageName = time().'_'.uniqid().'.'.$ext;

                            copy($path, storage_path('app/public/products/'.$imageName));
                            $imageNames[] = $imageName;
                        } else {
                            $imageNames[] = $path;
                        }
                    }
                }

                Product::create([
                    'name' => $name,
                    'detail' => $detail,
                    'image' => $imageNames,
                    'category' => $categoryIds,
                    'color' => $colorIds,
                    'size' => $sizeIds,
                    'price' => $price,
                    'status' => $status,
                ]);
            }
        }

        $reader->close();

        return back()->with('success', 'Imported successfully');
    }
}

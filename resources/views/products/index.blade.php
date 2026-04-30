@extends('layouts.admin')

@section('content')

    <div class="card mt-5" x-data="liveSearch()">
        <h2 class="card-header">Laravel 12 CRUD Example from scratch - ItSolutionStuff.com</h2>

        <div class="card-body">

            @session('success')
                <div class="alert alert-success">{{ $value }}</div>
            @endsession


            {{-- ADD THIS BELOW --}}
            @if (session('import_errors') && count(session('import_errors')) > 0)
                <div class="alert alert-warning">
                    <strong>Some rows had issues:</strong>
                    <ul class="mb-0">
                        @foreach (session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                {{-- LEFT SIDE: SEARCH --}}
                <div class="w-50">
                    <input type="text" placeholder="Search products..." class="form-control" x-model="query"
                        @input.debounce.400ms="search">
                </div>

                {{-- RIGHT SIDE: ACTION BUTTONS --}}
                <div class="d-flex gap-2">

                    {{-- IMPORT FORM --}}
                    {{-- ✅ Make sure enctype is present --}}
                    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" accept=".xlsx" required>
                        <button type="submit" class="btn btn-primary btn-sm">Import</button>
                    </form>

                    {{-- EXPORT BUTTON --}}
                    <a href="{{ route('products.export') }}" class="btn btn-success btn-sm">
                        Export
                    </a>

                    {{-- CREATE --}}
                    <a class="btn btn-success btn-sm" href="{{ route('products.create') }}">
                        Create
                    </a>

                </div>
            </div>
            {{-- SEARCH INPUT --}}
            {{-- <div class="w-96 mx-auto">
                <input type="text" placeholder="Search products..." class="border p-2 w-full" x-model="query"
                    @input.debounce.400ms="search">
            </div>

            <a class="btn btn-success btn-sm" href="{{ route('products.create') }}">
                Create New Product
            </a> --}}

        </div>


        {{-- TABLE --}}
        <table class="table table-bordered table-striped mt-4">
            <thead>
                <tr>
                    <th width="80px">No</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Category</th>
                    <th>Color</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Details</th>
                    <th width="250px">Action</th>
                </tr>
            </thead>

            <tbody>

                {{-- NORMAL DATA (Laravel render) --}}
                @foreach ($products as $product)
                    <tr x-show="query.length === 0">
                        <td width="80px">{{ ++$i }}</td>

                        <td>
                            {{-- We don't need json_decode because Laravel cast it to an array! --}}
                            @if (is_array($product->image) || is_object($product->image))
                                @foreach ($product->image as $img)
                                    <img src="{{ asset('storage/products/' . $img) }}" width="70" height="70"
                                        style="object-fit:cover;">
                                @endforeach
                            @endif
                        </td>

                        <td>{{ $product->name }}</td>
                        <td>{{ implode(', ', $product->size_names) }}</td>
                        <td>{{ implode(', ', $product->category_names) }}</td>
                        <td>{{ implode(', ', $product->color_names) }}</td>

                        <td>{{ $product->price }}</td>
                        <td>{{ $product->status }}</td>
                        <td>{{ $product->detail }}</td>

                        <td>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST">

                                <a class="btn btn-info btn-sm" href="{{ route('products.show', $product->id) }}">Show</a>

                                <a class="btn btn-primary btn-sm"
                                    href="{{ route('products.edit', $product->id) }}">Edit</a>

                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i>
                                    Delete</button>
                        </td>
                    </tr>
                @endforeach


                {{-- SEARCH RESULTS --}}
                <template x-for="item in results" :key="item.id">
                    <tr x-show="query.length > 0">
                        <td x-text="item.id"></td>

                        <td>
                            <template x-for="img in item.images">
                                <img :src="'/storage/products/' + img" width="70" height="70"
                                    style="object-fit:cover;">
                            </template>
                        </td>

                        <td x-text="item.name"></td>
                        <td x-text="item.size_names.join(', ')"></td>
                        <td x-text="item.category_names.join(', ')"></td>
                        <td x-text="item.color_names.join(', ')"></td>
                        <td x-text="item.price"></td>
                        <td x-text="item.status"></td>
                        <td x-text="item.detail"></td>

                        <td>

                            <a :href="`admin/products/${item.id}`" class="btn btn-info btn-sm">Show</a>

                            <a :href="`admin/products/${item.id}/edit`" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                </template>

            </tbody>
        </table>



        {{-- PAGINATION ONLY WHEN NOT SEARCHING --}}
        <div x-show="query.length === 0">
            {!! $products->links() !!}
        </div>

    </div>
    </div>

    <script>
        function liveSearch() {
            return {
                query: '',
                results: [],
                searching: false,

                search() {
                    if (this.query.length < 1) {
                        this.results = []
                        return
                    }

                    this.searching = true

                    fetch(`{{ route('products.search') }}?q=${this.query}`)
                        .then(res => res.json())
                        .then(data => {
                            this.results = data
                            this.searching = false
                        })
                }
            }
        }
    </script>

@endsection

@extends('layouts.customer')

@section('content')
    <div x-data="productSearch()">
        @session('success')
            <div class="alert alert-success">{{ $value }}</div>
        @endsession
        @session('error')
            <div class="alert alert-danger">{{ $value }}</div>
        @endsession
        {{-- SEARCH --}}
        <input type="text" class="form-control mb-4" placeholder="Search products..." x-model="query"
            @input.debounce.400ms="search">


        <div class="row">

            {{-- ================= NORMAL PRODUCTS ================= --}}
            <template x-if="query.length === 0">
                <div class="row">

                    @foreach ($products as $product)
                        @php
                            $images = $product->image ?? [];
                            $sizeIds = $product->size ?? [];
                            $colorIds = $product->color ?? [];

                            $sizes = \App\Models\Size::whereIn('id', $sizeIds)->get();
                            $colors = \App\Models\Color::whereIn('id', $colorIds)->get();
                        @endphp

                        <div class="col-md-3 mb-4">
                            <div class="card p-3 position-relative">

                                {{-- ADD TO CART ICON --}}
                                <button type="submit" form="cartForm-{{ $product->id }}"
                                    class="btn btn-dark btn-sm position-absolute" style="top:10px; right:10px; z-index:10;">
                                    <i class="fa fa-cart-plus"></i>
                                </button>


                                {{-- IMAGE CAROUSEL --}}
                                @if (count($images))
                                    <div id="carousel-{{ $product->id }}" class="carousel slide mb-2">

                                        <div class="carousel-inner">
                                            @foreach ($images as $index => $img)
                                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                                    <img src="{{ asset('storage/products/' . $img) }}" class="d-block w-100"
                                                        style="height:200px; object-fit:cover;">
                                                </div>
                                            @endforeach
                                        </div>

                                        @if (count($images) > 1)
                                            <button class="carousel-control-prev" type="button"
                                                data-bs-target="#carousel-{{ $product->id }}" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon"></span>
                                            </button>

                                            <button class="carousel-control-next" type="button"
                                                data-bs-target="#carousel-{{ $product->id }}" data-bs-slide="next">
                                                <span class="carousel-control-next-icon"></span>
                                            </button>
                                        @endif

                                    </div>
                                @endif


                                <div class="d-flex justify-content-between align-items-center">

                                    <h6 class="mb-0">{{ $product->name }}</h6>

                                    {{-- WISHLIST BUTTON --}}
                                    <form action="{{ route('wishlist.add') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                                        <button class="btn btn-light btn-sm border">
                                            <i class="fa fa-heart text-danger"></i>
                                        </button>
                                    </form>

                                </div>



                                {{-- CART FORM --}}
                                <form id="cartForm-{{ $product->id }}" action="{{ route('cart.add') }}" method="POST">
                                    @csrf

                                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                                    {{-- COLOR --}}
                                    <div class="mb-2">
                                        <strong class="small">Color</strong><br>
                                        @foreach ($colors as $color)
                                            <label class="me-2">
                                                <input type="radio" name="color_id" value="{{ $color->id }}" required>
                                                {{ $color->name }}
                                            </label>
                                        @endforeach
                                    </div>

                                    {{-- SIZE --}}
                                    <div class="mb-2">
                                        <strong class="small">Size</strong><br>
                                        @foreach ($sizes as $size)
                                            <label class="me-2">
                                                <input type="radio" name="size_id" value="{{ $size->id }}" required>
                                                {{ $size->name }}
                                            </label>
                                        @endforeach
                                    </div>

                                </form>
                                <h6 class="mb-0">${{ $product->price }}</h6>
                                {{-- VIEW PRODUCT BUTTON --}}
                                <a href="{{ route('inquiry.create', $product->id) }}"
                                    class="btn btn-outline-primary btn-sm w-100 mt-2">
                                    Product Inquiry
                                </a>

                            </div>
                        </div>
                    @endforeach

                </div>
            </template>



            {{-- ================= SEARCH RESULTS ================= --}}
            <template x-for="item in results" :key="item.id">

                <div class="col-md-3 mb-4">
                    <div class="card p-3 position-relative">

                        {{-- ADD TO CART ICON --}}
                        <button type="submit" :form="'cartForm-' + item.id" class="btn btn-dark btn-sm position-absolute"
                            style="top:10px; right:10px; z-index:10;">
                            <i class="fa fa-cart-plus"></i>
                        </button>


                        {{-- IMAGE --}}
                        <template x-if="item.images.length">
                            <img :src="'/storage/products/' + item.images[0]" style="height:200px; object-fit:cover;">
                        </template>


                        <div class="d-flex justify-content-between align-items-center">

                            <h6 class="mb-0" x-text="item.name"></h6>

                            {{-- WISHLIST --}}
                            <form method="POST" action="{{ route('wishlist.add') }}">
                                @csrf

                                <input type="hidden" name="product_id" :value="item.id">

                                <button class="btn btn-light btn-sm border">
                                    <i class="fa fa-heart text-danger"></i>
                                </button>
                            </form>

                        </div>

                        <p class="text-muted small">
                            ₹ <span x-text="item.price"></span>
                        </p>


                        {{-- FORM --}}
                        <form :id="'cartForm-' + item.id" method="POST" action="{{ route('cart.add') }}">
                            @csrf

                            <input type="hidden" name="product_id" :value="item.id">

                            {{-- COLOR --}}
                            <div class="mb-2">
                                <strong class="small">Color</strong><br>

                                <template x-for="color in item.colors">
                                    <label class="me-2">
                                        <input type="radio" name="color_id" :value="color.id" required>
                                        <span x-text="color.name"></span>
                                    </label>
                                </template>
                            </div>

                            {{-- SIZE --}}
                            <div class="mb-2">
                                <strong class="small">Size</strong><br>

                                <template x-for="size in item.sizes">
                                    <label class="me-2">
                                        <input type="radio" name="size_id" :value="size.id" required>
                                        <span x-text="size.name"></span>
                                    </label>
                                </template>
                            </div>

                        </form>

                        {{-- VIEW PRODUCT --}}
                        <a :href="'/products/' + item.id" class="btn btn-outline-dark btn-sm w-100 mt-2">
                            Buy Product
                        </a>

                    </div>
                </div>

            </template>

        </div>


        {{-- PAGINATION --}}
        <div x-show="query.length === 0">
            {!! $products->links() !!}
        </div>

    </div>



    <script>
        function productSearch() {
            return {
                query: '',
                results: [],

                search() {

                    if (this.query.length < 1) {
                        this.results = []
                        return
                    }

                    fetch(`{{ route('customer.products.search') }}?q=${this.query}`)
                        .then(res => res.json())
                        .then(data => {
                            this.results = data
                        })
                }
            }
        }
    </script>
@endsection

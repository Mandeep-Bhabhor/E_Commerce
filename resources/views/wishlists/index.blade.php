@extends('layouts.customer')

@section('content')

<div class="container mt-4">
 @session('success')
                <div class="alert alert-success">{{ $value }}</div>
            @endsession
    <h3 class="mb-4">
        <i class="fa fa-heart text-danger"></i> My Wishlist
    </h3>

    <div class="row">

        @forelse($wishlists as $item)

            @php
                $product = $item->product;
                $images = $product->image ?? [];
                $firstImg = $images[0] ?? null;
            @endphp

            <div class="col-md-3 mb-4">
                <div class="card p-3">

                    {{-- IMAGE --}}
                    @if($firstImg)
                        <img src="{{ asset('storage/products/'.$firstImg) }}"
                             style="height:200px; object-fit:cover;">
                    @endif

                    <h6 class="mt-2">{{ $product->name }}</h6>
                    <p class="text-muted small">₹ {{ $product->price }}</p>

                    <div class="d-flex gap-2">

                        {{-- VIEW --}}
                        <a href="{{ route('products.show',$product->id) }}"
                           class="btn btn-dark btn-sm w-100">
                           View
                        </a>

                        {{-- REMOVE --}}
                        <form action="{{ route('wishlist.remove',$item->id) }}"
                              method="POST">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-sm">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>

                    </div>

                </div>
            </div>

        @empty
            <div class="text-center mt-5">
                <h5>No items in wishlist</h5>
                <p class="text-muted">Go fall in love with some products first.</p>
            </div>
        @endforelse

    </div>

</div>

@endsection

@extends('layouts.admin')

@section('content')

<div class="card mt-5">
  <h2 class="card-header">Show Product</h2>
  <div class="card-body">

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-3">
        <a class="btn btn-primary btn-sm" href="{{ route('products.index') }}">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">

        {{-- IMAGE SECTION --}}
      <div class="col-md-4 mb-3">
            <strong>Images:</strong><br/>

            @php
                $images = $product->image;
            @endphp

            @if(is_array($images) && count($images))
                <div class="row g-2">
                    @foreach($images as $img)
                        <div class="col-6">
                            <img
                                src="{{ asset('storage/products/'.$img) }}"
                                class="img-fluid rounded border"
                                alt="Product Image">
                        </div>
                    @endforeach
                </div>
            @else
                <p>No images available</p>
            @endif
        </div>

        {{-- DETAILS SECTION --}}
        <div class="col-xs-12 col-sm-12 col-md-8">
            <div class="form-group mb-3">
                <strong>Name:</strong><br/>
                {{ $product->name }}
            </div>

             <div class="col-xs-12 col-sm-12 col-md-8">
            <div class="form-group mb-3">
                <strong>Color:</strong><br/>
                {{ implode(', ', $product->color_names) }}

            </div>

             <div class="col-xs-12 col-sm-12 col-md-8">
            <div class="form-group mb-3">
                <strong>Size:</strong><br/>
                {{ implode(', ', $product->size_names) }}

            </div>

            <div class="col-xs-12 col-sm-12 col-md-8">
            <div class="form-group mb-3">
                <strong>Category:</strong><br/>
                {{ implode(', ', $product->category_names) }}

            </div>

            <div class="form-group">
                <strong>Details:</strong><br/>
                {{ $product->detail }}
            </div>
        </div>

    </div>

  </div>
</div>
@endsection

@extends('products.layout')

@section('content')

<div class="card mt-5">
  <h2 class="card-header">Show Category</h2>
  <div class="card-body">

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-3">
        <a class="btn btn-primary btn-sm" href="{{ route('categories.index') }}">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">


        {{-- DETAILS SECTION --}}
        <div class="col-xs-12 col-sm-12 col-md-8">
            <div class="form-group mb-3">
                <strong>Name:</strong><br/>
                {{ $category->name }}
            </div>

           
            
         

    </div>

  </div>
</div>
@endsection

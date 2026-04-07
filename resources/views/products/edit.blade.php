@extends('layouts.admin')

@section('content')
    <div class="card mt-5">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h2 class="card-header">Edit Product</h2>
        <div class="card-body">

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a class="btn btn-primary btn-sm" href="{{ route('products.index') }}"><i class="fa fa-arrow-left"></i>
                    Back</a>
            </div>

            <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="inputName" class="form-label"><strong>Name:</strong></label>
                    <input type="text" name="name" value="{{ $product->name }}"
                        class="form-control @error('name') is-invalid @enderror" id="inputName" placeholder="Name">
                    @error('name')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                @php
                    $savedCategories = $product->category;
                    $savedColors = $product->color;
                    $savedSizes = $product->size;

                @endphp

                <div class="mb-3">
                    <label class="form-label"><strong>Category:</strong></label>

                    <select class="js-example-basic-multiple form-control" name="categories[]" multiple>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ in_array($category->id, $savedCategories) ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('categories')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>


                <div class="mb-3">
                    <label for="inputColor" class="form-label"><strong>Color:</strong></label>
                    <select class="js-example-basic-multiple form-control" name="colors[]" multiple>
                        @foreach ($colors as $color)
                            <option value="{{ $color->id }}"{{ in_array($color->id, $savedColors) ? 'selected' : '' }}>
                                {{ $color->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('colors')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label"><strong>Size:</strong></label>

                    <select class="js-example-basic-multiple form-control" name="sizes[]" multiple>
                        @foreach ($sizes as $size)
                            <option value="{{ $size->id }}" {{ in_array($size->id, $savedSizes) ? 'selected' : '' }}>
                                {{ $size->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('sizes')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="row">

                    {{-- IMAGE SECTION --}}
                    <div class="col-md-4 mb-3">
                        <strong>Images:</strong><br />

                        @php
                            $images = $product->image;
                        @endphp

                        <div class="row g-2" id="existing-images">
                            @foreach ($images as $index => $img)
                                <div class="col-6 position-relative image-box" data-index="{{ $index }}">

                                    {{-- REMOVE BUTTON --}}
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0"
                                        onclick="removeImage({{ $index }})">
                                        ✕
                                    </button>

                                    <img src="{{ asset('storage/products/' . $img) }}"
                                        class="img-fluid rounded border mb-2">

                                    <label class="small">Replace image</label>
                                    <input type="file" name="replace_images[{{ $index }}]"
                                        class="form-control form-control-sm">
                                </div>
                            @endforeach
                        </div>

                        {{-- Hidden input to store removed indexes --}}
                        <input type="hidden" name="removed_images" id="removed_images">
                    </div>
                </div>
                <div class="mb-3">
                    <div id="img_repeator">
                        <div class="img-block mb-2">

                            <label for="inputImage" class="form-label"><strong>Image:</strong></label>
                            <input type="file" name="new_images[]" multiple
                                class="form-control @error('images[]') is-invalid @enderror" id="inputName"
                                placeholder="Image" onchange="previewImage(this)">
                            <img class="mt-2 rounded" width="150" style="display:none;">

                            @error('new_images')
                                <div class="form-text text-danger">{{ $message }}</div>
                            @enderror
                            @error('new_images.*')
                                <div class="form-text text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addimg_field()">
                        + Add Image
                    </button>
                </div>
                <div class="mb-3">
                    <label for="inputPrice" class="form-label"><strong>Price:</strong></label>
                    <input type="number" name="price" value="{{ $product->price }}"
                        class="form-control @error('price') is-invalid @enderror" id="inputPrice" placeholder="Price">
                    @error('price')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="inputDetail" class="form-label"><strong>Detail:</strong></label>
                    <textarea class="form-control @error('detail') is-invalid @enderror" style="height:150px" name="detail"
                        id="inputDetail" placeholder="Detail">{{ $product->detail }}</textarea>
                    @error('detail')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk"></i> Update</button>
            </form>

        </div>
    </div>
    <script>
        let removedImages = [];

        function removeImage(index) {
            // store index
            removedImages.push(index);

            // update hidden input
            document.getElementById('removed_images').value = JSON.stringify(removedImages);

            // remove from UI
            const box = document.querySelector(`.image-box[data-index='${index}']`);
            if (box) {
                box.remove();
            }
        }

        function previewImage(input) {
            const file = input.files[0];
            const img = input.nextElementSibling;

            if (file) {
                img.src = URL.createObjectURL(file);
                img.style.display = "block";
            }
        }

        function addimg_field() {
            const div = document.getElementById("img_repeator");
            const block = document.createElement("div");
            block.className = "img-block mb-2";

            block.innerHTML = ` <label for="inputImage" class="form-label"><strong>Image:</strong></label>
                            <input type="file" name="new_images[]" multiple
                                class="form-control id="inputName"
                                placeholder="Image" onchange="previewImage(this)">
                            <img class="mt-2 rounded" width="150" style="display:none;"> `;

            div.appendChild(block);
        }

        $(document).ready(function() {
            $('.js-example-basic-multiple').select2();
        });
    </script>
@endsection

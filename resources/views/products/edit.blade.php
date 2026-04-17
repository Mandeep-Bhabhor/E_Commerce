@extends('layouts.admin')

@section('content')
    <style>
        .card {
            border-radius: 12px;
        }

        .card-header {
            font-size: 1.8rem;
            font-weight: 700;
            padding: 1rem 1.5rem;
        }

        .card-body {
            padding: 2rem;
        }

        .image-box img {
            height: 180px;
            width: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .select2-container {
            width: 100% !important;
        }
    </style>

    <div class="mx-auto" style="max-width: 900px;">
        <div class="card mt-4 border-0 shadow-sm">
            @if ($errors->any())
                <div class="alert alert-danger m-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h2 class="card-header bg-white fw-bold">Edit Product</h2>
            <div class="card-body p-4">

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-3">
                    <a class="btn btn-primary btn-sm" href="{{ route('products.index') }}">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
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
                        $savedCategories = $product->category ?? [];
                        $savedColors = $product->color ?? [];
                        $savedSizes = $product->size ?? [];
                        $images = $product->image ?? [];
                    @endphp

                    <div class="mb-3">
                        <label class="form-label"><strong>Category:</strong></label>
                        <select class="select2-multi w-100" name="categories[]" multiple data-placeholder="Select category">
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
                        <label class="form-label"><strong>Color:</strong></label>
                        <select class="select2-multi w-100" name="colors[]" multiple data-placeholder="Select colors">
                            @foreach ($colors as $color)
                                <option value="{{ $color->id }}"
                                    {{ in_array($color->id, $savedColors) ? 'selected' : '' }}>
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
                        <select class="select2-multi w-100" name="sizes[]" multiple data-placeholder="Select sizes">
                            @foreach ($sizes as $size)
                                <option value="{{ $size->id }}"
                                    {{ in_array($size->id, $savedSizes) ? 'selected' : '' }}>
                                    {{ $size->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('sizes')
                            <div class="form-text text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <strong>Existing Images:</strong>
                            <div class="row g-3 mt-2" id="existing-images">
                                @foreach ($images as $index => $img)
                                    <div class="col-lg-3 col-md-4 col-6 position-relative image-box"
                                        data-index="{{ $index }}">
                                        <button type="button"
                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2"
                                            onclick="removeImage({{ $index }})" style="z-index: 10;">
                                            ✕
                                        </button>

                                        <img src="{{ asset('storage/products/' . $img) }}"
                                            class="img-fluid mb-2 border shadow-sm">

                                        <label class="small text-muted mb-1">Replace image</label>
                                        <input type="file" name="replace_images[{{ $index }}]"
                                            class="form-control form-control-sm" onchange="previewImage(this)">
                                        <img class="mt-2" style="display:none;">
                                    </div>
                                @endforeach
                            </div>

                            <input type="hidden" name="removed_images" id="removed_images">
                        </div>
                    </div>

                    <div class="mb-4">
                        <div id="img_repeator">
                            <div class="img-block mb-3">
                                <label class="form-label"><strong>Add New Images:</strong></label>
                                <input type="file" name="new_images[]"
                                    class="form-control @error('new_images.*') is-invalid @enderror"
                                    onchange="previewImage(this)">
                                <img class="mt-2 shadow-sm"
                                    style="display:none; height: 150px; width: auto; border-radius: 8px;">
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addimg_field()">
                            + Add Another Image
                        </button>

                        @error('new_images')
                            <div class="form-text text-danger">{{ $message }}</div>
                        @enderror
                        @error('new_images.*')
                            <div class="form-text text-danger">{{ $message }}</div>
                        @enderror
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

                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Update Product
                    </button>
                </form>
            </div>
        </div>

        <script>
            let removedImages = [];

            function removeImage(index) {
                removedImages.push(index);
                document.getElementById('removed_images').value = JSON.stringify(removedImages);

                const box = document.querySelector(`.image-box[data-index='${index}']`);
                if (box) box.remove();
            }

            function previewImage(input) {
                const file = input.files[0];
                const img = input.nextElementSibling;

                if (file && img) {
                    img.src = URL.createObjectURL(file);
                    img.style.display = 'block';
                }
            }

            function addimg_field() {
                const div = document.getElementById('img_repeator');
                const block = document.createElement('div');
                block.className = 'img-block mb-3';

                block.innerHTML = `
                <label class="form-label text-muted small mb-1"><strong>Additional Image:</strong></label>
                <input type="file" name="new_images[]" class="form-control" onchange="previewImage(this)">
                <img class="mt-2 shadow-sm" style="display:none; height: 150px; width: auto; border-radius: 8px;">
            `;

                div.appendChild(block);
            }

            $(document).ready(function() {
                $('.select2-multi').each(function() {
                    $(this).select2({
                        width: '100%',
                        placeholder: $(this).data('placeholder') || 'Select options',
                        allowClear: true,
                        closeOnSelect: false
                    });
                });
            });
        </script>
    @endsection

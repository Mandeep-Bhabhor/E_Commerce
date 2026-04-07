@extends('layouts.admin')

<x-slot name="header">
    {{ isset($legalPage) ? 'Edit Legal Page' : 'Create Legal Page' }}
</x-slot>

@section('content')
<div class="max-w-5xl mx-auto pb-10">
    
    {{-- Back Button --}}
    <div class="mb-6">
        <a href="{{ route('admin.legal-pages.index') }}" class="text-gray-500 hover:text-gray-700 flex items-center text-sm">
            &larr; Back to Pages
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6">
            <form action="{{ isset($legalPage) ? route('admin.legal-pages.update', $legalPage->id) : route('admin.legal-pages.store') }}" method="POST">
                @csrf
                @if(isset($legalPage))
                    @method('PUT')
                @endif

                {{-- Page Title --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Page Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" 
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                        value="{{ old('title', $legalPage->title ?? '') }}" 
                        placeholder="e.g., Privacy Policy" required>
                    @error('title') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- URL Slug --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">URL Slug <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <div class="flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                            myshop.com/pages/
                        </span>
                        <input type="text" name="slug" 
                            class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                            value="{{ old('slug', $legalPage->slug ?? '') }}" 
                            placeholder="privacy-policy">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Leave blank to automatically generate from the title.</p>
                    @error('slug') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- TinyMCE Description Editor --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Page Description / Content</label>
                    <textarea id="description-editor" name="description">{{ old('description', $legalPage->description ?? '') }}</textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Submit Button --}}
                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded shadow-sm transition">
                        {{ isset($legalPage) ? 'Update Page' : 'Save Page' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- --- TINYMCE INTEGRATION --- --}}
{{-- We use the CDNJS link so it loads instantly without needing an API key for development --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
            selector: '#description-editor',
            height: 500,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic textcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'link table code | removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 16px; color: #374151; }'
        });
    });
</script>
@endsection
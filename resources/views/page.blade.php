@extends('layouts.customer')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            
            {{-- Breadcrumbs for easy navigation --}}
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/" class="text-decoration-none" style="color: #4facfe;">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $page->title }}</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-4 p-md-5">
                    
                    {{-- Page Title --}}
                    <h1 class="fw-bold mb-2 text-center" style="color: #2d3436;">
                        {{ $page->title }}
                    </h1>
                    
                    {{-- Last Updated Date (Great for legal compliance!) --}}
                    <p class="text-center text-muted mb-4" style="font-size: 0.9rem;">
                        Last updated: {{ $page->updated_at->format('F d, Y') }}
                    </p>
                    
                    <hr class="mb-4" style="border-color: rgba(0,0,0,0.1);">

                    {{-- 
                        THE MAGIC LINE: 
                        We use {!! !!} so the TinyMCE HTML renders properly 
                        (e.g., bold text and lists) instead of raw code tags.
                    --}}
                    <div class="legal-content text-secondary" style="line-height: 1.8; font-size: 1.05rem;">
                        @if(!empty($page->description))
                            {!! $page->description !!}
                        @else
                            <p class="text-center italic">Content is currently being updated.</p>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- Custom CSS to make the TinyMCE content look like a real document --}}
<style>
    .legal-content h1, 
    .legal-content h2, 
    .legal-content h3, 
    .legal-content h4 {
        color: #2d3436;
        font-weight: 600;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }
    .legal-content p {
        margin-bottom: 1.25rem;
    }
    .legal-content a {
        color: #4facfe;
        text-decoration: none;
        font-weight: 500;
        transition: 0.2s;
    }
    .legal-content a:hover {
        text-decoration: underline;
        color: #00f2fe;
    }
    .legal-content ul, 
    .legal-content ol {
        margin-bottom: 1.5rem;
        padding-left: 2rem;
    }
    .legal-content li {
        margin-bottom: 0.5rem;
    }
</style>
@endsection
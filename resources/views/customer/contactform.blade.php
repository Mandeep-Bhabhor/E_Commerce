@extends('layouts.customer') {{-- Assuming your main layout is named customer.blade.php --}}

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">

                {{-- Success Message Alert --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                        <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                    {{-- Card Header with your Theme Gradient --}}
                    <div class="card-header text-white p-4"
                        style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <h3 class="mb-0 fw-bold"><i class="fa fa-envelope-open-text me-2"></i> Get in Touch</h3>
                        <p class="mb-0 text-white-50 mt-1">We'd love to hear from you. Send us a message!</p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('contact.submit') }}" method="POST">
                            @csrf

                            {{-- Name Field --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary">Full Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror" {{--    placeholder="John Doe" --}}
                                    value="{{ old('name', auth()->user()->name ?? '') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email Field --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary">Email Address <span
                                        class="text-danger">*</span></label>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror" {{--  placeholder="john@example.com" --}}
                                    value="{{ old('email', auth()->user()->email ?? '') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Phone Field (Optional) --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary">Phone Number <span
                                        class="text-muted fw-normal">(Optional)</span></label>
                                <input type="text" name="phone"
                                    class="form-control @error('phone') is-invalid @enderror" {{--    placeholder="+1 234 567 890" --}}
                                    value="{{ old('phone', auth()->user()->phone ?? '') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Subject Field --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary">Subject <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="subject"
                                    class="form-control @error('subject') is-invalid @enderror"
                                   {{--  placeholder="E.g., Order Inquiry, Returns, Feedback" --}}value="{{ old('subject') }}"
                                    required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Message Field --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold text-secondary">Your Message <span
                                        class="text-danger">*</span></label>
                                <textarea name="message" rows="5" class="form-control @error('message') is-invalid @enderror"
                                    {{--   placeholder="How can we help you today?" --}} required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Submit Button --}}
                            <button type="submit" class="btn btn-accent w-100 py-2 fs-5 shadow-sm"
                                style="border-radius: 10px;">
                                <i class="fa fa-paper-plane me-2"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

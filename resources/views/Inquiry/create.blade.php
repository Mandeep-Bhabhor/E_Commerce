@extends('layouts.customer')

@section('content')
<div class="container mt-4">

    {{-- SUCCESS MESSAGE --}}
    @session('success')
        <div class="alert alert-success">
            {{ $value }}
        </div>
    @endsession

    <div class="row">

        {{-- ================= INQUIRY FORM ================= --}}
        <div class="col-md-5">

            <div class="card p-4 shadow-sm">

                <h4 class="mb-3">Product Inquiry</h4>

                {{-- PRODUCT INFO --}}
                <div class="mb-3">
                    <strong>Product:</strong> {{ $product->name }} <br>
                    <strong>Price:</strong> ₹{{ $product->price }}
                </div>

                {{-- FORM --}}
                <form action="{{ route('inquiry.store') }}" method="POST">
                    @csrf

                    <input type="hidden"
                           name="product_id"
                           value="{{ $product->id }}">

                    {{-- INQUIRY TITLE --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Inquiry Title
                        </label>

                        <input type="text"
                               name="inquiry"
                               class="form-control"
                               placeholder="e.g. Bulk order, availability..."
                               required>
                    </div>

                    {{-- MESSAGE --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Message
                        </label>

                        <textarea name="message"
                                  class="form-control"
                                  rows="4"
                                  placeholder="Write your question here..."
                                  required></textarea>
                    </div>

                    {{-- SUBMIT --}}
                    <button type="submit"
                            class="btn btn-dark w-100">
                        Send Inquiry
                    </button>

                </form>

            </div>

        </div>


        {{-- ================= PREVIOUS INQUIRIES ================= --}}
        <div class="col-md-7">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h4 class="mb-4">
                        Previous Inquiries
                    </h4>

                    @forelse($previousInquiries as $inquiry)

                        <div class="border rounded p-3 mb-4">

                            {{-- TITLE --}}
                            <div class="mb-2">
                                <strong>
                                    {{ $inquiry->inquiry }}
                                </strong>
                            </div>

                            {{-- USER MESSAGE --}}
                            <div class="mb-3">

                                <small class="text-muted">
                                    Your Message
                                </small>

                                <div class="bg-light rounded p-3 mt-1">
                                    {{ $inquiry->message }}
                                </div>

                            </div>

                            {{-- ADMIN REPLY --}}
                            @if($inquiry->reply)

                                <div>

                                    <small class="text-primary fw-bold">
                                        Admin Reply
                                    </small>

                                    <div class="border border-primary rounded p-3 mt-1 bg-white">
                                        {{ $inquiry->reply }}
                                    </div>

                                </div>

                            @else

                                <div class="text-muted small">
                                    Waiting for admin reply...
                                </div>

                            @endif

                            {{-- DATE --}}
                            <div class="text-end mt-2">
                                <small class="text-muted">
                                    {{ $inquiry->created_at->format('d M Y h:i A') }}
                                </small>
                            </div>

                        </div>

                    @empty

                        <div class="text-center text-muted py-5">
                            No previous inquiries found
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>
@endsection
@extends('layouts.admin')

@section('content')

<div class="card shadow-sm border-0">

    <div class="card-body">

        <h3 class="mb-4">Inquiry Details</h3>

        {{-- USER --}}
        <div class="mb-3">
            <strong>User:</strong>
            {{ $inquiry->user->name }}
        </div>

        {{-- PRODUCT --}}
        <div class="mb-3">
            <strong>Product:</strong>
            {{ $inquiry->product->name }}
        </div>

        {{-- TITLE --}}
        <div class="mb-3">
            <strong>Inquiry:</strong>
            {{ $inquiry->inquiry }}
        </div>

        {{-- MESSAGE --}}
        <div class="mb-4">
            <strong>Message:</strong>

            <div class="border rounded p-3 bg-light mt-2">
                {{ $inquiry->message }}
            </div>
        </div>

        <hr>

        {{-- REPLY FORM --}}
        <form action="{{ route('admin.inquiries.reply', $inquiry->id) }}"
              method="POST">

            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">
                    Admin Reply
                </label>

                <textarea name="reply"
                          rows="5"
                          class="form-control"
                          placeholder="Write reply here...">{{ $inquiry->reply }}</textarea>
            </div>

            <button class="btn btn-primary">
                Save Reply
            </button>

        </form>

    </div>

</div>

@endsection
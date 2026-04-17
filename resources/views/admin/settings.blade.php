@extends('layouts.admin')

@section('content')
    <style>
        .card { border-radius: 12px; }
        .card-header { font-size: 1.5rem; font-weight: 700; padding: 1rem 1.5rem; }
    </style>

    <div class="mx-auto" style="max-width: 900px;">
        <div class="card mt-4 border-0 shadow-sm">
            <h2 class="card-header bg-white fw-bold">Select What to Show</h2>
            <div class="card-body p-4">

                <form method="POST" action="{{ route('settings.updateVisibility') }}">
                    @csrf

                    {{-- HEADER LOGO --}}
                    <div class="mb-4 border-bottom pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Header Logo</span>
                            <div class="form-check form-switch fs-4 mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" name="show_header_logo"
                                    {{ $setting->show_header_logo ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-2">
                            @foreach($setting->header_logo ?? [] as $logo)
                                <img src="{{ asset('storage/'.$logo) }}" class="img-thumbnail" style="height: 60px; width: auto;">
                            @endforeach
                        </div>
                    </div>

                    {{-- FOOTER LOGO --}}
                    <div class="mb-4 border-bottom pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Footer Logo</span>
                            <div class="form-check form-switch fs-4 mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" name="show_footer_logo"
                                    {{ $setting->show_footer_logo ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-2">
                            @foreach($setting->footer_logo ?? [] as $logo)
                                <img src="{{ asset('storage/'.$logo) }}" class="img-thumbnail" style="height: 50px; width: auto;">
                            @endforeach
                        </div>
                    </div>

                    {{-- EMAILS --}}
                    <div class="mb-4 border-bottom pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Emails</span>
                            <div class="form-check form-switch fs-4 mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" name="show_email"
                                    {{ $setting->show_email ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="mt-2 text-muted">
                            @foreach($setting->email ?? [] as $email)
                                <div><i class="fa fa-envelope me-2"></i>{{ $email }}</div>
                            @endforeach
                        </div>
                    </div>

                    {{-- PHONE --}}
                    <div class="mb-4 border-bottom pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Phone</span>
                            <div class="form-check form-switch fs-4 mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" name="show_phone"
                                    {{ $setting->show_phone ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="mt-2 text-muted">
                            @foreach($setting->phone ?? [] as $phone)
                                <div><i class="fa fa-phone me-2"></i>{{ $phone }}</div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg mt-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Visibility
                    </button>

                </form>
            </div>
        </div>
    </div>
@endsection
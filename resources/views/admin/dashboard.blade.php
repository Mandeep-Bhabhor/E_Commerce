@extends('layouts.admin')

@section('content')
    <style>
        .card { border-radius: 12px; }
        .card-header { font-size: 1.5rem; font-weight: 700; padding: 1rem 1.5rem; }
    </style>

    <div class="mx-auto" style="max-width: 900px;">
        <div class="card mt-4 border-0 shadow-sm">
            <h2 class="card-header bg-white fw-bold">Header & Footer Settings</h2>
            <div class="card-body p-4">

                <form method="POST" action="{{ route('settings.save') }}" enctype="multipart/form-data">
                    @csrf

                    @php
                        function decodeField($value)
                        {
                            if (is_null($value)) {
                                return [];
                            }
                            $decoded = is_string($value) ? json_decode($value, true) : $value;
                            return is_array($decoded) ? $decoded : [$value];
                        }

                        $emails = $settings ? decodeField($settings->email) : [];
                        $phones = $settings ? decodeField($settings->phone) : [];

                        $headerLogo = $settings->header_logo ?? null;
                        $footerLogo = $settings->footer_logo ?? null;
                    @endphp

                    <div class="mb-4">
                        <label class="form-label fw-bold">Emails</label>
                        <div id="emailRepeater">
                            @forelse ($emails as $email)
                                <div class="d-flex gap-2 mb-2 repeater-item">
                                    <input type="email" name="emails[]" class="form-control"
                                        value="{{ $email }}" placeholder="Enter email">
                                    <button type="button" class="btn btn-outline-danger remove-btn"><i class="fa fa-times"></i></button>
                                </div>
                            @empty
                                <div class="d-flex gap-2 mb-2 repeater-item">
                                    <input type="email" name="emails[]" class="form-control"
                                        placeholder="Enter email">
                                    <button type="button" class="btn btn-outline-danger remove-btn"><i class="fa fa-times"></i></button>
                                </div>
                            @endforelse
                        </div>
                        <button type="button" id="addEmail" class="btn btn-sm btn-primary mt-1">+ Add Email</button>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Phones</label>
                        <div id="phoneRepeater">
                            @forelse ($phones as $phone)
                                <div class="d-flex gap-2 mb-2 repeater-item">
                                    <input type="text" name="phones[]" class="form-control"
                                        value="{{ $phone }}" placeholder="Enter phone">
                                    <button type="button" class="btn btn-outline-danger remove-btn"><i class="fa fa-times"></i></button>
                                </div>
                            @empty
                                <div class="d-flex gap-2 mb-2 repeater-item">
                                    <input type="text" name="phones[]" class="form-control"
                                        placeholder="Enter phone">
                                    <button type="button" class="btn btn-outline-danger remove-btn"><i class="fa fa-times"></i></button>
                                </div>
                            @endforelse
                        </div>
                        <button type="button" id="addPhone" class="btn btn-sm btn-primary mt-1">+ Add Phone</button>
                    </div>

                    <hr class="my-4">

                    <div class="mb-4">
                        <label class="form-label fw-bold">Header Logo</label>
                        @if ($headerLogo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $headerLogo) }}" class="img-thumbnail" style="height: 80px; width: auto;">
                                <p class="text-muted small mt-1 mb-0">Current Logo</p>
                            </div>
                        @endif
                        <input type="file" name="header_logo" class="form-control">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Footer Logo</label>
                        @if ($footerLogo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $footerLogo) }}" class="img-thumbnail" style="height: 80px; width: auto;">
                                <p class="text-muted small mt-1 mb-0">Current Logo</p>
                            </div>
                        @endif
                        <input type="file" name="footer_logo" class="form-control">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Google Map Embed Code</label>
                        <textarea name="map_iframe" rows="3" class="form-control"
                            placeholder='Paste your <iframe...> tag from Google Maps here'>{{ $settings->map_iframe ?? '' }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function addRow(containerId) {
            let container = document.getElementById(containerId);
            let items = container.querySelectorAll('.repeater-item');
            let newRow = items[0].cloneNode(true);
            newRow.querySelectorAll('input').forEach(i => i.value = '');
            container.appendChild(newRow);
        }

        document.getElementById('addEmail').onclick = () => addRow('emailRepeater');
        document.getElementById('addPhone').onclick = () => addRow('phoneRepeater');

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-btn')) {
                let repeater = e.target.closest('[id$="Repeater"]');
                if (repeater.querySelectorAll('.repeater-item').length > 1) {
                    e.target.closest('.repeater-item').remove();
                }
            }
        });
    </script>
@endsection
@extends('layouts.admin')

<x-slot name="header">
    Settings
</x-slot>

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white p-6 rounded-lg border">
            <h2 class="text-xl font-semibold mb-6">Header & Footer Settings</h2>

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

                    // We treat logos as single strings now, not arrays
                    $headerLogo = $settings->header_logo ?? null;
                    $footerLogo = $settings->footer_logo ?? null;
                @endphp

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Emails</label>
                    <div id="emailRepeater" class="space-y-2">
                        @forelse ($emails as $email)
                            <div class="flex gap-2 repeater-item">
                                <input type="email" name="emails[]" class="w-full border rounded px-3 py-2 text-sm"
                                    value="{{ $email }}" placeholder="Enter email">
                                <button type="button" class="remove-btn text-red-500">X</button>
                            </div>
                        @empty
                            <div class="flex gap-2 repeater-item">
                                <input type="email" name="emails[]" class="w-full border rounded px-3 py-2 text-sm"
                                    placeholder="Enter email">
                                <button type="button" class="remove-btn text-red-500">X</button>
                            </div>
                        @endforelse
                    </div>
                    <button type="button" id="addEmail" class="mt-2 px-3 py-1 bg-indigo-600 text-dark text-sm rounded">+
                        Add Email</button>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Phones</label>
                    <div id="phoneRepeater" class="space-y-2">
                        @forelse ($phones as $phone)
                            <div class="flex gap-2 repeater-item">
                                <input type="text" name="phones[]" class="w-full border rounded px-3 py-2 text-sm"
                                    value="{{ $phone }}" placeholder="Enter phone">
                                <button type="button" class="remove-btn text-red-500">X</button>
                            </div>
                        @empty
                            <div class="flex gap-2 repeater-item">
                                <input type="text" name="phones[]" class="w-full border rounded px-3 py-2 text-sm"
                                    placeholder="Enter phone">
                                <button type="button" class="remove-btn text-red-500">X</button>
                            </div>
                        @endforelse
                    </div>
                    <button type="button" id="addPhone" class="mt-2 px-3 py-1 bg-indigo-600 text-dark text-sm rounded">+
                        Add Phone</button>
                </div>

                <hr class="my-6">

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Header Logo</label>
                    @if ($headerLogo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $headerLogo) }}" class="h-20 w-auto rounded border mb-2">
                            <p class="text-xs text-gray-500">Current Logo</p>
                        </div>
                    @endif
                    <input type="file" name="header_logo" class="w-full border rounded px-3 py-2 text-sm">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Footer Logo</label>
                    @if ($footerLogo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $footerLogo) }}" class="h-20 w-auto rounded border mb-2">
                            <p class="text-xs text-gray-500">Current Logo</p>
                        </div>
                    @endif
                    <input type="file" name="footer_logo" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Google Map Embed Code</label>
                    <textarea name="map_iframe" rows="3" class="w-full border rounded px-3 py-2 text-sm"
                        placeholder='Paste your <iframe...> tag from Google Maps here'>{{ $settings->map_iframe ?? '' }}</textarea>
                    {{-- <p class="text-xs text-gray-500 mt-1">Search your address on Google Maps > Click "Share" > "Embed a map"
                        > "Copy HTML".</p> --}}
                </div>

                <button type="submit"
                    class="px-5 py-2 bg-green-600 text-dark-grey text-sm rounded shadow-sm hover:bg-green-700">
                    Save Settings
                </button>
            </form>
        </div>
    </div>

    <script>
        // Simple row adder for Emails and Phones only
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
            if (e.target.classList.contains('remove-btn')) {
                let repeater = e.target.closest('[id$="Repeater"]');
                if (repeater.querySelectorAll('.repeater-item').length > 1) {
                    e.target.closest('.repeater-item').remove();
                }
            }
        });
    </script>
@endsection

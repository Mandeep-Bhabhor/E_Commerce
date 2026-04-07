@extends('layouts.admin')

<x-slot name="header">
    Layout Settings
</x-slot>

@section('content')

<div class="max-w-4xl mx-auto bg-white p-6 rounded border">

    <h2 class="text-xl font-semibold mb-6">Select What to Show</h2>

    <form method="POST" action="{{ route('settings.updateVisibility') }}">
        @csrf

        {{-- HEADER LOGO --}}
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <span class="font-medium">Header Logo</span>
                <input type="checkbox" name="show_header_logo"
                    {{ $setting->show_header_logo ? 'checked' : '' }}>
            </div>

            <div class="mt-2 flex gap-2">
                @foreach($setting->header_logo ?? [] as $logo)
                    <img src="{{ asset('storage/'.$logo) }}" width="60" class="border rounded">
                @endforeach
            </div>
        </div>

        {{-- FOOTER LOGO --}}
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <span class="font-medium">Footer Logo</span>
                <input type="checkbox" name="show_footer_logo"
                    {{ $setting->show_footer_logo ? 'checked' : '' }}>
            </div>

            <div class="mt-2 flex gap-2">
                @foreach($setting->footer_logo ?? [] as $logo)
                    <img src="{{ asset('storage/'.$logo) }}" width="50" class="border rounded">
                @endforeach
            </div>
        </div>

        {{-- EMAILS --}}
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <span class="font-medium">Emails</span>
                <input type="checkbox" name="show_email"
                    {{ $setting->show_email ? 'checked' : '' }}>
            </div>

            <div class="mt-2">
                @foreach($setting->email ?? [] as $email)
                    <div class="text-sm text-gray-700">{{ $email }}</div>
                @endforeach
            </div>
        </div>

        {{-- PHONE --}}
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <span class="font-medium">Phone</span>
                <input type="checkbox" name="show_phone"
                    {{ $setting->show_phone ? 'checked' : '' }}>
            </div>

            <div class="mt-2">
                @foreach($setting->phone ?? [] as $phone)
                    <div class="text-sm text-gray-700">{{ $phone }}</div>
                @endforeach
            </div>
        </div>

        <button type="submit"
            class="px-5 py-2 bg-indigo-600 text-white rounded">
            Save
        </button>

    </form>

</div>

@endsection
@extends('layouts.admin')

<x-slot name="header">
    Message Details
</x-slot>

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Back Button --}}
        <div>
            <a href="{{ route('admin.contacts.index') }}" class="text-gray-500 hover:text-gray-700 flex items-center text-sm">
                &larr; Back to Inbox
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- LEFT COLUMN: The Message --}}
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $contact->subject }}</h2>
                            <p class="text-sm text-gray-500 mt-1">Received on
                                {{ $contact->created_at->format('l, F j, Y \a\t h:i A') }}</p>
                        </div>
                        <span
                            class="px-3 py-1 text-xs font-semibold rounded-full {{ $contact->status === 'resolved' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($contact->status) }}
                        </span>
                    </div>

                    <hr class="my-4 border-gray-100">

                    <div class="prose max-w-none text-gray-700 whitespace-pre-wrap">{{ $contact->message }}</div>
                </div>

                {{-- REPLY FORM --}}
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Reply to Customer</h3>

                    {{-- Updated Action Route --}}
                    <form action="{{ route('admin.contacts.reply', $contact->id) }}" method="POST">
                        @csrf

                        <textarea name="reply_message" rows="5" required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Type your reply here..."></textarea>

                        @error('reply_message')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror

                        <div class="mt-4 flex justify-end">
                            {{-- Changed type from "button" to "submit" --}}
                            <button type="submit"
                                class="bg-indigo-600 text-dark px-4 py-2 rounded shadow-sm hover:bg-indigo-700 transition">
                                Send Email Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- RIGHT COLUMN: Customer Info Card --}}
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 h-fit">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Sender Details</h3>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-400">Name</p>
                        <p class="font-medium text-gray-900">{{ $contact->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Email Address</p>
                        <a href="mailto:{{ $contact->email }}"
                            class="font-medium text-indigo-600 hover:underline">{{ $contact->email }}</a>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Phone Number</p>
                        <p class="font-medium text-gray-900">{{ $contact->phone ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Account Status</p>
                        @if ($contact->user_id)
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                Registered User
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                Guest Visitor
                            </span>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

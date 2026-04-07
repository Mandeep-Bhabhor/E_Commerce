@extends('layouts.admin')

<x-slot name="header">
    Customer Inquiries
</x-slot>

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($messages as $msg)
                <tr class="hover:bg-gray-50 transition-colors {{ $msg->status === 'pending' ? 'bg-blue-50/30' : '' }}">
                    
                    {{-- Status Badge --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($msg->status === 'pending')
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">New</span>
                        @elseif($msg->status === 'read')
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Read</span>
                        @else
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Resolved</span>
                        @endif
                    </td>

                    {{-- Customer Info --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $msg->name }}</div>
                        <div class="text-sm text-gray-500">{{ $msg->email }}</div>
                    </td>

                    {{-- Subject --}}
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 font-medium">{{ $msg->subject }}</div>
                        <div class="text-sm text-gray-500 truncate max-w-xs">{{ Str::limit($msg->message, 40) }}</div>
                    </td>

                    {{-- Date --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $msg->created_at->format('M d, Y h:i A') }}
                    </td>

                    {{-- Action Button --}}
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.contacts.show', $msg->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded">Open</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        No messages found. Your inbox is clean!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination Links --}}
        @if($messages->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $messages->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
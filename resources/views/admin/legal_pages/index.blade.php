@extends('layouts.admin')

<x-slot name="header">
    Legal Pages Management
</x-slot>

@section('content')
<div class="max-w-7xl mx-auto">
    
    {{-- Top Bar with Add Button --}}
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">All Pages</h2>
        <a href="{{ route('admin.legal-pages.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow-sm transition">
            + Create New Page
        </a>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Pages Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($pages as $page)
                <tr class="hover:bg-gray-50 transition-colors">
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $page->title }}</div>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            /pages/{{ $page->slug }}
                        </span>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $page->updated_at->format('M d, Y') }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-3">
                            {{-- Edit Button --}}
                            <a href="{{ route('admin.legal-pages.edit', $page->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            
                            {{-- Delete Form --}}
                            <form action="{{ route('admin.legal-pages.destroy', $page->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this page?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        No legal pages created yet. Click "Create New Page" to get started!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
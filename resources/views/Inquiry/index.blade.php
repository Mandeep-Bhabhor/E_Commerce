@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Product Inquiries</h3>
</div>

{{-- SUCCESS MESSAGE --}}
@session('success')
    <div class="alert alert-success">
        {{ $value }}
    </div>
@endsession

<div class="card shadow-sm border-0">
    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Product</th>
                        <th>Inquiry</th>
                        <th>Message</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($inquiries as $inquiry)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $inquiry->user->name ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $inquiry->product->name ?? 'Deleted Product' }}
                            </td>

                            <td>
                                {{ $inquiry->inquiry }}
                            </td>

                            <td>
                                {{ Str::limit($inquiry->message, 50) }}
                            </td>

                            <td>
                                <a href="{{ route('admin.inquiries.show', $inquiry->id) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fa fa-eye me-1"></i> View
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No inquiries found
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>
</div>

{{-- PAGINATION --}}
<div class="mt-4">
    {{ $inquiries->links() }}
</div>

@endsection
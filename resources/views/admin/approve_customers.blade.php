@extends('layouts.admin')

@section('content')
    <div class="container py-4">

        <div class="card shadow border-0">

            <div class="card-header bg-white">
                <h3 class="fw-bold mb-0">Approve Customers</h3>
            </div>

            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="table-responsive">

                    <table class="table table-bordered align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($users as $user)
                                <tr>

                                    <td>{{ $user->id }}</td>

                                    <td>{{ $user->name }}</td>

                                    <td>{{ $user->email }}</td>

                                    <td>

                                        @if ($user->status == 'active')
                                            <span class="badge bg-success">
                                                Approved
                                            </span>
                                        @elseif($user->status == 'pending')
                                            <span class="badge bg-warning text-dark">
                                                Pending
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                Blocked
                                            </span>
                                        @endif

                                    </td>

                                    <td>

                                        @if ($user->status != 'active')
                                            <form action="{{ route('admin.customer.approve', $user->id) }}" method="POST">

                                                @csrf
                                                @method('PUT')

                                                <button class="btn btn-success btn-sm">
                                                    Approve
                                                </button>

                                            </form>
                                        @else
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                Already Approved
                                            </button>
                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No Customers Found
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>
@endsection

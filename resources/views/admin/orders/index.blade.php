@extends('layouts.admin')

@section('content')

    <div class="card mt-5" x-data="liveSearch()">
        <h2 class="card-header">All Orders</h2>

        <div class="card-body">
            @session('success')
                <div class="alert alert-success">{{ $value }}</div>
            @endsession

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">

                {{-- SEARCH INPUT --}}
                <div class="w-96 mx-auto">
                    <input type="text" placeholder="Search products..." class="border p-2 w-full" x-model="query"
                        @input.debounce.400ms="search">
                </div>

                <a class="btn btn-success btn-sm" href="{{ route('products.create') }}">
                    Create New Product
                </a>

            </div>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Order No</th>
                        <th>User</th>
                        <th>Address</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th width="160">Action</th>
                    </tr>
                </thead>

                <tbody>

                    {{-- Laravel renderr --}}
                    @forelse($orders as $order)
                        <tr x-show="query.length === 0">
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->order_no }}</td>

                            <td>
                                {{ $order->user->name ?? '-' }}
                                <br>
                                <small>{{ $order->user->email ?? '' }}</small>
                            </td>

                            <td>
                                {{ $order->address->address ?? '-' }} <br>
                                {{ $order->address->city ?? '' }},
                                {{ $order->address->state ?? '' }}
                            </td>

                            <td>
                                ₹ {{ $order->grand_total }}
                            </td>

                            <td>
                                <span class="badge bg-dark">
                                    {{ ucfirst($order->order_status) }}
                                </span>
                            </td>

                            <td>
                                {{ $order->created_at->format('d M Y') }}
                            </td>

                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-info btn-sm">
                                    View
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                No orders yet
                            </td>
                        </tr>
                    @endforelse

                    {{-- Search results --}}
                    <template x-for="item in results" :key="item.id">
                        <tr x-show="query.length > 0">
                            <td x-text="item.id"></td>

                            <td x-text="item.order_no"></td>

                            <td x-text="item.user ? item.user.name : ''"></td>

                            <td x-text="item.address ? item.address.address : ''"></td>

                            <td x-text="item.grand_total"></td>

                            <td x-text="item.order_status"></td>

                            <td x-text="item.created_at"></td>



                            <td>

                                <a :href="`/admin/orders/${item.id}`" class="btn btn-info btn-sm">Show</a>

                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div x-show="query.length === 0">
                {!! $orders->links() !!}
            </div>

        </div>
    </div>
    <script>
        function liveSearch() {
            return {
                query: '',
                results: [],
                searching: false,

                search() {
                    if (this.query.length < 1) {
                        this.results = []
                        return
                    }

                    this.searching = true

                    fetch(`{{ route('admin.orders.search') }}?q=${this.query}`)
                        .then(res => res.json())
                        .then(data => {
                            this.results = data
                            this.searching = false
                        })
                }
            }
        }
    </script>

@endsection

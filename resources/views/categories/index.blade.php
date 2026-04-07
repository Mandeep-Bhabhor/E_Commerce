@extends('layouts.admin')

@section('content')

    <div class="card mt-5" x-data="liveSearch()">
        <h2 class="card-header">Laravel 12 CRUD Example from scratch - ItSolutionStuff.com</h2>
        <div class="card-body">

            @session('success')
                <div class="alert alert-success" role="alert"> {{ $value }} </div>
            @endsession

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                {{-- Search Input --}}
                <div class="w-50 mx-auto">
                    <input type="text" placeholder="Search Category..." class="border p-2 w-full" x-model="query"
                        @input.debounce.300ms="search">
                </div>
                <a class="btn btn-success btn-sm" href="{{ route('categories.create') }}"> <i class="fa fa-plus"></i> Create New
                    Category</a>
            </div>

            <table class="table table-bordered table-striped mt-4">
                <thead>
                    <tr>
                        <th width="80px">No</th>
                        <th>Name</th>
                        <th width="250px">Action</th>
                    </tr>
                </thead>

                <tbody>
                    {{-- normal data --}}

                    @forelse ($categories as $category)
                        <tr x-show="query.length === 0">
                            <td>{{ ++$i }}</td>

                            <td>{{ $category->name }}</td>
                            <td>
                                <form action="{{ route('categories.destroy', $category->id) }}" method="POST">

                                    <a class="btn btn-info btn-sm" href="{{ route('categories.show', $category->id) }}"><i
                                            class="fa-solid fa-list"></i> Show</a>

                                    <a class="btn btn-primary btn-sm" href="{{ route('categories.edit', $category->id) }}"><i
                                            class="fa-solid fa-pen-to-square"></i> Edit</a>

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i>
                                        Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">There are no data.</td>
                        </tr>
                    @endforelse


                    {{-- SEARCH RESULTS --}}
                    <template x-for="item in results" :key="item.id">
                        <tr x-show="query.length > 0">
                            <td x-text="item.id"></td>
                            <td x-text="item.name"></td>
                            <td>
                                <a :href="`/categories/${item.id}`" class="btn btn-info btn-sm">Show</a>
                                <a :href="`/categories/${item.id}/edit`" class="btn btn-primary btn-sm">Edit</a>
                            </td>
                        </tr>
                    </template>
                </tbody>

            </table>
            <div x-show="query.length === 0">
                {!! $categories->links() !!}
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

                    fetch(`{{ route('categories.search') }}?q=${this.query}`)
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

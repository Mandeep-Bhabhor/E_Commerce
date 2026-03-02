@extends('products.customer_layout')

@section('content')
    @session('success')
        <div class="alert alert-success">{{ $value }}</div>
    @endsession
    @session('error')
        <div class="alert alert-danger">{{ $value }}</div>
    @endsession
    <div class="card mt-5">
        {{-- USER SAVED ADDRESSES --}}
        @if ($addresses->count())
            <form action="{{ route('select.address') }}" method="POST">
                @csrf

                <label class="form-label"><strong>Select Saved Address</strong></label>

                <select class="form-control" name="address_id" id="addressSelect" required>
                    <option value="">-- Choose Address --</option>

                    @foreach ($addresses as $addr)
                        <option value="{{ $addr->id }}" data-address="{{ $addr->address }}"
                            data-city="{{ $addr->city }}" data-state="{{ $addr->state }}"
                            data-pincode="{{ $addr->pincode }}" data-type="{{ $addr->type }}">
                            {{ ucfirst($addr->type) }} — {{ $addr->city }}
                        </option>
                    @endforeach
                </select>

                <button class="btn btn-dark mt-3 w-100">
                    Deliver to this address
                </button>

            </form>
        @endif

        {{-- DISPLAY SELECTED ADDRESS --}}
        <div id="selectedAddressBox" class="border rounded p-3 mt-3 bg-light" style="display:none;">

            <h6 class="mb-1">Selected Address</h6>

            <p class="mb-1" id="addr_line"></p>
            <p class="mb-1">
                <span id="addr_city"></span>,
                <span id="addr_state"></span> -
                <span id="addr_pin"></span>
            </p>
            <span class="badge bg-dark" id="addr_type"></span>
        </div>

    </div>


    <h2 class="card-header">Shipping Address</h2>

    <div class="card-body">

        <div class="d-flex justify-content-end mb-3">
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('cart.index') }}">
                <i class="fa fa-arrow-left"></i> Back to Cart
            </a>
        </div>

        <form action="{{ route('address.store') }}" method="POST">
            @csrf

            {{-- FULL NAME --}}

            {{-- PHONE --}}

            {{-- ADDRESS LINE 1 --}}
            <div class="mb-3">
                <label class="form-label"><strong>Address Line 1</strong></label>
                <input type="text" name="address_line1" value="{{ old('address_line1') }}"
                    class="form-control @error('address_line1') is-invalid @enderror" placeholder="House no, Street, Area">
                @error('address_line1')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- ADDRESS LINE 2 --}}
            <div class="mb-3">
                <label class="form-label"><strong>Address Line 2 (Optional)</strong></label>
                <input type="text" name="address_line2" value="{{ old('address_line2') }}" class="form-control"
                    placeholder="Landmark, Apartment, etc">
            </div>

            {{-- CITY --}}
            <div class="mb-3">
                <label class="form-label"><strong>City</strong></label>
                <input type="text" name="city" value="{{ old('city') }}"
                    class="form-control @error('city') is-invalid @enderror">
                @error('city')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- STATE --}}
            <div class="mb-3">
                <label class="form-label"><strong>State</strong></label>
                <input type="text" name="state" value="{{ old('state') }}"
                    class="form-control @error('state') is-invalid @enderror">
                @error('state')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- PINCODE --}}
            <div class="mb-3">
                <label class="form-label"><strong>Pincode</strong></label>
                <input type="text" name="pincode" value="{{ old('pincode') }}"
                    class="form-control @error('pincode') is-invalid @enderror">
                @error('pincode')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- ADDRESS TYPE --}}
            <div class="mb-3">
                <label class="form-label"><strong>Address Type</strong></label><br>

                <label class="me-3">
                    <input type="radio" name="type" value="home" checked> Home
                </label>

                <label class="me-3">
                    <input type="radio" name="type" value="office"> Office
                </label>
            </div>

            {{-- DEFAULT ADDRESS --}}
            {{-- <div class="mb-3">
                <label>
                    <input type="checkbox" name="is_default" value="1">
                    Set as default delivery address
                </label>
            </div> --}}

            <button type="submit" class="btn btn-dark w-100">
                Save Address
            </button>

        </form>

    </div>
    </div>
    <script>
        document.getElementById('addressSelect')?.addEventListener('change', function() {

            const selected = this.options[this.selectedIndex];

            if (!selected.value) {
                document.getElementById('selectedAddressBox').style.display = 'none';
                return;
            }

            document.getElementById('selectedAddressBox').style.display = 'block';

            document.getElementById('addr_line').innerText =
                selected.dataset.address;

            document.getElementById('addr_city').innerText =
                selected.dataset.city;

            document.getElementById('addr_state').innerText =
                selected.dataset.state;

            document.getElementById('addr_pin').innerText =
                selected.dataset.pincode;

            document.getElementById('addr_type').innerText =
                selected.dataset.type.toUpperCase();
        });
    </script>

@endsection

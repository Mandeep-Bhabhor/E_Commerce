@extends('products.customer_layout')

@section('content')

    <div class="container mt-4" x-data="cartPage({{ $total }})">

        <h3 class="mb-4 d-flex align-items-center"><i class="fa fa-shopping-cart me-2"></i> My Cart <a
                class="btn btn-danger ms-auto" href="{{ route('cart.destroy') }}">Clear Cart</a></h3>



        @if (count($cartItems) == 0)
            <div class="text-center mt-5">
                <h5>Your cart is empty</h5>
                <p class="text-muted">You clearly resisted impulse buying. Impressive.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th width="120">Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($cartItems as $item)
                            @php
                                $product = $item->product;
                                $images = json_decode($product->image, true) ?? [];
                                $firstImg = $images[0] ?? null;
                            @endphp

                            <tr x-data="qtyUpdater({{ $item->id }}, {{ $item->qty }}, {{ $item->price }})">

                                {{-- IMAGE --}}
                                <td>
                                    @if ($firstImg)
                                        <img src="{{ asset('storage/products/' . $firstImg) }}" width="70"
                                            height="70" style="object-fit:cover;">
                                    @endif
                                </td>

                                {{-- NAME --}}
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                </td>

                                {{-- COLOR --}}
                                <td>{{ $item->color->name ?? '-' }}</td>

                                {{-- SIZE --}}
                                <td>{{ $item->size->name ?? '-' }}</td>

                                {{-- QTY --}}
                                <td>
                                    <input type="number" x-model="qty" min="1" class="form-control form-control-sm"
                                        @change="updateQty">
                                </td>

                                {{-- PRICE --}}
                                <td>₹ {{ $item->price }}</td>

                                {{-- TOTAL --}}
                                <td>
                                    ₹ <span x-text="total"></span>
                                </td>

                                {{-- REMOVE --}}
                                <td>
                                    <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-danger btn-sm w-100">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>


                </table>
            </div>

            {{-- GRAND TOTAL --}}
            <div class="text-end mt-4">
                <h4>Total: ₹ <span x-text="grandTotal"></span></h4>

                <a href="{{ route('address.create') }}" class="btn btn-success mt-2">
                    Proceed to Checkout
                </a>
            </div>
        @endif

    </div>
    <script>
        function qtyUpdater(cartId, initialQty, price, ) {
            return {
                qty: initialQty,
                price: price,
                total: initialQty * price,
                updateQty() {

                    if (this.qty < 1) {
                        this.qty = 1
                    }

                    fetch(`{{ route('cart.update', ':id') }}`.replace(':id', cartId), {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                qty: this.qty
                            })
                        })
                        .then(res => res.json())
                        .then(data => {

                            // update UI instantly
                            this.total = data.total
                            this.qty = data.qty


                            // refresh grand total listener
                            window.dispatchEvent(new CustomEvent('cart-updated', {
                                detail: {
                                    total: data.grand_total
                                }
                            }))

                        })
                        .catch(err => {
                            console.error('Qty update failed', err)
                        })
                }
            }
        }

        function cartPage(initialTotal) {
            return {
                ///this is alpine name : parameter
                grandTotal: initialTotal,

                init() {
                    window.addEventListener('cart-updated', (e) => {
                        this.grandTotal = e.detail.total
                    })
                }
            }
        }
    </script>



@endsection

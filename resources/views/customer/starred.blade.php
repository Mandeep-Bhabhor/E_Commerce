@extends('layouts.customer')

@section('content')
    <div class="container py-5">
        <div class="mx-auto" style="max-width: 700px;">

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold mb-0">★ Starred Messages</h3>
                        <a href="{{ route('customer.profile') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                            ← Back
                        </a>
                    </div>

                    <div id="starred-list">
                        <div class="text-center text-muted py-5">
                            <div class="spinner-border spinner-border-sm me-2"></div>
                            Loading starred messages...
                        </div>
                    </div>

                    <div id="starred-empty" class="text-center text-muted py-5 d-none">
                        <div style="font-size:48px;opacity:0.3;">☆</div>
                        <p class="mt-2">No starred messages yet.<br>Star messages in your chat to see them here.</p>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script type="module">
        import { db, collection, getDocs, deleteDoc, doc } from "/js/firebase.js";

        const userId = "{{ auth()->id() }}";
        const listEl = document.getElementById('starred-list');
        const emptyEl = document.getElementById('starred-empty');

        async function loadStarred() {
            try {
                const snap = await getDocs(
                    collection(db, 'customer_favourites', userId, 'messages')
                );

                if (snap.empty) {
                    listEl.classList.add('d-none');
                    emptyEl.classList.remove('d-none');
                    return;
                }

                listEl.innerHTML = '';

                snap.forEach(docSnap => {
                    const d = docSnap.data();
                    const msgId = docSnap.id;
                    const senderLabel = d.sender === 'customer' ? 'You' : 'Support';

                    const card = document.createElement('div');
                    card.className = 'p-3 mb-3 rounded-3 border bg-white d-flex justify-content-between align-items-start';
                    card.innerHTML = `
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <small class="fw-semibold text-muted">${senderLabel}</small>
                                <small class="text-muted">${d.chat_id || ''}</small>
                            </div>
                            <div class="mt-1" style="word-break:break-word;">
                                ${d.message || '<i class="text-muted">Media attachment</i>'}
                            </div>
                            ${d.media_url ? '<small class="text-primary">📎 Has attachment</small>' : ''}
                        </div>
                        <button class="btn btn-sm btn-outline-warning ms-3 unstar-btn" data-id="${msgId}" title="Unstar">
                            ★
                        </button>
                    `;

                    card.querySelector('.unstar-btn').addEventListener('click', async (e) => {
                        const id = e.currentTarget.dataset.id;
                        await deleteDoc(doc(db, 'customer_favourites', userId, 'messages', id));
                        card.remove();

                        // Check if list is now empty
                        if (listEl.children.length === 0) {
                            listEl.classList.add('d-none');
                            emptyEl.classList.remove('d-none');
                        }
                    });

                    listEl.appendChild(card);
                });

            } catch (e) {
                console.error('Load starred failed:', e);
                listEl.innerHTML = '<p class="text-danger text-center">Failed to load starred messages</p>';
            }
        }

        loadStarred();
    </script>
@endsection

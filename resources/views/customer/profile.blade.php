@extends('layouts.customer')

@section('content')
    <div class="container py-5">
        <div class="mx-auto" style="max-width: 900px;">

            <!-- Profile Info -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-5">
                    <h3 class="fw-bold mb-2">Profile Information</h3>
                    <p class="text-muted mb-4">
                        Update your account details and email.
                    </p>

                    @if (session('status') === 'profile-updated')
                        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                            Profile updated successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Profile Picture</label>

                            <div class="d-flex align-items-center gap-4 mt-3">
                                <!-- Current Image -->
                                <div>
                                    @if (auth()->user()->pfp)
                                        <img src="{{ asset('storage/' . auth()->user()->pfp) }}" alt="Profile Picture"
                                            class="rounded-circle border shadow-sm"
                                            style="width: 100px; height: 100px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center shadow-sm"
                                            style="width: 100px; height: 100px; font-size: 2rem;">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Upload -->
                                <div class="flex-grow-1">
                                    <input type="file" name="pfp" accept="image/*"
                                        class="form-control form-control-lg rounded-3">
                                    <small class="text-muted">
                                        JPG, PNG, WEBP. Max 2MB.
                                    </small>

                                    @error('pfp')
                                        <small class="text-danger d-block mt-2">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                                class="form-control form-control-lg rounded-3" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                                class="form-control form-control-lg rounded-3" required>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button class="btn btn-primary px-4 py-2 rounded-pill">
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- About Section — saved to Firestore users_profile/{userId} -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-5">
                    <h3 class="fw-bold mb-2">About</h3>
                    <p class="text-muted mb-4">
                        Write a short bio about yourself. This is visible to support staff.
                    </p>

                    <div class="mb-4">
                        <textarea
                            id="about-input"
                            class="form-control form-control-lg rounded-3"
                            rows="4"
                            maxlength="500"
                            placeholder="Tell us a little about yourself…"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small id="about-status" class="text-muted"></small>
                            <small id="about-chars" class="text-muted">0 / 500</small>
                        </div>
                    </div>

                    <button id="about-save" class="btn btn-primary px-4 py-2 rounded-pill">
                        Save About
                    </button>
                </div>
            </div>

            <!-- Password -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-5">
                    <h3 class="fw-bold mb-2">Update Password</h3>
                    <p class="text-muted mb-4">
                        Keep your account secure.
                    </p>

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Current Password</label>
                            <input type="password" name="current_password" class="form-control form-control-lg rounded-3">
                            @error('current_password', 'updatePassword')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">New Password</label>
                            <input type="password" name="password" class="form-control form-control-lg rounded-3">
                            @error('password', 'updatePassword')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirm Password</label>
                            <input type="password" name="password_confirmation"
                                class="form-control form-control-lg rounded-3">
                        </div>

                        <button class="btn btn-primary px-4 py-2 rounded-pill">
                            Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Delete Account -->
            <div class="card border-0 shadow-sm rounded-4 border-danger">
                <div class="card-body p-5">
                    <h3 class="fw-bold text-danger mb-2">Delete Account</h3>
                    <p class="text-muted mb-4">
                        Permanently delete your account and all associated data.
                    </p>

                    <button class="btn btn-danger px-4 py-2 rounded-pill" data-bs-toggle="modal"
                        data-bs-target="#deleteModal">
                        Delete Account
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')

                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold text-danger">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted">
                            Enter your password to permanently delete your account.
                        </p>

                        <input type="password" name="password" class="form-control rounded-3" placeholder="Enter password">

                        @error('password', 'userDeletion')
                            <small class="text-danger mt-2 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button class="btn btn-danger rounded-pill px-4">
                            Delete Permanently
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- About section — Firestore read/write via Firebase JS SDK --}}
    <script type="module">
        import { db, doc, getDoc, setDoc } from "/js/firebase.js";

        const userId    = "{{ auth()->id() }}";
        const aboutInput  = document.getElementById('about-input');
        const aboutSave   = document.getElementById('about-save');
        const aboutStatus = document.getElementById('about-status');
        const aboutChars  = document.getElementById('about-chars');

        /*
        |----------------------------------------------------------------------
        | CHARACTER COUNTER
        |----------------------------------------------------------------------
        */
        aboutInput.addEventListener('input', () => {
            aboutChars.textContent = aboutInput.value.length + ' / 500';
        });

        /*
        |----------------------------------------------------------------------
        | LOAD EXISTING ABOUT FROM FIRESTORE
        |----------------------------------------------------------------------
        */
        try {
            const snap = await getDoc(doc(db, 'users_profile', userId));
            if (snap.exists() && snap.data().about) {
                aboutInput.value = snap.data().about;
                aboutChars.textContent = snap.data().about.length + ' / 500';
            }
        } catch (e) {
            console.error('Could not load about:', e);
        }

        /*
        |----------------------------------------------------------------------
        | SAVE ABOUT TO FIRESTORE
        |----------------------------------------------------------------------
        */
        aboutSave.addEventListener('click', async () => {

            const text = aboutInput.value.trim();

            aboutSave.disabled   = true;
            aboutSave.textContent = 'Saving…';
            aboutStatus.textContent = '';

            try {
                await setDoc(
                    doc(db, 'users_profile', userId),
                    { about: text },
                    { merge: true }
                );

                aboutStatus.textContent = '✓ Saved';
                aboutStatus.className   = 'text-success small';

            } catch (e) {
                console.error('Save about failed:', e);
                aboutStatus.textContent = 'Failed to save. Try again.';
                aboutStatus.className   = 'text-danger small';
            } finally {
                aboutSave.disabled    = false;
                aboutSave.textContent = 'Save About';
            }
        });
    </script>
@endsection

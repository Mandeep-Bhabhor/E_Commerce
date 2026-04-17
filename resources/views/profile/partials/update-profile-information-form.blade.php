<section class="bg-slate-800/90 backdrop-blur-md border border-slate-700 rounded-3xl shadow-2xl p-8">
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-2 text-sm text-slate-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <div>
            <x-input-label for="name" :value="__('Name')" class="text-slate-200 font-medium" />
            <x-text-input id="name" name="name" type="text"
                class="mt-2 block w-full rounded-xl bg-slate-950 border-slate-600 text-white"
                :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2 text-red-400" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-slate-200 font-medium" />
            <x-text-input id="email" name="email" type="email"
                class="mt-2 block w-full rounded-xl bg-slate-950 border-slate-600 text-white"
                :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2 text-red-400" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700">
                {{ __('Save Changes') }}
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-emerald-400">
                    {{ __('Saved successfully.') }}
                </p>
            @endif
        </div>
    </form>
</section>
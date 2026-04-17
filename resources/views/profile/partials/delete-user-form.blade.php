<section class="bg-slate-800/90 backdrop-blur-md border border-red-900/40 rounded-3xl shadow-2xl p-8">
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-red-400">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-2 text-sm text-slate-400 leading-relaxed">
            {{ __('Once your account is deleted, all data will be permanently removed.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-6 py-3 rounded-xl bg-red-600 hover:bg-red-700">
        {{ __('Delete Account') }}
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="POST" action="{{ route('profile.destroy') }}" class="p-8 bg-slate-900 rounded-2xl">
            @csrf
            @method('DELETE')

            <h2 class="text-xl font-bold text-white">
                {{ __('Confirm Account Deletion') }}
            </h2>

            <p class="mt-3 text-sm text-slate-400 leading-relaxed">
                {{ __('Enter your password to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="text-slate-200" />
                <x-text-input id="password" name="password" type="password"
                    class="mt-2 block w-full rounded-xl bg-slate-950 border-slate-600 text-white"
                    placeholder="{{ __('Password') }}" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-red-400" />
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')" class="rounded-xl">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="rounded-xl bg-red-600 hover:bg-red-700">
                    {{ __('Delete Permanently') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
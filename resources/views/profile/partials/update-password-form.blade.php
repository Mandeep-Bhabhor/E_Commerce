<section class="bg-slate-800/90 backdrop-blur-md border border-slate-700 rounded-3xl shadow-2xl p-8">
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-white">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-2 text-sm text-slate-400">
            {{ __('Ensure your account is using a strong password.') }}
        </p>
    </header>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" class="text-slate-200" />
            <x-text-input id="update_password_current_password" name="current_password" type="password"
                class="mt-2 block w-full rounded-xl bg-slate-950 border-slate-600 text-white"
                autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-red-400" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" class="text-slate-200" />
            <x-text-input id="update_password_password" name="password" type="password"
                class="mt-2 block w-full rounded-xl bg-slate-950 border-slate-600 text-white"
                autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-red-400" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="text-slate-200" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password"
                class="mt-2 block w-full rounded-xl bg-slate-950 border-slate-600 text-white"
                autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-red-400" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700">
                {{ __('Update Password') }}
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-emerald-400">
                    {{ __('Password updated.') }}
                </p>
            @endif
        </div>
    </form>
</section>
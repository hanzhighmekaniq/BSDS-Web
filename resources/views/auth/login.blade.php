<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="font-heading font-semibold text-lg text-bsds-navy">Welcome back</h2>
        <p class="text-sm text-bsds-gray mt-1">Masuk untuk melanjutkan simulasi Anda.</p>
    </div>

    @if (app()->environment('local'))
        <div class="mb-6 p-3 rounded-xl border border-dashed border-bsds-border bg-bsds-bg">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-bsds-gray mb-2">Quick Login (dev only)</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach ([
                    ['label' => 'Admin', 'email' => 'admin@gmail.com'],
                    ['label' => 'Peneliti', 'email' => 'researcher@bsds.test'],
                    ['label' => 'Siswa', 'email' => 'student@bsds.test'],
                ] as $demo)
                    <button type="button"
                        onclick="document.getElementById('email').value='{{ $demo['email'] }}'; document.getElementById('password').value='password';"
                        class="px-2 py-1.5 rounded-lg border border-bsds-border bg-white text-xs font-semibold text-bsds-navy hover:border-bsds-teal hover:bg-bsds-teal/10 transition">
                        {{ $demo['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-password-input id="password" name="password" required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-bsds-border text-bsds-teal shadow-sm focus:ring-bsds-teal" name="remember">
                <span class="ms-2 text-sm text-bsds-gray">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-bsds-gray hover:text-bsds-navy rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsds-teal" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

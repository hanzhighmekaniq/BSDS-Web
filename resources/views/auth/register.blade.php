<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="font-heading font-semibold text-lg text-bsds-navy">Let's start your simulation</h2>
        <p class="text-sm text-bsds-gray mt-1">Buat akun untuk mulai berlatih mengambil keputusan.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Kelas -->
        <div class="mt-4">
            <x-input-label for="classroom_id" value="Kelas" />
            <select id="classroom_id" name="classroom_id" required class="block mt-1 w-full rounded-xl border-bsds-border focus:border-bsds-teal focus:ring-bsds-teal shadow-sm text-sm">
                <option value="">Pilih kelas</option>
                @foreach ($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected(old('classroom_id') == $classroom->id)>{{ $classroom->name }} ({{ $classroom->academic_year }})</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('classroom_id')" class="mt-2" />
        </div>

        <!-- No. Absen -->
        <div class="mt-4">
            <x-input-label for="absence_no" value="No. Absen" />
            <x-text-input id="absence_no" class="block mt-1 w-full" type="number" min="1" max="200" name="absence_no" :value="old('absence_no')" required />
            <x-input-error :messages="$errors->get('absence_no')" class="mt-2" />
        </div>

        <!-- NIS / Kode Peserta -->
        <div class="mt-4">
            <x-input-label for="nis" value="NIS / Kode Peserta" />
            <x-text-input id="nis" class="block mt-1 w-full" type="text" name="nis" :value="old('nis')" required />
            <x-input-error :messages="$errors->get('nis')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-bsds-gray hover:text-bsds-navy rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsds-teal" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

<section id="kelas">
    <header>
        <h2 class="font-heading text-lg font-semibold text-bsds-navy">Kelas</h2>
        <p class="mt-1 text-sm text-bsds-gray">Pilih kelas kamu agar hasil belajar tercatat di kelas yang benar.</p>
    </header>

    <form method="post" action="{{ route('profile.classroom') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="classroom_id" value="Kelas" />
            <select id="classroom_id" name="classroom_id" required
                class="block w-full mt-1 text-sm shadow-sm rounded-xl border-bsds-border focus:border-bsds-teal focus:ring-bsds-teal">
                <option value="">Pilih kelas</option>
                @foreach ($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected(old('classroom_id', $user->studentProfile?->classroom_id) == $classroom->id)>
                        {{ $classroom->name }} ({{ $classroom->academic_year }})
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('classroom_id')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            @if (session('status') === 'classroom-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-bsds-success">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

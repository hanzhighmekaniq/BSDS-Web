<x-app-layout>
    <x-slot name="header">
        <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="w-full space-y-6">
            <div class="p-4 sm:p-8 bg-white border border-bsds-border rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            @role('student')
                <div class="p-4 sm:p-8 bg-white border border-bsds-border rounded-2xl">
                    <div class="max-w-xl">
                        @include('profile.partials.update-classroom-form')
                    </div>
                </div>
            @endrole

            <div class="p-4 sm:p-8 bg-white border border-bsds-border rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white border border-bsds-border rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
</x-app-layout>

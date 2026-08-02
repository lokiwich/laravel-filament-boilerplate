<div class="mt-4">
    <x-filament::button
        tag="a"
        color="gray"
        outlined
        class="w-full"
        :href="route('auth.keycloak.redirect')"
    >
        {{ __('keycloak.login_button') }}
    </x-filament::button>
</div>

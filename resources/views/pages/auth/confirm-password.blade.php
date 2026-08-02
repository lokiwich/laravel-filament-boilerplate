<x-layouts::auth :title="__('auth.confirm_password.title')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('auth.confirm_password.title')"
            :description="__('auth.confirm_password.description')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify
            options-route="passkey.confirm-options"
            submit-route="passkey.confirm"
            :label="__('auth.confirm_password.with_passkey')"
            :loading-label="__('auth.confirm_password.confirming')"
            :separator="__('auth.confirm_password.or_with_password')"
        />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="__('common.password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('common.password')"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('auth.confirm_password.submit') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>

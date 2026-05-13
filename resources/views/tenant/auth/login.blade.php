<x-tenant-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Tenant Login</h1>
        <p class="mt-2 text-sm text-gray-600">Sign in with your tenant account credentials.</p>
    </div>

    <form method="POST" action="{{ route('tenant.login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="admin@tenant.local" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center">
                <input id="remember" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <x-primary-button class="w-full justify-center">
            {{ __('Sign in') }}
        </x-primary-button>
    </form>

    <div class="mt-6 text-center text-sm text-gray-600">
        <p>Don't have an account? <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-medium">Register here</a></p>
    </div>
</x-tenant-guest-layout>


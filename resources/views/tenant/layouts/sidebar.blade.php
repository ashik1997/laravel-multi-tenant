@php
    $tenant = Auth::guard('tenant')->user();
    $sidebarLinks = [
        [
            'label' => 'Dashboard',
            'route' => 'tenant.dashboard',
            'active' => request()->routeIs('tenant.dashboard'),
            'icon' => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-3a1 1 0 011-1h2a1 1 0 011 1v3h3a1 1 0 001-1v-10',
        ],
        [
            'label' => 'Billing',
            'route' => 'tenant.billing.index',
            'active' => request()->routeIs('tenant.billing*'),
            'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm4.5-9h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm4.5-9h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm4.5-9h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008z',
        ],
    ];
@endphp

<aside class="flex h-full w-64 flex-col border-r border-gray-200 bg-white">
    <!-- Logo Section -->
    <div class="flex h-16 items-center gap-3 border-b border-gray-100 px-6">
        <a href="{{ route('tenant.dashboard') }}" class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-md bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center">
                <span class="text-white font-bold text-sm">T</span>
            </div>
            <span class="text-base font-semibold text-gray-900">{{ config('app.name', 'Laravel') }}</span>
        </a>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 space-y-1 px-3 py-4">
        @foreach ($sidebarLinks as $link)
            <a href="{{ route($link['route']) }}" class="{{ $link['active'] ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors duration-200">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                </svg>
                <span>{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <!-- User Section -->
    <div class="border-t border-gray-100 p-4">
        <div x-data="{ profileOpen: false }" class="space-y-3">
            <!-- User Info -->
            <div class="rounded-lg bg-gray-50 p-3">
                <div class="truncate text-sm font-medium text-gray-900">{{ $tenant?->name ?? 'Tenant Admin' }}</div>
                <div class="truncate text-xs text-gray-500">{{ $tenant?->email ?? 'admin@tenant.local' }}</div>
            </div>

            <!-- Logout Button -->
            <form method="POST" action="{{ route('tenant.logout') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full rounded-md border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-700 hover:bg-red-50 hover:border-red-300 hover:text-red-700 transition-colors duration-200">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Log Out</span>
                    </div>
                </button>
            </form>
        </div>
    </div>
</aside>

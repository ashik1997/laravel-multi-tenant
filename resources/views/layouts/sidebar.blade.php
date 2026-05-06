@php
    $sidebarLinks = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active' => request()->routeIs('dashboard'),
            'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10',
        ],
        [
            'label' => 'Tenants',
            'route' => 'tenants.index',
            'active' => request()->routeIs('tenants.*'),
            'icon' => 'M4 6h16M4 12h16M4 18h16',
        ],
        [
            'label' => 'Profile',
            'route' => 'profile.edit',
            'active' => request()->routeIs('profile.edit'),
            'icon' => 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 20a8 8 0 0116 0',
        ],
    ];
@endphp

<aside class="flex h-full w-64 flex-col border-r border-gray-200 bg-white">
    <div class="flex h-16 items-center gap-3 border-b border-gray-100 px-6">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <x-application-logo class="h-9 w-auto fill-current text-gray-800" />
            <span class="text-base font-semibold text-gray-900">{{ config('app.name', 'Laravel') }}</span>
        </a>
    </div>

    <nav class="flex-1 space-y-1 px-3 py-4">
        @foreach ($sidebarLinks as $link)
            <a href="{{ route($link['route']) }}" class="{{ $link['active'] ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                </svg>
                <span>{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="border-t border-gray-100 p-4">
        <div class="mb-3">
            <div class="truncate text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
            <div class="truncate text-xs text-gray-500">{{ Auth::user()->email }}</div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full rounded-md border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-700 hover:bg-gray-50">
                Log Out
            </button>
        </form>
    </div>
</aside>

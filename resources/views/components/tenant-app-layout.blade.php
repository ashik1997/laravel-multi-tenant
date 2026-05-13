<div x-data="{ sidebarOpen: false }" class="min-h-screen bg-gray-50">
    <!-- Sidebar Overlay (Mobile) -->
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden" @click="sidebarOpen = false"></div>

    <!-- Sidebar (Desktop) -->
    <div class="fixed inset-y-0 left-0 z-40 hidden lg:block">
        @include('tenant.layouts.sidebar')
    </div>

    <!-- Sidebar (Mobile) -->
    <div x-show="sidebarOpen" x-cloak class="fixed inset-y-0 left-0 z-40 lg:hidden">
        @include('tenant.layouts.sidebar')
    </div>

    <!-- Main Content -->
    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 border-b border-gray-200 bg-white">
            <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                <button type="button" class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="min-w-0 flex-1 lg:flex-none">
                    <h1 class="text-lg font-semibold text-gray-900">{{ $header ?? 'Tenant Dashboard' }}</h1>
                </div>

                <div class="hidden text-right sm:block">
                    <div class="text-sm font-medium text-gray-900">{{ Auth::guard('tenant')->user()?->name ?? 'Tenant' }}</div>
                    <div class="text-xs text-gray-500">{{ Auth::guard('tenant')->user()?->email ?? 'N/A' }}</div>
                </div>
            </div>
        </header>

        <main class="px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>
</div>

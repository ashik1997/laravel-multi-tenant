<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Tenant Dashboard</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen">
            <nav class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <div class="text-lg font-semibold text-gray-900">Tenant Dashboard</div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="text-sm text-gray-600">
                                {{ Auth::guard('tenant')->user()->name }}
                            </div>

                            <form method="POST" action="{{ route('tenant.logout') }}">
                                @csrf

                                <x-secondary-button type="submit">
                                    {{ __('Log out') }}
                                </x-secondary-button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="text-xl font-semibold">{{ Auth::guard('tenant')->user()->name }}</div>
                            <div class="mt-2 text-sm text-gray-600">{{ Auth::guard('tenant')->user()->email }}</div>
                            <div class="mt-6 text-sm text-gray-700">
                                Tenant login is working.
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>

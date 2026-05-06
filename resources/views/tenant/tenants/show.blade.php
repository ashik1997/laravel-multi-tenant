<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tenant Details') }}
            </h2>
            <a href="{{ route('tenants.edit', $tenant) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                Edit Tenant
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Domain</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->domain }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Database</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->database }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Connection</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->driver }} / {{ $tenant->host }}:{{ $tenant->port }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Charset</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->charset }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Collation</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $tenant->collation }}</dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <a href="{{ route('tenants.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to tenants</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

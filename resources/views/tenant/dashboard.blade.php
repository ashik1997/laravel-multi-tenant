@extends('tenant.layouts.app', ['header' => 'Dashboard'])
@section('content')
    <div class="max-w-4xl">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold mb-2">Welcome, {{ Auth::guard('tenant')->user()?->name }}!</h2>
                <p class="text-gray-600 mb-6">You are logged in to your tenant account.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Account Info -->
                    <div class="rounded-lg border border-gray-200 p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Account Information</h3>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm text-gray-500">Name</span>
                                <p class="font-medium text-gray-900">{{ Auth::guard('tenant')->user()?->name }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Email</span>
                                <p class="font-medium text-gray-900">{{ Auth::guard('tenant')->user()?->email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="rounded-lg border border-gray-200 p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Quick Actions</h3>
                        <div class="space-y-2">
                            <a href="{{ route('tenant.billing.index') }}" class="block px-4 py-2 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 text-center transition-colors">
                                Manage Billing
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

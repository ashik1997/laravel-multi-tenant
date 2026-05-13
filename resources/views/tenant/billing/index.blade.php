<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Tenant Billing</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen">
            <nav class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16 items-center">
                        <div class="text-lg font-semibold text-gray-900">Tenant Billing</div>
                        <a href="{{ route('tenant.dashboard') }}" class="text-sm text-blue-600">Back to dashboard</a>
                    </div>
                </div>
            </nav>

            <main class="py-12">
                <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="text-xl font-semibold">Billing for {{ $tenant->name }}</div>
                            <div class="mt-4 text-sm text-gray-600">Email: {{ $tenant->email }}</div>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <div class="text-sm font-medium text-gray-700">Subscription status</div>
                                    <div class="mt-1 text-gray-800">{{ optional($tenant->subscription('default'))->stripe_status ?? ucfirst($tenant->payment_status ?? 'inactive') }}</div>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-700">Current payment method</div>
                                    <div class="mt-1 text-gray-800">{{ $tenant->payment_provider ? strtoupper($tenant->payment_provider) : 'Stripe' }}</div>
                                </div>
                            </div>

                            @if(session('status'))
                                <div class="mt-6 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
                            @endif

                            @if(session('manual_instructions'))
                                <div class="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-900">{{ session('manual_instructions') }}</div>
                            @endif

                            <form id="subscription-form" method="POST" action="{{ route('tenant.billing.subscribe') }}" class="mt-8 space-y-6">
                                @csrf

                                <div>
                                    <div class="text-sm font-medium text-gray-700">Plan</div>
                                    <div class="mt-2 grid gap-3 sm:grid-cols-2">
                                        @foreach($plans as $key => $plan)
                                            <label class="block rounded-lg border border-gray-200 p-4 cursor-pointer hover:border-blue-500 {{ $loop->first ? 'border-blue-500 bg-blue-50' : '' }}">
                                                <input type="radio" name="plan" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }} class="sr-only" />
                                                <div class="text-base font-semibold text-gray-900">{{ $plan['name'] }}</div>
                                                <div class="mt-1 text-sm text-gray-600">{{ $plan['description'] }}</div>
                                                <div class="mt-3 text-lg font-semibold text-gray-900">{{ $plan['amount'] }}</div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-700">Payment method</div>
                                    <div class="mt-2 grid gap-3 sm:grid-cols-2">
                                        @foreach($gateways as $key => $gateway)
                                            <label class="block rounded-lg border border-gray-200 p-4 cursor-pointer hover:border-blue-500 {{ $loop->first ? 'border-blue-500 bg-blue-50' : '' }}">
                                                <input type="radio" name="gateway" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }} class="sr-only gateway-option" />
                                                <div class="text-base font-semibold text-gray-900">{{ $gateway['name'] }}</div>
                                                <div class="mt-1 text-sm text-gray-600">{{ $gateway['description'] }}</div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div id="stripe-card-section" class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                                    <label class="block text-sm font-medium text-gray-700">Card details</label>
                                    <div id="card-element" class="mt-2 rounded-md border border-gray-300 bg-white p-3"></div>
                                    <p class="mt-2 text-xs text-gray-500">Enter your credit card details. Stripe will securely tokenize them.</p>
                                </div>

                                <div id="manual-instructions" class="hidden rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-900"></div>

                                <input id="payment_method" name="payment_method" type="hidden" />
                                <input id="gateway" name="gateway" type="hidden" value="stripe" />

                                <div id="card-errors" class="text-sm text-red-600"></div>

                                <button id="subscribe-button" type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700">Continue</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <script src="https://js.stripe.com/v3"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const stripe = Stripe('{{ config('services.stripe.key') }}');
                const elements = stripe.elements();
                const cardElement = elements.create('card', {
                    style: {
                        base: {
                            color: '#111827',
                            fontFamily: 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, sans-serif',
                            fontSmoothing: 'antialiased',
                            fontSize: '16px',
                            '::placeholder': { color: '#9ca3af' },
                        },
                        invalid: { color: '#ef4444' },
                    },
                });

                cardElement.mount('#card-element');

                const form = document.getElementById('subscription-form');
                const gatewayInput = document.getElementById('gateway');
                const paymentMethodInput = document.getElementById('payment_method');
                const cardErrors = document.getElementById('card-errors');
                const button = document.getElementById('subscribe-button');
                const stripeSection = document.getElementById('stripe-card-section');
                const manualInstructions = document.getElementById('manual-instructions');
                const gatewayOptions = document.querySelectorAll('.gateway-option');
                const gateways = @json($gateways);

                function updateGatewayUI() {
                    const selected = document.querySelector('.gateway-option:checked');
                    const selectedKey = selected ? selected.value : 'stripe';
                    gatewayInput.value = selectedKey;

                    if (selectedKey === 'stripe') {
                        stripeSection.classList.remove('hidden');
                        manualInstructions.classList.add('hidden');
                        manualInstructions.textContent = '';
                    } else {
                        stripeSection.classList.add('hidden');
                        const gateway = gateways[selectedKey] || {};
                        manualInstructions.textContent = gateway.instructions || 'Please complete the payment instructions for this method.';
                        manualInstructions.classList.remove('hidden');
                    }
                }

                gatewayOptions.forEach(function (option) {
                    option.addEventListener('change', updateGatewayUI);
                });

                updateGatewayUI();

                form.addEventListener('submit', async function (event) {
                    const selectedKey = gatewayInput.value;
                    if (selectedKey !== 'stripe') {
                        return;
                    }

                    event.preventDefault();
                    button.disabled = true;
                    cardErrors.textContent = '';

                    const { setupIntent, error } = await stripe.confirmCardSetup(
                        '{{ $intent->client_secret }}',
                        {
                            payment_method: {
                                card: cardElement,
                                billing_details: {
                                    name: '{{ $tenant->name }}',
                                    email: '{{ $tenant->email }}',
                                },
                            },
                        }
                    );

                    if (error) {
                        cardErrors.textContent = error.message;
                        button.disabled = false;
                        return;
                    }

                    paymentMethodInput.value = setupIntent.payment_method;
                    form.submit();
                });
            });
        </script>
    </body>
</html>

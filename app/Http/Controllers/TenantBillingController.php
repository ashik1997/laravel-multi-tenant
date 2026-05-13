<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TenantBillingController extends Controller
{
    public function index(): View
    {
        $tenant = Auth::guard('tenant')->user();
        $plans = config('billing.plans');
        $gateways = config('billing.gateways');

        return view('tenant.billing.index', [
            'tenant' => $tenant,
            'intent' => $tenant->createSetupIntent(),
            'plans' => $plans,
            'gateways' => $gateways,
        ]);
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan' => ['required', 'string'],
            'gateway' => ['required', 'string'],
            'payment_method' => ['nullable', 'string'],
        ]);

        $plan = config("billing.plans.{$validated['plan']}");
        $gateway = $validated['gateway'];
        $gatewayConfig = config("billing.gateways.{$gateway}");

        if (! $plan || ! $gatewayConfig) {
            abort(422, 'The selected billing plan or payment method is invalid.');
        }

        $tenant = Auth::guard('tenant')->user();

        if ($gateway === 'stripe') {
            if (empty($validated['payment_method'])) {
                abort(422, 'A Stripe payment method is required.');
            }

            $tenant->newSubscription('default', $plan['price_id'])
                ->create($validated['payment_method']);

            $tenant->payment_provider = 'stripe';
            $tenant->payment_status = 'active';
            $tenant->save();

            return back()->with('status', 'Stripe subscription created successfully.');
        }

        $tenant->payment_provider = $gateway;
        $tenant->payment_status = 'pending';
        $tenant->save();

        return back()
            ->with('status', 'Manual payment selected. Please complete payment as instructed below.')
            ->with('manual_instructions', $gatewayConfig['instructions'] ?? null);
    }
}

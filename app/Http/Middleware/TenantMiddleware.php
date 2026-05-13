<?php
namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;

class TenantMiddleware
{
    public function handle($request, Closure $next)
    {
        $tenant = Tenant::whereHas('domains', function ($query) use ($request) {
            $query->where('domain', $request->getHost());
        })->first();

        if (! $tenant && app()->environment('local')) {
            $tenantIdentifier = $request->query('tenant');

            if ($tenantIdentifier) {
                $tenant = Tenant::where('id', $tenantIdentifier)
                    ->orWhere('name', $tenantIdentifier)
                    ->orWhereHas('domains', function ($query) use ($tenantIdentifier) {
                        $query->where('domain', $tenantIdentifier);
                    })
                    ->first();
            }

            if (! $tenant && Tenant::count() === 1) {
                $tenant = Tenant::first();
            }
        }

        if (! $tenant) {
            abort(404, 'Tenant not found');
        }

        app()->instance('tenant', $tenant);

        return $next($request);
    }
}

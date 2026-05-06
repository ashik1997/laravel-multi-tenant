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

        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        app()->instance('tenant', $tenant);

        return $next($request);
    }
}

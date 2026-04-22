<?php
use Closure;
use App\Models\Tenant;

class TenantMiddleware
{
    public function handle($request, Closure $next)
    {
        $tenant = Tenant::where('domain', $request->getHost())->first();

        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        app()->instance('tenant', $tenant);

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Domain;
use Illuminate\Support\Facades\Session;

class ResolveDomainMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // Try to find domain by hostname
        $domain = Domain::where('domain', $host)->first();

        if ($domain) {
            // Set domain in request
            $request->attributes->set('domain', $domain);

            // Set tenant context
            if ($domain->tenant) {
                Session::put([
                    'tenant_id' => $domain->tenant->id,
                    'domain_id' => $domain->id,
                    'domain' => $domain->domain,
                ]);
            }
        }

        return $next($request);
    }
}

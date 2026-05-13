<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TenantSwitchController extends Controller
{
    /**
     * Get available tenants for current user
     */
    public function getAvailableTenants()
    {
        $user = Auth::user();

        // Get tenants where user has access
        $tenants = Tenant::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get(['id', 'name', 'domain']);

        return response()->json([
            'success' => true,
            'current_tenant' => session('tenant_id'),
            'tenants' => $tenants,
        ]);
    }

    /**
     * Switch to a different tenant
     */
    public function switchTenant(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        $user = Auth::user();
        $tenant = Tenant::findOrFail($validated['tenant_id']);

        // Check if user has access to this tenant
        $hasAccess = $tenant->users()->where('user_id', $user->id)->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this tenant',
            ], 403);
        }

        // Store tenant info in session
        Session::put([
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
        ]);

        // Set tenant context for Stancl Tenancy
        tenancy()->initialize($tenant);

        return response()->json([
            'success' => true,
            'message' => 'Switched to tenant: ' . $tenant->name,
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
        ]);
    }

    /**
     * Get current tenant context
     */
    public function getCurrentTenant()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context',
            ], 400);
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'tenant' => $tenant,
            'domains' => $tenant->domains ?? [],
        ]);
    }

    /**
     * Initialize tenant session on login
     */
    public function initializeTenant($tenantId)
    {
        $user = Auth::user();
        $tenant = Tenant::findOrFail($tenantId);

        // Check if user has access
        $hasAccess = $tenant->users()->where('user_id', $user->id)->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        Session::put([
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant initialized',
        ]);
    }
}

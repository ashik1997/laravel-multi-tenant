<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DomainController extends Controller
{
    /**
     * Display all domains for current tenant
     */
    public function index()
    {
        $tenant = Auth::user()->tenant ?? Tenant::find(session('tenant_id'));

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $domains = Domain::where('tenant_id', $tenant->id)->get();

        return response()->json([
            'success' => true,
            'domains' => $domains,
            'count' => $domains->count(),
        ]);
    }

    /**
     * Store a new domain with FTP configuration
     */
    public function store(Request $request)
    {
        $tenant = Auth::user()->tenant ?? Tenant::find(session('tenant_id'));

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $validated = $request->validate([
            'domain' => 'required|string|unique:domains|regex:/^([a-z0-9]{1}([a-z0-9-]{0,61}[a-z0-9]{1})?\.){1,}([a-z]{2,})$/',
            'ftp_host' => 'nullable|string',
            'ftp_username' => 'nullable|string',
            'ftp_password' => 'nullable|string|min:6',
            'ftp_port' => 'nullable|integer|between:1,65535',
            'upload_path' => 'nullable|string',
            'max_upload_size' => 'nullable|integer|min:1048576', // minimum 1MB
        ]);

        $validated['tenant_id'] = $tenant->id;

        $domain = Domain::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Domain created successfully',
            'domain' => $domain,
        ], 201);
    }

    /**
     * Update domain configuration
     */
    public function update(Request $request, $domainId)
    {
        $domain = Domain::findOrFail($domainId);

        $this->authorize('update', $domain);

        $validated = $request->validate([
            'ftp_host' => 'nullable|string',
            'ftp_username' => 'nullable|string',
            'ftp_password' => 'nullable|string|min:6',
            'ftp_port' => 'nullable|integer|between:1,65535',
            'upload_path' => 'nullable|string',
            'max_upload_size' => 'nullable|integer|min:1048576',
        ]);

        $domain->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Domain updated successfully',
            'domain' => $domain,
        ]);
    }

    /**
     * Delete a domain
     */
    public function destroy($domainId)
    {
        $domain = Domain::findOrFail($domainId);

        $this->authorize('delete', $domain);

        $domain->delete();

        return response()->json([
            'success' => true,
            'message' => 'Domain deleted successfully',
        ]);
    }

    /**
     * Get domain configuration
     */
    public function show($domainId)
    {
        $domain = Domain::findOrFail($domainId);

        $this->authorize('view', $domain);

        return response()->json([
            'success' => true,
            'domain' => $domain,
            'ftp_configured' => $domain->hasFtpConfig(),
        ]);
    }

    /**
     * Test FTP connection
     */
    public function testFtp($domainId)
    {
        $domain = Domain::findOrFail($domainId);

        $this->authorize('update', $domain);

        if (!$domain->hasFtpConfig()) {
            return response()->json([
                'success' => false,
                'message' => 'FTP configuration incomplete',
            ], 400);
        }

        try {
            $ftp_config = $domain->getFtpConfig();
            $conn_id = ftp_connect($ftp_config['host'], $ftp_config['port']);

            if (!$conn_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to FTP server',
                ], 400);
            }

            $login_result = ftp_login($conn_id, $ftp_config['username'], $ftp_config['password']);

            if (!$login_result) {
                ftp_close($conn_id);
                return response()->json([
                    'success' => false,
                    'message' => 'FTP login failed - invalid credentials',
                ], 400);
            }

            ftp_close($conn_id);

            return response()->json([
                'success' => true,
                'message' => 'FTP connection successful',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'FTP connection error: ' . $e->getMessage(),
            ], 400);
        }
    }
}

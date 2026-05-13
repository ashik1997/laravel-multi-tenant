<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        return view('tenant.tenants.index', [
            'tenants' => Tenant::query()->orderByDesc('created_at')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('tenant.tenants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedTenantData($request);
        $data['id'] = $this->tenantIdFromName($data['name']);
        $data['domain'] = $data['domain'] ?: $data['id'] . '.localhost';
        $data['database'] = $data['database'] ?: 'tenant_' . $data['id'];
        $plainPassword = $data['password'];
        $data['password'] = Hash::make($plainPassword);

        if (Tenant::whereKey($data['id'])->exists()) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'A tenant with this name already exists.']);
        }

        $tenant = Tenant::create($data);

        $tenant->domains()->firstOrCreate([
            'domain' => $tenant->domain,
        ]);

        $this->provisionTenantDatabase($tenant, $plainPassword);

        return redirect()
            ->route('tenants.index')
            ->with('status', 'Tenant created successfully.');
    }

    public function show(Tenant $tenant): View
    {
        return view('tenant.tenants.show', [
            'tenant' => $tenant,
        ]);
    }

    public function edit(Tenant $tenant): View
    {
        return view('tenant.tenants.edit', [
            'tenant' => $tenant,
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $this->validatedTenantData($request, $tenant);
        $data['domain'] = $data['domain'] ?: $tenant->domain;
        $data['database'] = $data['database'] ?: $tenant->database;

        $plainPassword = null;

        if (! empty($data['password'])) {
            $plainPassword = $data['password'];
            $data['password'] = Hash::make($plainPassword);
        } else {
            unset($data['password']);
        }

        $tenant->update($data);

        $tenant->domains()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            ['domain' => $tenant->domain],
        );

        $this->provisionTenantDatabase($tenant, $plainPassword);

        return redirect()
            ->route('tenants.index')
            ->with('status', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        /** @var \Illuminate\Database\Eloquent\Model $tenant */
        $tenant->delete();

        return redirect()
            ->route('tenants.index')
            ->with('status', 'Tenant deleted successfully.');
    }

    private function provisionTenantDatabase(Tenant $tenant, ?string $plainPassword = null): void
    {
        $tenant->setInternal('db_name', $tenant->database);
        $tenant->save();

        $database = $tenant->database()->getName();
        $manager = $tenant->database()->manager();

        if (! $manager->databaseExists($database)) {
            $manager->createDatabase($tenant);
        }

        Artisan::call('tenants:migrate', [
            '--tenants' => [$tenant->id],
        ]);

        if ($plainPassword !== null) {
            $this->createTenantAdminUser($tenant, $plainPassword);
        }
    }

    private function createTenantAdminUser(Tenant $tenant, string $plainPassword): void
    {
        tenancy()->initialize($tenant);

        try {
            DB::table('users')->updateOrInsert(
                ['email' => $tenant->email],
                [
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'password' => Hash::make($plainPassword),
                    'remember_token' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        } finally {
            tenancy()->end();
        }
    }

    private function validatedTenantData(Request $request, ?Tenant $tenant = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('tenants', 'email')->ignore($tenant?->id, 'id'),
            ],
            'password' => [$tenant ? 'nullable' : 'required', 'string', 'min:6'],
            'domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tenants', 'domain')->ignore($tenant?->id, 'id'),
            ],
            'database' => ['nullable', 'string', 'max:255'],
            'driver' => ['required', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'string', 'max:255'],
            'dbusername' => ['required', 'string', 'max:255'],
            'dbpassword' => ['nullable', 'string', 'max:255'],
            'charset' => ['required', 'string', 'max:255'],
            'collation' => ['required', 'string', 'max:255'],
        ]);
    }

    private function tenantIdFromName(string $name): string
    {
        $id = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $name));

        return $id ?: 'tenant';
    }
}

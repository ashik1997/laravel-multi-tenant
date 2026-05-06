<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class TenantUserSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 'main';

        $tenant = Tenant::updateOrCreate(
            ['id' => $tenantId],
            [
                'name' => 'Main',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('12345678'),
                'domain' => $tenantId . '.localhost',
                'database' => 'tenant_' . $tenantId,
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'dbusername' => 'root',
                'dbpassword' => '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
        );

        $tenant->domains()->firstOrCreate([
            'domain' => $tenantId . '.localhost',
        ]);

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
    }
}

<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements AuthenticatableContract, TenantWithDatabase
{
    use Authenticatable, HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'domain',
        'database',
        'driver',
        'host',
        'port',
        'dbusername',
        'dbpassword',
        'charset',
        'collation',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'password',
            'domain',
            'database',
            'driver',
            'host',
            'port',
            'dbusername',
            'dbpassword',
            'charset',
            'collation',
        ];
    }
}

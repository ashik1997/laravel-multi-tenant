<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Cashier\Billable;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements AuthenticatableContract, TenantWithDatabase
{
    use Authenticatable, Billable, HasDatabase, HasDomains;

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
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'card_brand',
        'card_last_four',
        'trial_ends_at',
        'payment_provider',
        'payment_status',
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
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'card_brand',
            'card_last_four',
            'trial_ends_at',
            'payment_provider',
            'payment_status',
        ];
    }

    /**
     * Get the custom domains for this tenant
     */
    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    /**
     * Get the users for this tenant
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

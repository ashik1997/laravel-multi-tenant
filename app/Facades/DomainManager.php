<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class DomainManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'domain-manager';
    }
}

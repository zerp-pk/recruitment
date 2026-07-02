<?php

namespace Zerp\Recruitment\Providers;

use App\Events\DefaultData;
use App\Events\GivePermissionToRole;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Zerp\Recruitment\Listeners\DataDefault;
use Zerp\Recruitment\Listeners\GiveRoleToPermission;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        DefaultData::class => [
            DataDefault::class,
        ],
        GivePermissionToRole::class => [
            GiveRoleToPermission::class,
        ],
    ];
}
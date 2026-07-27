<?php

namespace Zerp\Recruitment\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Zerp\Recruitment\Providers\RecruitmentServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [RecruitmentServiceProvider::class];
    }
}

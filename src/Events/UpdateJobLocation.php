<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\JobLocation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class UpdateJobLocation
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public JobLocation $jobLocation
    ) {}
}
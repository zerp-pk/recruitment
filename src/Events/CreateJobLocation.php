<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\JobLocation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class CreateJobLocation
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public JobLocation $jobLocation
    ) {}
}
<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\JobType;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class UpdateJobType
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public JobType $jobtype
    ) {}
}
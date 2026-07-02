<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\JobPosting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class UpdateJobPosting
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public JobPosting $jobposting
    ) {}
}
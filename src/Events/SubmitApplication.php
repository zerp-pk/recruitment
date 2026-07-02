<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\Candidate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class SubmitApplication
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public Candidate $candidate
    ) {}
}
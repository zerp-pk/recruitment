<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\Candidate;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyCandidate
{
    use Dispatchable;

    public function __construct(
        public Candidate $candidate
    ) {}
}
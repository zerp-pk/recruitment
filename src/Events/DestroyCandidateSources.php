<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\CandidateSources;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyCandidateSources
{
    use Dispatchable;

    public function __construct(
        public CandidateSources $candidateSources
    ) {}
}
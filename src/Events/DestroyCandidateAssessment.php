<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\CandidateAssessment;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyCandidateAssessment
{
    use Dispatchable;

    public function __construct(
        public CandidateAssessment $candidateAssessment
    ) {}
}
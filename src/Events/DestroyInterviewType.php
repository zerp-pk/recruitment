<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\InterviewType;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyInterviewType
{
    use Dispatchable;

    public function __construct(
        public InterviewType $interviewType
    ) {}
}
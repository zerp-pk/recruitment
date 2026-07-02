<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\InterviewRound;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyInterviewRound
{
    use Dispatchable;

    public function __construct(
        public InterviewRound $interviewRound
    ) {}
}
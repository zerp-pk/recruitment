<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\InterviewFeedback;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyInterviewFeedback
{
    use Dispatchable;

    public function __construct(
        public InterviewFeedback $interviewFeedback
    ) {}
}
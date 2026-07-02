<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\InterviewRound;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class UpdateInterviewRound
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public InterviewRound $interviewRound
    ) {}
}
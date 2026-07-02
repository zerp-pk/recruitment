<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\InterviewType;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class UpdateInterviewType
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public InterviewType $interviewType
    ) {}
}
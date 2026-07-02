<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\OnboardingChecklist;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class CreateOnboardingChecklist
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public OnboardingChecklist $onboardingchecklist
    ) {}
}
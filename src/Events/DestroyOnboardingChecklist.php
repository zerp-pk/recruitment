<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\OnboardingChecklist;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyOnboardingChecklist
{
    use Dispatchable;

    public function __construct(
        public OnboardingChecklist $onboardingchecklist
    ) {}
}
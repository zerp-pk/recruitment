<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\ChecklistItem;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyChecklistItem
{
    use Dispatchable;

    public function __construct(
        public ChecklistItem $checklistItem
    ) {}
}
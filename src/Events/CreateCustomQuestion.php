<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\CustomQuestion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class CreateCustomQuestion
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public CustomQuestion $customQuestion
    ) {}
}
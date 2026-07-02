<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\Offer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Zerp\Hrm\Models\Employee;

class ConvertOfferToEmployee
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public Offer $offer,
        public Employee $employee
    ) {}
}
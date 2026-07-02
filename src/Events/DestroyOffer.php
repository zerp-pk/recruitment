<?php

namespace Zerp\Recruitment\Events;

use Zerp\Recruitment\Models\Offer;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyOffer
{
    use Dispatchable;

    public function __construct(
        public Offer $offer
    ) {}
}
<?php

namespace V3\App\Events\Email;

class AbandonedCourseCohortCart
{
    public function __construct(
        public int $paymentId,
        public string $cadence = 'generic'
    ) {
    }
}

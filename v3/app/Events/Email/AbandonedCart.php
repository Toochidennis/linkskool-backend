<?php

namespace V3\App\Events\Email;

class AbandonedCart
{
    public function __construct(
        public int $paymentId
    ) {
    }
}

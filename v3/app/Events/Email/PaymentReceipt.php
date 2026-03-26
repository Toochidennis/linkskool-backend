<?php

namespace V3\App\Events\Email;

class PaymentReceipt
{
    public function __construct(
        public int $paymentId
    ) {
    }
}

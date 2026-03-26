<?php

namespace V3\App\Events\Email;

class LicenseActivated
{
    public function __construct(
        public int $licenseId
    ) {
    }
}

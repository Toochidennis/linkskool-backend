<?php

namespace V3\App\Events\Auth;

class UserRegisteredFirstTime
{
    public function __construct(
        public int $userId,
        public string $email,
        public string $firstName,
        public string $lastName
    ) {
    }
}

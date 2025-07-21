<?php

namespace App\Channels\Git\Models;

class User
{
    public function __construct(
        public null|int|string $key = null,
        public null|string     $name = null,
        public null|string     $email = null,
        public null|string     $username = null
    )
    {
    }
}

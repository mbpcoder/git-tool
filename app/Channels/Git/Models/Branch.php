<?php

namespace App\Channels\Git\Models;


class Branch
{
    public function __construct(
        public string|null $name = null,
        public bool        $merged = false,
        public bool        $protected = false)
    {
    }
}

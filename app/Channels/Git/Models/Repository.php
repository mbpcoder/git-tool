<?php

namespace App\Channels\Git\Models;


use Carbon\Carbon;

class Repository
{

    public function __construct(
        public int|string|null    $key = null,
        public string|null        $path = null,
        public string|null        $url = null,
        public string|null        $name = null,
        public Carbon|string|null $created_at = null,
        public Carbon|string|null $updated_at = null)
    {
    }
}

<?php

namespace App\Channels\Git\Models;

use Carbon\Carbon;

class Commit
{
    public function __construct(
        public int|string|null    $key = null,
        public string|null $branch = null,
        public string|null        $message = null,
        public string|null        $committer_name = null,
        public int|string|null    $committer_id = null,
        public string|null        $committer_email = null,
        public Carbon|string|null $committed_at = null,
        public string|null        $url = null
    )
    {
    }

}

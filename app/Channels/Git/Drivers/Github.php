<?php

namespace App\Channels\Git\Drivers;


use App\Channels\Git\IGit;
use App\Channels\Git\Models\Repository;
use Illuminate\Support\Collection;

class Github implements IGit
{

    public function getUsers(): array|Collection
    {
        // TODO: Implement getUsers() method.
    }

    public function getRepositories(): array|Collection
    {
        // TODO: Implement getRepositories() method.
    }

    public function getCommits(Repository $repository): array|Collection
    {
        // TODO: Implement getCommits() method.
    }
}

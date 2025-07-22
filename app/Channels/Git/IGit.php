<?php


namespace App\Channels\Git;

use App\Channels\Git\Models\Branch;
use App\Channels\Git\Models\Repository;
use App\Channels\Git\Models\User;
use Illuminate\Support\Collection;

interface IGit
{
    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection|array;

    /**
     * @return Collection|Repository[]
     */
    public function getRepositories(): Collection|array;

    public function getBranches(Repository $branch): array|Collection;

    public function getCommits($repository, Branch $branch, int|null $agoDays = null): array|Collection;
}

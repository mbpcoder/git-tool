<?php


namespace App\Channels\Git;

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

    public function getCommits(Repository $repository, int|null $agoDays = null): Collection|array;
}

<?php

namespace App\Channels\Git;


use App\Channels\Git\Models\Branch;
use Illuminate\Support\Collection;

class GitManager implements IGit
{
    private IGit $driver;

    public function __construct(string $className)
    {
        $this->driver = $this->getDriver($className);
    }


    private function getDriver($className)
    {
        $class = 'App\\Channels\\Git\\Drivers\\' . $className;
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Git driver class '$class' does not exist.");
        }
        return new $class();
    }

    public function getUsers(): array|Collection
    {
        return $this->driver->getUsers();
    }

    public function getRepositories(): array|Collection
    {
        return $this->driver->getRepositories();
    }

    public function getBranches($branch): array|Collection
    {
        return $this->driver->getBranches($branch);
    }

    public function getCommits($repository, Branch $branch, int|null $agoDays = null): array|Collection
    {
        return $this->driver->getCommits($repository, $branch, $agoDays);
    }
}

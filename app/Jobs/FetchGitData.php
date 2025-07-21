<?php

namespace App\Jobs;

use App\Services\GitServices;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchGitData implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $gitDriver
    )
    {
        //
    }

    public function handle(): void
    {
        $gitServices = new GitServices($this->gitDriver);
        $gitServices->storeUsers();
        //$gitServices->storeRepositories();
        $gitServices->storeCommits();
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\FetchGitData;
use Illuminate\Console\Command;

class UpdateGit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-git';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch_sync(new FetchGitData('Gitlab'));
    }
}

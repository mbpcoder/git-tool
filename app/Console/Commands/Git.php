<?php

namespace App\Console\Commands;

use App\Git\Commit;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class Git extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:git';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get all git';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = 'C:\Users\Bagheri\Documents\Projects';

        $this->findAndStoreRepositories($path);
        $this->storeRepositoryCommits();
    }

    private function storeRepositoryCommits(): void
    {
        $repositories = Repository::query()
            ->where('is_active', true)
            ->get();
        foreach ($repositories as $_repository) {
            $git = new \App\Git\Git();
            $git->setPath($_repository->path);
            $commits = $git->getAllCommits();

            $this->info($_repository->name . ' commits count:' . $commits->count());

            $commits = $commits->reverse();

            foreach ($commits as $_commit) {
                $author = $this->storeAuthor($_commit);
                $this->storeCommit($_repository, $_commit, $author);
            }
        }
    }

    private function findAndStoreRepositories(string $path, int $level = 1): void
    {
        if ($level <= 2) {
            $allDirectories = \Illuminate\Support\Facades\File::directories($path);
            foreach ($allDirectories as $_directory) {
                $this->info($_directory);
                $git = new \App\Git\Git();
                $git->setPath($_directory);
                if (str_contains($_directory, 'isovisitrev')) {
                    $a = 2;
                }
                if ($git->isRepository()) {
                    $repository = $this->storeRepository($_directory, $git->getRemoteName(), $git->getRemoteUrl());
                } else {
                    $this->findAndStoreRepositories($_directory, $level + 1);
                }
            }
        }
    }

    private function checkGitAndUpdate($path, $level = 1)
    {
        if ($level <= 2) {
            $allDirectories = \Illuminate\Support\Facades\File::directories($path);
            foreach ($allDirectories as $_directory) {
                $this->info($_directory);
                $git = new \App\Git\Git();
                $git->setPath($_directory);
                if ($git->isRepository()) {
                    $repository = $this->storeRepository($_directory, $git->getRemoteName(), $git->getRemoteUrl());

                    $this->info('Branch: ' . $git->getCurrentBranch());
                    if (!$git->hasUnCommittedChange()) {
                        $this->info($git->pull());
                        $this->extractAllCommits($_directory);

                    } else {
                        $this->info($git->getUnCommittedChanges());
                    }
                } else {
                    $this->checkGitAndUpdate($_directory, $level + 1);
                }
            }
        }
    }

    private function storeRepository($path, $remoteName, $remoteUrl): Repository
    {
        $repository = Repository::query()
            ->where('path', $path)
            ->first();

        if ($repository === null) {
            $repository = Repository::query()->create([
                'path' => $path,
                'name' => $remoteName,
                'url' => $remoteUrl,
                'is_active' => true,
            ]);
        }
        return $repository;
    }

    private function storeAuthor(Commit $gitCommit): User
    {
        // 92468622a326424b4c298088d3f2d47236ec3cb0
        $user = User::query()
            ->where('email', $gitCommit->commitAuthorEmail)
            ->first();

        if ($user === null) {
            $user = User::query()->create([
                'name' => $gitCommit->commitAuthorName,
                'email' => $gitCommit->commitAuthorEmail,
                'password' => bcrypt(Str::random(16)),
            ]);
        }
        return $user;
    }

    private function storeCommit(Repository $repository, Commit $gitCommit, User $author): \App\Models\Commit
    {
        $commit = \App\Models\Commit::query()
            ->where('repository_id', $repository->id)
            ->where('hash', $gitCommit->hash)
            ->first();

        if ($commit === null) {
            try {
                $commit = \App\Models\Commit::query()->create([
                    'repository_id' => $repository->id,
                    'author_user_id' => $author->id,
                    'branch' => $gitCommit->branch,
                    'hash' => $gitCommit->hash,
                    'message' => $gitCommit->message,
                    'commit_at' => $gitCommit->createdAt
                ]);
            } catch (\Throwable $e) {
                $a = 1;
            }
            // 92468622a326424b4c298088d3f2d47236ec3cb0
        }
        return $commit;
    }

    private function extractAllCommits(string $path)
    {
        $this->info($path);
        $git = new \App\Git\Git();
        $git->setPath($path);
        if ($git->isRepository()) {
            $this->info('Branch: ' . $git->getCurrentBranch());
            $commits = $git->getAllCommits();

        }
    }
}

<?php

namespace App\Services;

use App\Channels\Git\GitManager;
use App\Models\Commit;
use App\Models\Repository;
use App\Models\User;
use Carbon\Carbon;

readonly class GitServices
{
    private GitManager $gitManager;

    public function __construct(string $gitDriver)
    {
        $this->gitManager = new GitManager($gitDriver);
    }

    public function storeUsers(): void
    {
        $users = $this->gitManager->getUsers();
        foreach ($users as $user) {

            $data = [
                'username' => $user->username,
                'name' => $user->name
            ];
            if (!empty($user->email)) {
                $data['email'] = $user->email;
            }

            User::query()->updateOrInsert([
                'username' => $user->username
            ], $data);
        }
    }

    public function storeRepositories(): void
    {
        $repositories = $this->gitManager->getRepositories();
        foreach ($repositories as $repo) {
            $repository = Repository::query()->updateOrCreate([
                'url' => $repo->url
            ], [
                    'key' => $repo->key,
                    'name' => $repo->name,
                    'path' => $repo->path,
                    'created_at' => $repo->created_at,
                    'updated_at' => $repo->updated_at
                ]
            );
        }
    }

    public function storeCommits(): void
    {
        $loggedUsers = [];
        Repository::query()->chunk(100, function ($items) use (&$loggedUsers) {

            foreach ($items as $repository) {

                $branches = $this->gitManager->getBranches($repository);

                foreach ($branches as $branch) {
                    $commits = $this->gitManager->getCommits($repository, $branch, 1);
                    foreach ($commits as $commit) {
                        $userId = User::query()
                            ->where('email', $commit->committer_email)
                            ->orWhere('email2', $commit->committer_email)
                            ->orWhere('email3', $commit->committer_email)
                            ->value('id');

                        if ($userId === null) {

//                            if (!isset($loggedUsers[$commit->committer_email])) {
//                                file_put_contents('email.txt', $commit->committer_name . '   ' . $commit->committer_email . PHP_EOL, FILE_APPEND);
//                                $loggedUsers[$commit->committer_email] = true;
//                            }

                            continue;
                        }

                        try {
                            Commit::query()->updateOrCreate([
                                'hash' => $commit->key
                            ], [
                                    'branch' => $commit->branch,
                                    'message' => $commit->message,
                                    'commit_at' => Carbon::createFromTimeString($commit->committed_at),
                                    'repository_id' => $repository->id,
                                    'author_user_id' => $userId
                                ]
                            );
                        } catch (\Throwable $e) {
                            echo $e->getMessage() . PHP_EOL;
                        }
                    }
                }


            }

        });


    }

}

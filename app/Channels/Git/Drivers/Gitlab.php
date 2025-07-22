<?php

namespace App\Channels\Git\Drivers;


use App\Channels\Git\IGit;
use App\Channels\Git\Models\Branch;
use App\Channels\Git\Models\Commit;
use App\Channels\Git\Models\Repository;
use App\Channels\Git\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Gitlab implements IGit
{
    private string $token;
    private string $host;

    public function __construct()
    {
        $this->host = config('services.gitlab.host');
        $this->token = config('services.gitlab.token');
    }

    public function getUsers(): array|Collection
    {
        $page = 1;
        $perPage = 100;

        $users = collect();
        do {
            $path = "/users?per_page={$perPage}&page={$page}";

            $usersData = $this->httpGet($path);

            foreach ($usersData as $userData) {
                $user = new User();
                $user->key = $userData['id'];
                $user->name = !empty($userData['name']) ? $userData['name'] : null;
                $user->email = !empty($userData['public_email']) ? $userData['public_email'] : null;
                $user->username = !empty($userData['username']) ? $userData['username'] : null;

                $users->push($user);
            }
            $page++;

        } while (count($usersData) === $perPage);


        return $users;
    }

    public function getRepositories(): array|Collection
    {
        $page = 1;
        $perPage = 100;

        $repositories = collect();
        do {
            $path = "/projects?per_page={$perPage}&page={$page}";

            $repositoriesData = $this->httpGet($path);

            foreach ($repositoriesData as $repoData) {
                $repository = new Repository();

                $repository->key = $repoData['id'];
                $repository->name = $repoData['name'];
                $repository->url = $repoData['web_url'];
                $repository->path = $repoData['path_with_namespace'];
                $repository->created_at = $repoData['created_at'];
                $repository->updated_at = $repoData['updated_at'];

                $repositories->push($repository);
            }
            $page++;

        } while (count($repositoriesData) === $perPage);

        return $repositories;
    }

    public function getBranches($repository): array|Collection
    {
        $page = 1;
        $perPage = 100;

        $branches = collect();
        do {
            $path = "/projects/{$repository->key}/repository/branches?per_page={$perPage}&page={$page}";

            $branchesData = $this->httpGet($path);

            foreach ($branchesData as $_branchData) {
                $branch = new Branch(
                    name: $_branchData['name'],
                    merged: $_branchData['merged'],
                    protected: $_branchData['protected'],
                );
                $branches->push($branch);
            }
            $page++;

        } while (count($branchesData) === $perPage);

        return $branches;
    }

    public function getCommits($repository, Branch $branch, int|null $agoDays = null): array|Collection
    {
        $commits = collect();
        $page = 1;
        $perPage = 100;

        $since = null;
        if ($agoDays !== null) {
            $since = now()->subDays($agoDays)->toIso8601String();
        }


        do {
            $query = [
                'per_page' => $perPage,
                'page' => $page,
                'ref_name' => $branch->name
            ];

            if ($since !== null) {
                $query['since'] = $since;
            }


            $path = "/projects/{repositoryKey}/repository/commits";

            $commitsData = $this->httpGet($path, [
                'repositoryKey' => $repository->key,
            ], $query);

            if ($commitsData !== []) {
                foreach ($commitsData as $commitData) {
                    $commit = new Commit();

                    $commit->key = $commitData['id'];
                    $commit->message = $commitData['message'];
                    $commit->branch = $branch->name;
                    $commit->committer_name = $commitData['author_name'];
                    $commit->committer_email = $commitData['author_email'];
                    $commit->committed_at = $commitData['committed_date'];
                    $commit->url = $commitData['web_url'];

                    $commits->push($commit);
                }
                $page++;
            }
        } while (count($commitsData) === $perPage);

        return $commits;
    }

    private function httpGet($path, $parameters = [], array $query = [])
    {
        if (!empty($query)) {
            $path = $path . '?' . http_build_query($query);
        }

        $response = Http::withHeader('PRIVATE-TOKEN', $this->token)
            ->connectTimeout(60)
            ->retry(10, 1000)
            ->withUrlParameters($parameters)
            ->get($this->host . $path);
        $response->throwIfClientError();
        return $response->json();
    }
}

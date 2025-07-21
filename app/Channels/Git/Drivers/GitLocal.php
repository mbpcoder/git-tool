<?php

namespace App\Channels\Git\Drivers;


use App\Channels\Git\IGit;
use App\Channels\Git\Models\Commit;
use App\Channels\Git\Models\Repository;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;

class GitLocal implements IGit
{
    private string $basePath;

    public function __construct()
    {
        $this->basePath = config('services.gitlocal.path');
    }

    public function getUsers(): array|Collection
    {
        return [];
    }

    public function getRepositories(): array|Collection
    {
        $reposPath = $this->getSubDirectories($this->basePath);

        $repositories = collect();
        // check the path is a git directory
        foreach ($reposPath as $path) {
            $dirPath = $this->basePath . '/' . $path;
            $isGitDir = $this->commandExecuter('git rev-parse --show-toplevel', $dirPath);
            if ($isGitDir) {
                $repo = new Repository();
                $repo->url = $this->getRemoteUrl($dirPath);
                $repo->path = $dirPath;
                $repo->name = basename($repo->url);
                $repositories->push($repo);
            }
        }
        return $repositories;
    }

    public function getCommits($repository): array|Collection
    {
        $output = $this->commandExecuter('git log --pretty="format:%H<~>%s<~>%an<~>%ad<~>%ae"', $repository->path);
        $commitsData = explode("\n", $output);
        if ($commitsData[0] === "") {
            return [];
        }
        $commits = collect();
        foreach ($commitsData as $commitData) {
            [$hash, $message, $author_name, $date, $author_email] = explode('<~>', $commitData);
            $commit = new Commit();

            $commit->key = $hash;
            $commit->message = $message;
            $commit->committer_name = $author_name;
            $commit->committer_email = $author_email;
            $commit->committed_at = $date;

            $commits->push($commit);
        }
        return $commits;
    }

    private function commandExecuter($cmd, string $dir = null)
    {
        $process = Process::fromShellCommandline($cmd, $dir);
        $process->run();

        if (!$process->isSuccessful() && $process->getOutput() !== "") {
            throw new ProcessFailedException($process);
        }
        return $process->getOutput();
    }

    private function getSubDirectories(string $baseDir): array
    {
        $scanResult = scandir($baseDir);
        $subDirectories = array_diff($scanResult, ['.', '..']);

        //filter non directory path
        $subDirectories = array_filter($subDirectories, function ($path) use ($baseDir) {
            return is_dir($baseDir . '/' . $path);
        });

        return $subDirectories;
    }

    private function getRemoteUrl(string $dirPath): string
    {
        $remoteUrl = $this->commandExecuter('git config --get remote.origin.url', $dirPath);
        if ($remoteUrl !== '') {
            $remoteUrl = rtrim($remoteUrl);
            return str_replace('.git', '', $remoteUrl);
        } else {
            return $dirPath;
        }
    }
}

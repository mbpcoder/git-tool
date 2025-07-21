<?php

namespace App\Git;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class Git
{
    private string $path;

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function isRepository(): bool
    {
        $path = $this->path . DIRECTORY_SEPARATOR . '.git';
        return File::exists($path) && File::isDirectory($path);
    }

    public function hasUnCommittedChange(): bool
    {
        $result = $this->execute('git status -s');
        return !empty($result->output());
    }

    public function getUnCommittedChanges(): string
    {
        $result = $this->execute('git status -s');
        return $result->output();
    }

    public function getCurrentBranch(): string
    {
        $result = $this->execute('git branch --show-current');
        return trim($result->output());
    }

    public function getAllBranches(): string
    {
        $result = $this->execute('git branch');
        return $result->output();
    }

    public function getAllCommits(): string|Collection
    {
        $branch = $this->getCurrentBranch();
        $result = $this->execute('git log --pretty=oneline --pretty=fuller');
        $commitString = $result->output();

        $commits = collect();
        $count = preg_match_all('/^commit\s\b[0-9a-f]{40}\b/m', $commitString, $matches);
        for ($i = 0; $i < $count; $i++) {

            $firstHash = $matches[0][$i];
            if ($i > 0) {
                $firstHash = "\n" . $firstHash;
            }
            $firstHashPosition = strpos($commitString, $firstHash);

            if ($i + 1 === $count) {
                // last
                $secondHashPosition = strlen($commitString);
            } else {
                $secondHash = $matches[0][$i + 1];
                $secondHashPosition = strpos($commitString, "\n" . $secondHash);
            }

            $commitStr = trim(substr($commitString, $firstHashPosition, $secondHashPosition - $firstHashPosition));
            preg_match('/^commit\s(?<CommitCode>.+)\n/', $commitStr, $hash);
            preg_match('/Author:\s+(?<Author>.+)\n/', $commitStr, $author);
            preg_match('/Commit:\s+(?<Commit>.+)\n/', $commitStr, $commitAuthor);
            preg_match('/AuthorDate:\s+(?<AuthorDate>.+)\n/', $commitStr, $authorDate);
            preg_match('/CommitDate:\s+(?<CommitDate>.+)\n/', $commitStr, $commitDate);
            preg_match('/\n\n(?<Message>[\s\S]+)$/', $commitStr, $message);

            try {
                $authorDate = date_create_from_format('D M d H:i:s Y O', $authorDate['AuthorDate']);
                $commitDate = date_create_from_format('D M d H:i:s Y O', $commitDate['CommitDate']);
            } catch (\Throwable $exception) {
                $a = 1;
            }

            $commit = new Commit();
            $commit->branch = $branch;
            $commit->hash = $hash['CommitCode'] ?? null;

            $author = trim($author['Author']);
            $startPos = strpos($author, '<');
            $endPos = strrpos($author, '>');
            if ($startPos !== false && $endPos !== false) {
                $commit->authorName = trim(substr($author, 0, $startPos));
                $commit->authorEmail = substr($author, $startPos + 1, $endPos - $startPos - 1);
            }

            $commitAuthor = trim($commitAuthor['Commit']);
            $startPos = strpos($commitAuthor, '<');
            $endPos = strrpos($commitAuthor, '>');
            if ($startPos !== false && $endPos !== false) {
                $commit->commitAuthorName = trim(substr($commitAuthor, 0, $startPos));
                $commit->commitAuthorEmail = substr($commitAuthor, $startPos + 1, $endPos - $startPos - 1);
            }

            $commit->authorCreatedAt = $authorDate->format('Y-m-d H:i:s');
            $commit->createdAt = $commitDate->format('Y-m-d H:i:s');
            $commit->message = trim($message['Message']);

            if (str_contains($commit->message, 'commit')) {
                $a = 1;
            }


            $commits->push($commit);
        }

        return $commits;
    }

    public function clearUnCommittedChanges(): string
    {
        $result = $this->execute('git checkout --force');
        return $result->output();
    }

    public function getRemoteUrl(): string|null
    {
        // to scape exception when there is not any remote
        $result = $this->execute('git remote -v');

        if (empty($result->output())) return null;

        $result = $this->execute('git config --get remote.origin.url');
        return trim($result->output());
    }

    public function getRemoteName(): string|null
    {
        if (str_contains('isovisitrev', $this->path)) {
            $a = '';
        }
        $remoteUrl = $this->getRemoteUrl();

        if ($remoteUrl === null) {
            return basename($this->path);
        }

        // Split the string by '/'
        $urlParts = explode('/', $remoteUrl);

        // Get the last part of the URL, which contains the repository information
        $repositoryFullName = end($urlParts);

        // Remove '.git' extension if present
        return rtrim(trim($repositoryFullName), '.git');
    }

    public function pull(): string
    {
        $result = $this->execute('git pull');
        return $result->output();
    }

    private function execute($command): ProcessResult
    {
        $result = Process::timeout(120)->run("cd {$this->path} && " . $command);
        if ($result->failed()) {
            throw new ProcessFailedException($result);
        }
        return $result;
    }
}

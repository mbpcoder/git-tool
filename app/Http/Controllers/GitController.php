<?php

namespace App\Http\Controllers;

use App\Http\Requests\Git\GitUserCommitsCountsInPeriodRequest;
use App\Http\Requests\Git\GitUsersCommitsCountsRequest;
use App\Http\Resources\Git\GitResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GitController extends Controller
{

    public function getAll(): GitResource
    {
        $lastCommitData = $this->getLastCommitData();
        $commitCountsPerMonth = $this->getCommitCountsPerPeriod(
            Carbon::now()->startOfMonth()->toDateTimeString(),
            Carbon::now()->endOfMonth()->toDateTimeString()
        );
        $commitCountsPerWeek = $this->getCommitCountsPerPeriod(
            Carbon::now()->startOfWeek()->toDateTimeString(),
            Carbon::now()->endOfWeek()->toDateTimeString()
        );
        $commitCountsPerDay = $this->getCommitCountsPerPeriod(
            Carbon::now()->startOfDay()->toDateTimeString(),
            Carbon::now()->endOfDay()->toDateTimeString()
        );

        $commitData = [
            'last_commit' => $lastCommitData,
            'commit_counts_per_month' => $commitCountsPerMonth,
            'commit_counts_per_day' => $commitCountsPerDay,
            'commit_counts_per_week' => $commitCountsPerWeek,
        ];

        return new GitResource($commitData);
    }

    public function getUsersCommitsCounts(GitUsersCommitsCountsRequest $request): GitResource
    {
        $usersId = $request->validated('usersId');

        $data = DB::table('commits')
            ->whereIn('author_user_id', $usersId)
            ->select('author_user_id as user_id')
            ->selectRaw('COUNT(*) total_commits_count')
            ->selectRaw('COUNT(CASE WHEN commit_at >= ? THEN 1 END) as today_commits_count',
                [Carbon::now()->startOfDay()])
            ->selectRaw('COUNT(CASE WHEN commit_at >= ? THEN 1 END) as month_commits_count',
                [Carbon::now()->submonth()->startOfDay()])
            ->selectRaw('COUNT(CASE WHEN commit_at >= ? THEN 1 END) as week_commits_count',
                [Carbon::now()->subWeek()->startOfDay()])
            ->groupBy('author_user_id')
            ->get();

        return new GitResource($data);
    }

    public function getUserCommitsCountsInPeriod(GitUserCommitsCountsInPeriodRequest $request): GitResource
    {
        $userId = $request->validated('userId');
        $startDate = Carbon::createFromFormat('Y-m-d', $request->validated('startDateTime'));
        $endDate = $request->validated('endDateTime') ? Carbon::createFromFormat('Y-m-d', $request->validated('endDateTime')) : Carbon::now();

        $data = DB::table('commits')
            ->selectRaw('DATE (commit_at) as date')
            ->selectRaw('COUNT(*) AS commit_count')
            ->where('author_user_id', $userId)
            ->whereBetween('commit_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return new GitResource($data);
    }

    private function getLastCommitData(): Collection
    {
        return DB::table('commits')
            ->select('author_user_id')
            ->selectRaw('COUNT(*) as commit_count')
            ->selectRaw('MAX(commit_at) as last_commit_at')
            ->groupBy('author_user_id')
            ->get();
    }

    private function getCommitCountsPerPeriod(string $startDateTime, string $endDateTime): Collection
    {
        return DB::table('commits')
            ->select('author_user_id')
            ->selectRaw('COUNT(*) as commit_count')
            ->selectRaw('MAX(commit_at) as last_commit_at')
            ->whereBetween('commit_at', [$startDateTime, $endDateTime])
            ->groupBy('author_user_id')
            ->get();
    }

}

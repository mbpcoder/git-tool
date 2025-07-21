<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Commit
 *
 * @property int $id
 * @property int $repository_id,
 * @property int $author_user_id,
 * @property string $branch,
 * @property string $hash,
 * @property string $message,
 * @property Carbon $commit_at'
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App
 */
class Commit extends Model
{
    use HasFactory;

    protected $table = 'commits';

    protected array $dates = [
        'commit_at',
    ];

    protected $fillable = [
        'repository_id',
        'author_user_id',
        'branch',
        'hash',
        'message',
        'commit_at'
    ];
}

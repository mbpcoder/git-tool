<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Repository
 *
 * @property int $id
 * @property string $name
 * @property string $url
 * @property string $path
 * @property bool is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App
 */
class Repository extends Model
{
    use HasFactory;

    protected $table = 'repositories';

    protected $fillable = [
        'key',
        'name',
        'url',
        'path',
        'is_active',
        'created_at',
        'updated_at'
    ];
}

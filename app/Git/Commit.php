<?php

namespace App\Git;

use Illuminate\Database\Eloquent\Model;

class Commit extends Model
{
    public string $branch;
    public string $hash;
    public string $authorName;
    public string $authorEmail;
    public string $commitAuthorName;
    public string $commitAuthorEmail;
    public string $authorCreatedAt;
    public string $createdAt;
    public string $message;
}

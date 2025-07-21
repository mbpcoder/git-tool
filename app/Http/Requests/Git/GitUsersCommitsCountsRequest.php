<?php

namespace App\Http\Requests\Git;

use Illuminate\Foundation\Http\FormRequest;

class GitUsersCommitsCountsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'usersId' => 'required|array',
            'usersId.*' => 'integer|exists:users,id'
        ];
    }
}

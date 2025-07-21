<?php

namespace App\Http\Requests\Git;

use Illuminate\Foundation\Http\FormRequest;

class GitUserCommitsCountsInPeriodRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'userId' => 'required|exists:users,id',
            'startDateTime' => 'required|date_format:Y-m-d',
            'endDateTime' => 'date_format:Y-m-d',
        ];
    }
}

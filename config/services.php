<?php

return [

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'gitlab' => [
        'token' => env('GITLAB_TOKEN'),
        'host' => env('GITLAB_HOST')
    ],

    'git-local' => [
        'path' => env('GIT_LOCAL_PATH'),
    ],

];

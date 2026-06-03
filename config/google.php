<?php
return [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect_uri'  => env('GOOGLE_REDIRECT_URI'),
    'scopes'        => [
        'openid',
        'profile',
        'email',
        'https://www.googleapis.com/auth/drive.file',
    ],
    'access_type'   => 'offline',
    'prompt'        => env('GOOGLE_PROMPT'), // Kosongkan agar Google tidak memaksa consent setiap login.
    'drive' => [
        'folder_name' => env('APP_NAME', 'Sistem Backup'),
        'photos_subfolder'  => 'Foto',
        'backups_subfolder' => 'Backup',
    ],
];

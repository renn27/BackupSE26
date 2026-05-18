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
    'prompt'        => 'consent',   // WAJIB untuk dapat refresh_token setiap login
    'drive' => [
        'folder_name' => env('APP_NAME', 'Sistem Backup'),
        'photos_subfolder'  => 'Foto',
        'backups_subfolder' => 'Backup',
    ],
];

<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Schedule::call(function () {
    // Bersihkan file di folder temp yang usianya lebih dari 1 jam
    $files = Storage::disk('local')->files('temp');
    $now = now();

    foreach ($files as $file) {
        $lastModified = Storage::disk('local')->lastModified($file);
        if ($now->diffInHours(\Carbon\Carbon::createFromTimestamp($lastModified)) >= 1) {
            Storage::disk('local')->delete($file);
        }
    }
})->hourly();

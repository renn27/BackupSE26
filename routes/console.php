<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Services\PushNotificationService;
use Minishlink\WebPush\VAPID;

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

Artisan::command('webpush:keys', function () {
    try {
        $keys = VAPID::createVapidKeys();
    } catch (Throwable $exception) {
        $this->error('Gagal generate VAPID key lewat PHP OpenSSL: '.$exception->getMessage());
        $this->warn('Di Windows, gunakan Node crypto atau generate di environment server, lalu isi WEBPUSH_VAPID_* di .env.');
        return 1;
    }

    $this->info('Tambahkan ke .env:');
    $this->line('WEBPUSH_VAPID_SUBJECT='.config('app.url'));
    $this->line('WEBPUSH_VAPID_PUBLIC_KEY='.$keys['publicKey']);
    $this->line('WEBPUSH_VAPID_PRIVATE_KEY='.$keys['privateKey']);

    return 0;
})->purpose('Generate VAPID keys untuk Web Push.');

Artisan::command('push:remind-backup {--user=} {--dry-run}', function (PushNotificationService $pushNotificationService) {
    if (! $pushNotificationService->isConfigured()) {
        $this->error('Web Push belum dikonfigurasi. Jalankan php artisan webpush:keys lalu isi WEBPUSH_VAPID_* di .env.');
        return 1;
    }

    $users = User::query()
        ->where('role', 'petugas')
        ->where('status', 'active')
        ->whereHas('pushSubscriptions')
        ->when($this->option('user'), fn ($query, $userId) => $query->whereKey($userId))
        ->with('pushSubscriptions')
        ->get();

    $totalStats = ['sent' => 0, 'failed' => 0, 'expired' => 0, 'users' => 0];

    foreach ($users as $user) {
        $totalStats['users']++;

        $payload = [
            'title' => 'Waktunya Backup Data',
            'body' => 'Jangan lupa backup data SE2026 hari ini agar pekerjaan tetap aman.',
            'url' => route('petugas.dashboard'),
            'tag' => 'backup-reminder-'.$user->id.'-'.now()->format('YmdH'),
            'ttl' => 7200,
        ];

        if ($this->option('dry-run')) {
            $this->line("[dry-run] {$user->email}: {$payload['body']}");
            continue;
        }

        $stats = $pushNotificationService->sendToSubscriptions($user->pushSubscriptions, $payload);
        $totalStats['sent'] += $stats['sent'];
        $totalStats['failed'] += $stats['failed'];
        $totalStats['expired'] += $stats['expired'];
    }

    $this->info("Reminder selesai. Users: {$totalStats['users']}, sent: {$totalStats['sent']}, failed: {$totalStats['failed']}, expired removed: {$totalStats['expired']}.");

    return 0;
})->purpose('Kirim Web Push pengingat backup ke petugas aktif.');

foreach (['19:00', '20:00', '21:00'] as $backupReminderTime) {
    Schedule::command('push:remind-backup')
        ->dailyAt($backupReminderTime)
        ->withoutOverlapping();
}

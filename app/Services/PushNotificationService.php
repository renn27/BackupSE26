<?php

namespace App\Services;

use App\Models\PushSubscription as StoredPushSubscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    public function isConfigured(): bool
    {
        return filled(config('webpush.vapid.public_key'))
            && filled(config('webpush.vapid.private_key'))
            && filled(config('webpush.vapid.subject'));
    }

    public function sendToSubscription(StoredPushSubscription $subscription, array $payload): bool
    {
        return $this->sendToSubscriptions(collect([$subscription]), $payload)['sent'] > 0;
    }

    public function sendToSubscriptions(Collection $subscriptions, array $payload): array
    {
        if (! $this->isConfigured() || $subscriptions->isEmpty()) {
            return [
                'sent' => 0,
                'failed' => 0,
                'expired' => 0,
                'errors' => [$this->isConfigured() ? 'Belum ada browser yang mengaktifkan notifikasi.' : 'Konfigurasi VAPID belum lengkap.'],
            ];
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('webpush.vapid.subject'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ], [
            'TTL' => $payload['ttl'] ?? 3600,
            'urgency' => $payload['urgency'] ?? 'normal',
        ], null, [
            'verify' => (bool) config('webpush.verify_ssl'),
        ]);

        $payloadJson = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        foreach ($subscriptions as $subscription) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->public_key,
                    'authToken' => $subscription->auth_token,
                    'contentEncoding' => $subscription->content_encoding ?: 'aes128gcm',
                ]),
                $payloadJson
            );
        }

        $stats = ['sent' => 0, 'failed' => 0, 'expired' => 0, 'errors' => []];

        foreach ($webPush->flush() as $report) {
            $storedSubscription = $subscriptions->firstWhere('endpoint', $report->getEndpoint());

            if ($report->isSuccess()) {
                $stats['sent']++;
                $storedSubscription?->forceFill(['last_used_at' => now()])->save();
                continue;
            }

            $stats['failed']++;

            if ($report->isSubscriptionExpired()) {
                $stats['expired']++;
                $storedSubscription?->delete();
            }

            $stats['errors'][] = $report->getReason();

            Log::warning('Web push delivery failed.', [
                'endpoint' => $report->getEndpoint(),
                'reason' => $report->getReason(),
                'expired' => $report->isSubscriptionExpired(),
            ]);
        }

        $stats['errors'] = array_values(array_unique(array_filter($stats['errors'])));

        return $stats;
    }
}

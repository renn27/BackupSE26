<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebPushSubscriptionController extends Controller
{
    public function config(): JsonResponse
    {
        return response()->json([
            'enabled' => filled(config('webpush.vapid.public_key')),
            'publicKey' => config('webpush.vapid.public_key'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'contentEncoding' => ['nullable', 'string', 'max:30'],
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'user_id' => $request->user()->id,
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => $validated['contentEncoding'] ?? 'aes128gcm',
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'last_used_at' => now(),
            ]
        );

        return response()->json(['message' => 'Subscription notifikasi berhasil disimpan.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
        ]);

        PushSubscription::where('user_id', $request->user()->id)
            ->where('endpoint', $validated['endpoint'])
            ->delete();

        return response()->json(['message' => 'Subscription notifikasi berhasil dihapus.']);
    }

    public function test(Request $request, PushNotificationService $pushNotificationService): JsonResponse
    {
        $stats = $pushNotificationService->sendToSubscriptions(
            $request->user()->pushSubscriptions()->get(),
            [
                'title' => 'Notifikasi ASISTEN SE2026 aktif',
                'body' => 'Pengingat akan tetap masuk dari browser ini selama izin notifikasi aktif.',
                'url' => $request->user()->isSuperAdmin()
                    ? route('admin.dashboard')
                    : route('petugas.dashboard'),
                'tag' => 'webpush-test',
                'ttl' => 60,
            ]
        );

        $message = $stats['sent'] > 0
            ? 'Notifikasi percobaan dikirim.'
            : 'Notifikasi percobaan gagal dikirim.';

        return response()->json([
            'message' => $message,
            'stats' => $stats,
        ], $stats['sent'] > 0 ? 200 : 422);
    }
}

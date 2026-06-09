const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const pushMenu = document.querySelector('[data-web-push-menu]');
const pushToggleButton = document.querySelector('[data-web-push-toggle]');
const pushSwitch = document.querySelector('[data-web-push-switch]');
const pushSwitchTrack = document.querySelector('[data-web-push-switch-track]');
const pushSwitchKnob = document.querySelector('[data-web-push-switch-knob]');
const pushStatus = document.querySelector('[data-web-push-status]');
const pushTestButton = document.querySelector('[data-web-push-test]');

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; i++) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}

function uint8ArrayToUrlBase64(value) {
    const bytes = value instanceof ArrayBuffer ? new Uint8Array(value) : value;
    let binary = '';

    bytes.forEach((byte) => {
        binary += String.fromCharCode(byte);
    });

    return window.btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

function subscriptionMatchesPublicKey(subscription, publicKey) {
    const subscriptionKey = subscription?.options?.applicationServerKey;

    if (!subscriptionKey) {
        return true;
    }

    return uint8ArrayToUrlBase64(subscriptionKey) === publicKey;
}

function withTimeout(promise, timeoutMs = 4000) {
    return Promise.race([
        promise,
        new Promise((_, reject) => {
            window.setTimeout(() => reject(new Error('Operation timed out.')), timeoutMs);
        }),
    ]);
}

function formatPushError(result) {
    const reason = result?.stats?.errors?.[0] || result?.message || 'Gagal mengirim test notif.';

    if (reason.includes('cURL error 60')) {
        return 'SSL/cURL server bermasalah.';
    }

    if (reason.includes('cURL error 6') || reason.includes('Could not resolve host')) {
        return 'Server tidak bisa resolve FCM.';
    }

    if (reason.includes('cURL error 7') || reason.includes('Connection refused') || reason.includes('timed out')) {
        return 'Server tidak bisa akses FCM.';
    }

    if (reason.includes('410') || reason.includes('404')) {
        return 'Subscription browser sudah kedaluwarsa.';
    }

    return reason.length > 70 ? `${reason.slice(0, 67)}...` : reason;
}

function setPushButtonState(state) {
    if (!pushToggleButton) {
        return;
    }

    const labels = {
        unsupported: 'Notifikasi tidak didukung browser ini',
        disabled: 'Notifikasi belum dikonfigurasi',
        denied: 'Izin notifikasi diblokir',
        enabled: 'Notifikasi aktif',
        error: 'Gagal memuat status notifikasi',
        idle: 'Aktifkan notifikasi',
        loading: 'Menyiapkan notifikasi...',
    };

    pushToggleButton.title = labels[state] || labels.idle;
    pushToggleButton.setAttribute('aria-label', pushToggleButton.title);
    pushToggleButton.dataset.state = state;
    pushToggleButton.classList.toggle('text-green-600', state === 'enabled');
    pushToggleButton.classList.toggle('text-rose-500', state === 'denied');
    pushSwitch?.toggleAttribute('checked', state === 'enabled');

    if (pushSwitch) {
        pushSwitch.checked = state === 'enabled';
        pushSwitch.disabled = ['unsupported', 'disabled', 'denied', 'loading'].includes(state);
    }

    if (pushTestButton) {
        pushTestButton.disabled = state !== 'enabled';
    }

    if (pushStatus) {
        pushStatus.textContent = labels[state] || labels.idle;
    }

    pushSwitchTrack?.classList.toggle('bg-se-primary', state === 'enabled');
    pushSwitchTrack?.classList.toggle('bg-slate-300', state !== 'enabled');
    pushSwitchKnob?.classList.toggle('translate-x-5', state === 'enabled');
}

async function getServiceWorkerRegistration() {
    if (!('serviceWorker' in navigator)) {
        return null;
    }

    const registration = await navigator.serviceWorker.register('/sw.js');

    registration.update().catch(() => {});

    return registration;
}

async function showTestNotification() {
    if (Notification.permission !== 'granted') {
        throw new Error('Notification permission is not granted.');
    }

    const options = {
        body: 'Notifikasi browser sudah aktif. Pengingat backup ramah akan dikirim jam 19.00, 20.00, dan 21.00 WIB.',
        icon: '/images/asisten-se2026-icon-192.png',
        badge: '/images/asisten-se2026-icon-192.png',
        tag: `webpush-local-test-${Date.now()}`,
        data: { url: window.location.href },
    };

    try {
        const registration = await withTimeout(navigator.serviceWorker.ready, 4000)
            .catch(() => getServiceWorkerRegistration());

        await registration.showNotification('Test notif ASISTEN SE2026', options);
        return;
    } catch (error) {
        console.warn('Service worker notification failed, using Notification fallback.', error);
    }

    new Notification('Test notif ASISTEN SE2026', options);
}

async function saveSubscription(subscription) {
    const payload = subscription.toJSON();
    payload.contentEncoding = (window.PushManager?.supportedContentEncodings || ['aes128gcm'])[0] || 'aes128gcm';

    await fetch(window.webPushRoutes.subscribe, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
    });
}

async function removeSubscription(subscription) {
    await fetch(window.webPushRoutes.unsubscribe, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ endpoint: subscription.endpoint }),
    });
}

async function refreshPushButtonState(publicKey) {
    if (Notification.permission === 'denied') {
        setPushButtonState('denied');
        return;
    }

    const registration = await getServiceWorkerRegistration();
    const subscription = await registration?.pushManager.getSubscription();

    if (subscription) {
        if (!subscriptionMatchesPublicKey(subscription, publicKey)) {
            await removeSubscription(subscription);
            await subscription.unsubscribe();
            setPushButtonState('idle');
            return;
        }

        await saveSubscription(subscription);
        setPushButtonState('enabled');
        return;
    }

    setPushButtonState(publicKey ? 'idle' : 'disabled');
}

async function enablePushNotifications(publicKey) {
    setPushButtonState('loading');

    const permission = await Notification.requestPermission();

    if (permission !== 'granted') {
        setPushButtonState(permission === 'denied' ? 'denied' : 'idle');
        return;
    }

    const registration = await getServiceWorkerRegistration();
    let existingSubscription = await registration.pushManager.getSubscription();

    if (existingSubscription && !subscriptionMatchesPublicKey(existingSubscription, publicKey)) {
        await removeSubscription(existingSubscription);
        await existingSubscription.unsubscribe();
        existingSubscription = null;
    }

    const subscription = existingSubscription || await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(publicKey),
    });

    await saveSubscription(subscription);

    setPushButtonState('enabled');
}

async function disablePushNotifications() {
    setPushButtonState('loading');

    const registration = await getServiceWorkerRegistration();
    const subscription = await registration?.pushManager.getSubscription();

    if (subscription) {
        await removeSubscription(subscription);
        await subscription.unsubscribe();
    }

    setPushButtonState('idle');
}

async function sendTestPushNotification() {
    pushTestButton.disabled = true;
    pushStatus && (pushStatus.textContent = 'Mengirim test notif...');

    const response = await fetch(window.webPushRoutes.test, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    });

    const result = await response.json();

    if (!response.ok || (result.stats?.sent || 0) < 1) {
        throw new Error(formatPushError(result));
    }

    await showTestNotification();

    pushStatus && (pushStatus.textContent = 'Test notif dikirim.');
    pushTestButton.disabled = false;
}

async function initWebPush() {
    if (!pushMenu || !pushToggleButton || !window.webPushRoutes || !csrfToken) {
        return;
    }

    pushMenu.classList.remove('hidden');
    setPushButtonState('loading');

    if (!('Notification' in window) || !('PushManager' in window) || !('serviceWorker' in navigator)) {
        setPushButtonState('unsupported');
        pushSwitch && (pushSwitch.disabled = true);
        return;
    }

    let config;

    try {
        const response = await withTimeout(fetch(window.webPushRoutes.config, { headers: { 'Accept': 'application/json' } }));

        if (!response.ok) {
            throw new Error('Web Push config request failed.');
        }

        config = await response.json();
    } catch (error) {
        console.error('Gagal memuat konfigurasi Web Push.', error);
        setPushButtonState('error');
        return;
    }

    if (!config.enabled || !config.publicKey) {
        setPushButtonState('disabled');
        pushSwitch && (pushSwitch.disabled = true);
        return;
    }

    try {
        await refreshPushButtonState(config.publicKey);
    } catch (error) {
        console.error('Gagal membaca status subscription Web Push.', error);
        setPushButtonState('error');
        return;
    }

    pushSwitch?.addEventListener('change', async () => {
        try {
            if (pushSwitch.checked) {
                await enablePushNotifications(config.publicKey);
            } else {
                await disablePushNotifications();
            }
        } catch (error) {
            console.error('Gagal mengubah status notifikasi.', error);
            setPushButtonState('idle');
        }
    });

    pushTestButton?.addEventListener('click', async () => {
        try {
            await sendTestPushNotification();
        } catch (error) {
            console.error('Gagal mengirim test notifikasi.', error);
            pushStatus && (pushStatus.textContent = error.message || 'Gagal mengirim test notif.');
            pushTestButton.disabled = false;
        }
    });
}

window.addEventListener('load', () => {
    initWebPush().catch((error) => console.error('Gagal memuat Web Push.', error));
});

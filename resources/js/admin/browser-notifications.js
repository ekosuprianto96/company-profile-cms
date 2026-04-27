import Pusher from 'pusher-js';

const config = window.__MANINJAU_ADMIN_BROWSER_NOTIFICATIONS__ ?? {};

window.Pusher = Pusher;

const notificationChannelName = config.adminId ? `private-App.Models.User.${config.adminId}` : '';
const permissionStorageKey = 'maninjau-admin-browser-notification-permission';
const eventNames = [
    'Illuminate\\Notifications\\Events\\BroadcastNotificationCreated',
    '.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated',
    'system.notification',
];

function normalize(value, fallback = '') {
    return String(value ?? fallback).trim();
}

function readNotificationPayload(payload) {
    if (!payload || typeof payload !== 'object') {
        return {};
    }

    const nestedData = payload.data && typeof payload.data === 'object' ? payload.data : {};
    const merged = {
        ...payload,
        ...nestedData,
    };

    merged.meta = merged.meta && typeof merged.meta === 'object' ? merged.meta : {};

    return merged;
}

function isRelevantNotification(payload) {
    const meta = payload.meta && typeof payload.meta === 'object' ? payload.meta : {};
    const title = normalize(payload.title).toLowerCase();
    const message = normalize(payload.message || payload.body).toLowerCase();

    if (meta.conversation_id || meta.service_request_id) {
        return true;
    }

    return (
        title.includes('pengajuan') ||
        title.includes('pesan baru dari user') ||
        message.includes('pengajuan') ||
        message.includes('pesan baru dari user')
    );
}

function formatIdentityLine(payload) {
    const meta = payload.meta && typeof payload.meta === 'object' ? payload.meta : {};
    const userName = normalize(meta.user_name || meta.sender_name || payload.notifiable_name || payload.admin_name);
    const requestCode = normalize(meta.service_request_code || meta.transaction_code_label || meta.request_code);

    if (userName && requestCode) {
        return `${userName} • ${requestCode}`;
    }

    if (userName) {
        return userName;
    }

    if (requestCode) {
        return requestCode;
    }

    return '';
}

function getNotificationIcon() {
    if (typeof document === 'undefined') {
        return '';
    }

    const favicon = document.querySelector('link[rel="icon"]');
    return favicon?.href || config.notificationIcon || '';
}

function openNotificationTarget(url) {
    if (!url) {
        return;
    }

    try {
        const targetUrl = new URL(url, window.location.origin).toString();
        window.location.href = targetUrl;
    } catch (error) {
        console.warn('Gagal membuka target notifikasi browser:', error);
    }
}

function showBrowserNotification(payload) {
    if (!('Notification' in window)) {
        return;
    }

    if (Notification.permission !== 'granted') {
        return;
    }

    const title = normalize(payload.title, 'Notifikasi admin');
    const message = normalize(payload.message || payload.body, 'Ada pembaruan baru.');
    const identityLine = formatIdentityLine(payload);
    const body = identityLine ? `${identityLine}\n${message}` : message;
    const url = normalize(payload.url || payload.link || '', '');
    const notification = new Notification(title, {
        body,
        icon: getNotificationIcon() || undefined,
        badge: getNotificationIcon() || undefined,
        data: { url },
    });

    notification.onclick = () => {
        window.focus();
        openNotificationTarget(url);
        notification.close();
    };
}

async function ensureNotificationPermission() {
    if (!('Notification' in window)) {
        return false;
    }

    if (!window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
        return false;
    }

    if (Notification.permission === 'granted') {
        return true;
    }

    if (Notification.permission === 'denied') {
        return false;
    }

    try {
        if (window.localStorage.getItem(permissionStorageKey) === 'asked') {
            return false;
        }
    } catch (error) {
        // Ignore storage failures.
    }

    try {
        const permission = await Notification.requestPermission();
        try {
            window.localStorage.setItem(permissionStorageKey, 'asked');
        } catch (error) {
            // Ignore storage failures.
        }

        return permission === 'granted';
    } catch (error) {
        console.warn('Gagal meminta izin browser notification:', error);
        return false;
    }
}

function createPusher() {
    if (!config.reverbKey || !notificationChannelName) {
        return null;
    }

    const reverbHost = normalize(config.reverbHost || window.location.hostname, window.location.hostname);
    const reverbScheme = normalize(config.reverbScheme || 'http', 'http').toLowerCase();
    const reverbPort = Number(config.reverbPort || 8080) || 8080;
    const reverbTlsPort = Number(config.reverbTlsPort || 443) || 443;
    const pusherCluster = normalize(config.pusherCluster || 'mt1', 'mt1') || 'mt1';
    const forceTLS = reverbScheme === 'https' || reverbScheme === 'wss' || window.location.protocol === 'https:';
    const authEndpoint = normalize(config.authEndpoint || `${window.location.origin}/broadcasting/auth`);
    const csrfToken = normalize(config.csrfToken || '');

    const pusher = new Pusher(config.reverbKey, {
        authEndpoint,
        authorizer: (channel) => ({
            authorize: async (socketId, callback) => {
                try {
                    const response = await fetch(authEndpoint, {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            socket_id: socketId,
                            channel_name: channel.name,
                        }),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        console.warn('Auth browser notification gagal:', response.status, data);
                        callback(true, data);
                        return;
                    }

                    callback(false, data);
                } catch (error) {
                    console.warn('Auth browser notification error:', error);
                    callback(true, error);
                }
            },
        }),
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
        cluster: pusherCluster,
        forceTLS,
        wsHost: reverbHost,
        wsPort: forceTLS ? reverbTlsPort : reverbPort,
        wssPort: forceTLS ? reverbTlsPort : reverbPort,
    });

    pusher.connection.bind('connected', () => {
        console.log('Browser notification realtime connected');
    });

    pusher.connection.bind('error', (error) => {
        console.warn('Browser notification realtime error:', error);
    });

    const channel = pusher.subscribe(notificationChannelName);

    channel.bind('pusher:subscription_succeeded', () => {
        console.log(`Browser notification subscribed to ${notificationChannelName}`);
    });

    channel.bind('pusher:subscription_error', (status) => {
        console.warn('Browser notification subscription error:', status);
    });

    const handleNotification = (payload) => {
        const notification = readNotificationPayload(payload);

        if (!isRelevantNotification(notification)) {
            return;
        }

        showBrowserNotification(notification);
    };

    for (const eventName of eventNames) {
        channel.bind(eventName, handleNotification);
    }

    return pusher;
}

document.addEventListener('DOMContentLoaded', async () => {
    await ensureNotificationPermission();
    createPusher();
});

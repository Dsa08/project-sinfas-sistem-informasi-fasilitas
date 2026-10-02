self.addEventListener('push', (event) => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (_) {
        data = { body: event.data ? event.data.text() : '' };
    }

    const title = data.title || 'Notifikasi SINFAS';
    const options = {
        body: data.body || 'Ada informasi baru di SINFAS.',
        icon: '/assets/logo-sinfas.png',
        badge: '/assets/logo-sinfas.png',
        data: { url: data.url || '/dashboard' },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const target = new URL(event.notification.data?.url || '/dashboard', self.location.origin);
    if (target.origin !== self.location.origin) return;

    event.waitUntil(clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windows) => {
        const existing = windows.find((client) => new URL(client.url).origin === self.location.origin);
        if (existing) {
            existing.navigate(target.href);
            return existing.focus();
        }
        return clients.openWindow(target.href);
    }));
});

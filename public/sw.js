/**
 * Service Worker SINFAS — PWA & Web Push Notification Handler
 * File: public/sw.js
 *
 * Mengelola siklus penerimaan pemberitahuan Web Push di latar belakang (background)
 * dan menangani interaksi klik notifikasi untuk navigasi jendela browser secara responsif.
 */

/**
 * Event Listener: 'push'
 *
 * Menerima payload notifikasi terenkripsi yang diteruskan dari Push Service vendor peramban.
 * Membaca data JSON (judul, badan pesan, icon, dan URL tujuan) lalu menampilkan notifikasi native ke OS pengguna.
 *
 * @param {PushEvent} event Objek push event yang memuat data kiriman server.
 */
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

    // Tahan worker aktif hingga notifikasi berhasil disajikan ke layar pengguna
    event.waitUntil(self.registration.showNotification(title, options));
});

/**
 * Event Listener: 'notificationclick'
 *
 * Menangani aksi saat pengguna mengklik pop-up banner notifikasi.
 * Menutup notifikasi dan memfokuskan tab SINFAS yang sedang terbuka (atau membuka window baru jika belum ada).
 *
 * @param {NotificationEvent} event Objek interaksi klik notifikasi.
 */
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const target = new URL(event.notification.data?.url || '/dashboard', self.location.origin);
    if (target.origin !== self.location.origin) return;

    event.waitUntil(clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windows) => {
        // Cari tab SINFAS yang telah aktif untuk diarahkan ke halaman tujuan
        const existing = windows.find((client) => new URL(client.url).origin === self.location.origin);
        if (existing) {
            existing.navigate(target.href);
            return existing.focus();
        }
        // Jika belum ada tab yang terbuka, luncurkan jendela tab baru
        return clients.openWindow(target.href);
    }));
});

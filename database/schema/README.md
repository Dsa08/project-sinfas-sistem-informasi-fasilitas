# Panduan skema ERD2

- `erd_gambar_2.sql` adalah skema bersih untuk membuat database prototype terpisah.
- `erd2_additive_migration.sql` adalah catatan SQL satu kali untuk konversi database lokal lama; jangan dijalankan ulang.
- `database/migrations/2026_10_10_000001_add_erd2_account_relations.php` adalah jalur konversi relasi aditif. `2026_10_10_000002_add_must_change_password_to_akun.php` menambahkan kontrol sandi sementara. Kolom legacy dipertahankan dan tautan baru di-backfill.
- `staff_sarana.nip` mengacu ke `pegawai.nip`; `staff_sarana.id_akun` mengacu ke akun dengan role admin sarana.
- `peminjaman.id_peminjaman` saat ini merupakan alias unik `kode_pinjam` agar pengembalian dan riwayat lama tetap terhubung.

## Konfigurasi dan keamanan produksi

- Set `APP_ENV=production` dan `APP_DEBUG=false` pada hosting. Jangan menyalin `.env` lokal ke hosting atau memasukkannya ke Git.
- Kredensial database yang pernah dibagikan di percakapan perlu diganti di panel hosting jika masih aktif; sesudahnya perbarui `.env` hosting dan bersihkan cache konfigurasi Laravel.
- Sebelum migrasi hosting, buat backup database yang bisa dipulihkan. Migrasi ERD2 ini sengaja tidak dapat dibatalkan dengan `migrate:rollback` karena dapat menghapus relasi dan data baru.
- Setelah deployment, jalankan pemeriksaan migrasi dan alur akun/peminjaman pada database staging terlebih dahulu.

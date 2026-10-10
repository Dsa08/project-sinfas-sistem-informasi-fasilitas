# Pedoman perubahan kode SINFAS

Gunakan pedoman ini untuk perubahan baru dan saat merapikan area yang disentuh:

- **Komentar:** jelaskan alasan, batasan, atau invariant yang tidak tampak dari kode. Hindari komentar yang hanya mengulang nama fungsi atau operasi berikutnya. Tambahkan PHPDoc pada service dan API publik yang kontraknya tidak sederhana.
- **KISS/YAGNI:** pilih solusi paling kecil yang memenuhi alur nyata; jangan menambah abstraksi, konfigurasi, atau tabel tanpa kebutuhan yang jelas.
- **DRY:** satukan aturan yang benar-benar sama (misalnya tujuan halaman per role), tetapi jangan menyatukan alur yang kebetulan mirip namun punya aturan berbeda.
- **Keterbacaan/kejutan minimal:** gunakan nama yang menyatakan maksud, validasi input di batas aplikasi, dan pertahankan perilaku yang diharapkan dari route serta model.
- **Boy Scout Rule:** perbaiki masalah kecil yang berdekatan dengan perubahan, sambil menjaga diff tetap relevan dan mudah ditinjau.
- **SOLID:** pisahkan aturan bisnis dari HTTP controller jika ada manfaat nyata untuk pemakaian ulang atau pengujian. Hindari membuat service hanya sebagai pembungkus satu baris.
- **Database:** perubahan skema harus punya migration Laravel, pembaruan skema prototype yang sesuai, dan catatan dampak/rollback. Uji pada database terisolasi sebelum produksi.
- **Verifikasi:** jalankan tes terkait, Pint pada file yang disentuh, pemeriksaan sintaks, dan `git diff --check`. Jangan menyatakan tes alur database berhasil bila hanya pemeriksaan statis yang dilakukan.

Komentar baru dalam bahasa Indonesia mengikuti bahasa dominan dokumentasi proyek. Beri komentar pada keputusan keamanan dan integritas data; jangan beri komentar pada setiap baris.

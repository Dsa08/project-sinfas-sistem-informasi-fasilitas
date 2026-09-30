<p align="center">
  <img src="public/assets/logo-sinfas.png" alt="SINFAS Logo" width="130">
</p>

<h1 align="center">SINFAS &mdash; Sistem Informasi Fasilitas</h1>

<p align="center">
  <strong>Aplikasi Peminjaman Barang & Sarana Prasarana Sekolah Berbasis Web</strong><br>
  <em>Khusus Lingkungan SMK Negeri 11 Bandung</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Status-Active%20Development-0ea5e9?style=for-the-badge&logo=git&logoColor=white" alt="Status">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Vite-8.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
  <img src="https://img.shields.io/badge/MySQL-8.x-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="License">
</p>

---

## 📋 Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Fitur Utama](#-fitur-utama)
  - [1. Pengguna (Siswa / Guru)](#1-pengguna-siswa--guru)
  - [2. Admin Sarana (Sarpras)](#2-admin-sarana-sarpras)
  - [3. Admin Sistem (Superadmin)](#3-admin-sistem-superadmin)
- [Alur Kerja Sistem](#-alur-kerja-sistem)
- [Tech Stack](#-tech-stack)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi & Panduan Menjalankan](#-instalasi--panduan-menjalankan)
- [Struktur Direktori](#-struktur-direktori)
- [Alur Branch Git](#-alur-branch-git)
- [Lisensi](#-lisensi)

---

## 🏫 Tentang Proyek

**SINFAS (Sistem Informasi Fasilitas)** adalah platform web terpadu yang dirancang untuk mempermudah dan mendigitalisasi proses peminjaman sarana prasarana sekolah di **SMK Negeri 11 Bandung**. 

Melalui sistem ini, siswa dan pengajar dapat mengecek ketersediaan stok peralatan (elektronik, alat laboratorium, sarana olahraga, proyektor, kabel, dsb.) secara realtime, mengajukan pinjaman secara online tanpa antre manual di ruang Sarpras, serta melacak status persetujuan secara transparan.

---

## ✨ Fitur Utama

### 1. Pengguna (Siswa / Guru)
- **Portal Publik (Landing Page)**: Informasi layanan, alur 3 langkah peminjaman, dan tautan bantuan terintegrasi.
- **Katalog Fasilitas Realtime**: Menampilkan daftar alat sarana dengan badge stok dinamis (Tersedia, Terbatas, Habis) dan filter kategori.
- **Formulir Pengajuan Cepat**: Pengajuan peminjaman barang dengan input tanggal mulai, tanggal selesai, ruangan/lokasi pemakaian, serta keperluan peminjaman.
- **Tracking Status Peminjaman**: Pelacakan status permohonan secara realtime (*Menunggu Verifikasi*, *Disetujui*, *Ditolak*, *Sedang Dipinjam*, *Selesai*).
- **Alur Pengembalian Mandiri**: Pengajuan pengembalian barang secara online dengan unggah bukti foto/video kondisi sarana setelah pemakaian.

### 2. Admin Sarana (Sarpras)
- **Dashboard Ringkasan Operasional**: Statistik barang aktif, total pinjaman berjalan, dan antrean verifikasi mendesak.
- **Master Data Barang & Kategori**: Manajemen inventaris sarana (tambah, edit, perbarui stok, serta upload foto representasi barang).
- **Verifikasi Permohonan (Approval System)**: Persetujuan atau penolakan pengajuan peminjaman siswa disertai alasan penolakan.
- **Validasi Pengembalian**: Konfirmasi penerimaan fisik barang dan verifikasi bukti foto kondisi barang saat kembali.
- **Laporan & Riwayat Peminjaman**: Ekspor data dan pencatatan riwayat peminjaman untuk kebutuhan rekap bulanan/tahunan Sarpras.

### 3. Admin Sistem (Superadmin)
- **Manajemen Akun Pengguna**: Pengelolaan kredensial (NIS/NIP/Username) untuk siswa, staf, dan admin.
- **Audit & Monitoring**: Pemantauan log aktivitas sistem dan status sesi login.
- **Pengaturan Konfigurasi Sistem**: Pengaturan profil instansi sekolah dan parameter global sistem informasi.

---

## 🔄 Alur Kerja Sistem

```
┌────────────────┐      Ajukan Online      ┌─────────────────────────┐
│     Siswa      │ ──────────────────────> │  Antrean Verifikasi     │
│ (Pilih Barang) │                         │  (Admin Sarpras)        │
└────────────────┘                         └────────────┬────────────┘
        ▲                                               │
        │ Notifikasi Status                             │ Setujui / Tolak
        │                                               ▼
┌───────┴────────┐     Ambil Barang        ┌─────────────────────────┐
│ Disetujui      │ <────────────────────── │ Konfirmasi Approval     │
└───────┬────────┘                         └─────────────────────────┘
        │
        │ Gunakan & Ajukan Pengembalian (+ Bukti Foto/Video)
        ▼
┌────────────────┐     Pengecekan Fisik    ┌─────────────────────────┐
│ Form Kembali   │ ──────────────────────> │ Selesai / Arsip Riwayat │
└────────────────┘                         └─────────────────────────┘
```

---

## 🛠️ Tech Stack

| Komponen | Teknologi | Keterangan |
|----------|-----------|------------|
| **Backend Framework** | [Laravel 12.x](https://laravel.com) | Arsitektur MVC, Eloquent ORM, Middleware Auth, Blade Engine |
| **Frontend Styling** | [CSS Modules](resources/css/modules) & [Tailwind CSS v4](https://tailwindcss.com) | Desain modular modern, responsif, dan performa tinggi |
| **Asset Bundler** | [Vite 8.x](https://vitejs.dev) | Hot Module Replacement (HMR) dan build pipeline kilat |
| **Database** | [MySQL 8.x](https://www.mysql.com) | Relasi tabel ACID, Foreign Key Constraints & Triggers |
| **Icons & Typography** | Inter, Poppins, Blade Icons | Tipografi clean dan keterbacaan tinggi |

---

## 💻 Persyaratan Sistem

Pastikan lingkungan lokal telah memenuhi persyaratan software berikut:

| Software | Versi Rekomendasi | Tautan Download |
|----------|-------------------|-----------------|
| **PHP** | 8.2 atau lebih baru | [php.net/downloads](https://www.php.net/downloads) |
| **Composer** | 2.x | [getcomposer.org](https://getcomposer.org/download/) |
| **Node.js** | 20.x atau 22.x LTS | [nodejs.org](https://nodejs.org/) |
| **npm** | 10.x atau lebih baru | Terbawa otomatis bersama Node.js |
| **MySQL** | 8.0+ atau MariaDB 10.4+ | [Laragon](https://laragon.org/) / [XAMPP](https://www.apachefriends.org/) |
| **Git** | 2.x | [git-scm.com](https://git-scm.com/downloads) |

---

## 🚀 Instalasi & Panduan Menjalankan

Ikuti langkah-langkah di bawah untuk menjalankan SINFAS di komputer lokal:

### 1. Kloning Repositori
```bash
git clone https://github.com/Dsa08/project-sinfas-sistem-informasi-fasilitas.git
cd project-sinfas-sistem-informasi-fasilitas
```

### 2. Instal Dependensi Backend & Frontend
```bash
# Instal paket PHP via Composer
composer install

# Instal dependensi JavaScript/CSS via NPM
npm install
```

### 3. Konfigurasi Environment File
Salin template konfigurasi `.env.example` menjadi `.env`, lalu generate encryption key:
```bash
cp .env.example .env
php artisan key:generate
```

Buka file `.env` dan atur kredensial koneksi basis data:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sinfas
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Setup Database & Jalankan Migrasi
Buat database bernama `sinfas` di MySQL Anda, kemudian jalankan migrasi dan seeder data awal:
```bash
php artisan migrate --seed
```

### 5. Kompilasi Aset Frontend
```bash
npm run build
```

### 6. Jalankan Server Pengembangan
Buka **dua terminal** terpisah:

- **Terminal 1 (Laravel Backend Server):**
  ```bash
  php artisan serve
  ```

- **Terminal 2 (Vite Frontend Development):**
  ```bash
  npm run dev
  ```

Buka peramban dan akses alamat: **[http://localhost:8000](http://localhost:8000)**

---

## 📁 Struktur Direktori

```text
project-sinfas-sistem-informasi-fasilitas/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Controller (User, Admin Sarana, Admin Sistem)
│   │   └── Middleware/        # Middleware autentikasi & hak akses role
│   └── Models/                # Eloquent Models (Barang, Peminjaman, Kategori, User)
├── database/
│   ├── migrations/            # Skema tabel basis data
│   └── seeders/               # Data awal (kategori, barang default, akun master)
├── public/
│   ├── assets/                # Asset statis publik (logo SINFAS, latar belakang)
│   └── build/                 # Hasil build Vite (CSS & JS terkompilasi)
├── resources/
│   ├── css/
│   │   ├── app.css            # Entry-point CSS
│   │   └── modules/           # Modul CSS terpisah (landing, admin, dashboard, dsb.)
│   ├── js/                    # Skrip logika frontend
│   └── views/
│       ├── admin/             # Tampilan panel admin sarana & admin sistem
│       ├── auth/              # Halaman login multi-identitas
│       ├── layouts/           # Template layout induk (app, landing, auth, admin)
│       └── user/              # Dashboard katalog siswa, loan request, profil
└── routes/
    ├── web.php                # Rute aplikasi web
    └── auth.php               # Rute autentikasi
```

---

## 🌿 Alur Branch Git

Repositori ini menggunakan strategi percabangan terstruktur:

| Branch | Fungsi & Penggunaan |
|--------|---------------------|
| `master` | Branch utama stabil (*production ready* & siap dideploy ke server hosting) |
| `frontend` | Khusus pengembangan antarmuka, Blade template, modul CSS, dan interaktivitas UI |
| `backend` | Khusus logika bisnis Controller, validasi, Model Eloquent, dan integrasi database |

---

## 📄 Lisensi

Proyek ini dikembangkan untuk kebutuhan internal sekolah dan didistribusikan di bawah lisensi [MIT License](LICENSE).

<p align="center">
  Dikembangkan dengan dedikasi untuk <strong>SMK Negeri 11 Bandung</strong> &bull; SINFAS &copy; 2026
</p>

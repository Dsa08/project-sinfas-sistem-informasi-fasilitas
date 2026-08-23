# SINFAS - Sistem Informasi Fasilitas

Aplikasi web peminjaman alat dan sarana secara online, dibangun dengan Laravel 12 dan Tailwind CSS v4.

---

## Persyaratan Sistem (System Requirements)

Pastikan perangkat lunak berikut sudah terinstal sebelum menjalankan proyek:

| Software | Versi Minimum | Download |
|----------|---------------|----------|
| **PHP** | 8.2+ | [php.net](https://www.php.net/downloads) |
| **Composer** | 2.x | [getcomposer.org](https://getcomposer.org/download/) |
| **Node.js** | 20+ | [nodejs.org](https://nodejs.org/) |
| **npm** | 10+ | Terinstal otomatis bersama Node.js |
| **MySQL** | 8.0+ | [mysql.com](https://dev.mysql.com/downloads/) atau gunakan [XAMPP](https://www.apachefriends.org/) / [Laragon](https://laragon.org/) |
| **Git** | 2.x | [git-scm.com](https://git-scm.com/downloads) |

---

## Instalasi & Setup

### 1. Clone Repositori

```bash
git clone https://github.com/Dsa08/project-sinfas-sistem-informasi-fasilitas.git
cd project-sinfas-sistem-informasi-fasilitas
```

### 2. Install Dependensi PHP (Composer)

```bash
composer install
```

### 3. Install Dependensi Frontend (NPM)

```bash
npm install
```

### 4. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Lalu buka file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sinfas
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Buat Database

Buat database baru bernama `sinfas` di MySQL (melalui phpMyAdmin, MySQL Workbench, atau terminal):

```sql
CREATE DATABASE sinfas;
```

### 6. Jalankan Migrasi Database

```bash
php artisan migrate
```

### 7. Build Aset Frontend

```bash
npm run build
```

---

## Menjalankan Aplikasi

Buka **dua terminal** secara bersamaan:

**Terminal 1 — Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 — Vite Dev Server (untuk hot reload CSS/JS):**
```bash
npm run dev
```

Akses aplikasi di browser: [http://localhost:8000](http://localhost:8000)

---

## Tech Stack

| Teknologi | Versi | Keterangan |
|-----------|-------|------------|
| Laravel | 12.x | Framework PHP backend |
| Tailwind CSS | 4.x | Utility-first CSS framework |
| Vite | 8.x | Bundler & dev server frontend |
| MySQL | 8.x | Database relasional |

---

## Struktur Branch

| Branch | Fungsi |
|--------|--------|
| `master` | Kode stabil & siap deploy |
| `frontend` | Pengerjaan semua halaman tampilan (Blade + Tailwind) |
| `backend` | Pengerjaan logika bisnis (Controller, Model, Middleware) |

---

## Lisensi

Proyek ini menggunakan lisensi [MIT](LICENSE).

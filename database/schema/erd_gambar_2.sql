-- Prototype schema for ERD image 2.
-- Apply only to the isolated sinfas_baru database; this is not a Laravel migration.
-- Assumptions: profile tables map one-to-one to akun, penyetujuan.nip points to
-- staff_sarana.nip, and each peminjaman has at most one pengembalian.

CREATE TABLE akun (
    id_akun BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    nomor_kontak VARCHAR(20) NULL,
    foto VARCHAR(255) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    must_change_password BOOLEAN NOT NULL DEFAULT FALSE,
    role ENUM('siswa', 'pegawai', 'admin_sarana', 'admin_sistem') NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id_akun),
    UNIQUE KEY akun_username_unique (username),
    UNIQUE KEY akun_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pegawai (
    nip VARCHAR(20) NOT NULL,
    id_akun BIGINT UNSIGNED NULL,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    jabatan VARCHAR(100) NULL,
    no_hp VARCHAR(20) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (nip),
    UNIQUE KEY pegawai_id_akun_unique (id_akun),
    CONSTRAINT pegawai_akun_fk FOREIGN KEY (id_akun) REFERENCES akun (id_akun)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE staff_sarana (
    nip VARCHAR(20) NOT NULL,
    id_akun BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20) NULL,
    PRIMARY KEY (nip),
    UNIQUE KEY staff_sarana_id_akun_unique (id_akun),
    CONSTRAINT staff_sarana_akun_fk FOREIGN KEY (id_akun) REFERENCES akun (id_akun),
    CONSTRAINT staff_sarana_pegawai_fk FOREIGN KEY (nip) REFERENCES pegawai (nip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE siswa (
    nis VARCHAR(20) NOT NULL,
    id_akun BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    kelas VARCHAR(50) NULL,
    no_hp VARCHAR(20) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (nis),
    UNIQUE KEY siswa_id_akun_unique (id_akun),
    CONSTRAINT siswa_akun_fk FOREIGN KEY (id_akun) REFERENCES akun (id_akun)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kategori (
    id_kategori BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama_kategori VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id_kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE barang (
    kode_barang VARCHAR(30) NOT NULL,
    id_kategori BIGINT UNSIGNED NOT NULL,
    nama_barang VARCHAR(255) NOT NULL,
    merk_model VARCHAR(255) NULL,
    no_seri_pabrik VARCHAR(255) NULL,
    ukuran_dimensi VARCHAR(255) NULL,
    bahan VARCHAR(255) NULL,
    tahun_pembelian YEAR NULL,
    jumlah_baik INT UNSIGNED NOT NULL DEFAULT 0,
    jumlah_kurang_baik INT UNSIGNED NOT NULL DEFAULT 0,
    jumlah_rusak_berat INT UNSIGNED NOT NULL DEFAULT 0,
    keterangan TEXT NULL,
    foto VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (kode_barang),
    KEY barang_id_kategori_index (id_kategori),
    CONSTRAINT barang_kategori_fk FOREIGN KEY (id_kategori) REFERENCES kategori (id_kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE peminjaman (
    id_peminjaman VARCHAR(30) NOT NULL,
    id_akun BIGINT UNSIGNED NOT NULL,
    kode_barang VARCHAR(30) NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    keterangan_penggunaan TEXT NULL,
    lokasi_penggunaan VARCHAR(255) NULL,
    status_pengajuan ENUM('menunggu', 'disetujui', 'ditolak') NOT NULL DEFAULT 'menunggu',
    alasan_penolakan TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id_peminjaman),
    KEY peminjaman_id_akun_index (id_akun),
    KEY peminjaman_kode_barang_index (kode_barang),
    CONSTRAINT peminjaman_akun_fk FOREIGN KEY (id_akun) REFERENCES akun (id_akun),
    CONSTRAINT peminjaman_barang_fk FOREIGN KEY (kode_barang) REFERENCES barang (kode_barang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pengembalian (
    kode_kembali VARCHAR(30) NOT NULL,
    kode_pinjam VARCHAR(30) NOT NULL,
    tanggal_kembali DATE NOT NULL,
    kondisi_barang VARCHAR(255) NULL,
    catatan TEXT NULL,
    bukti_foto_video VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (kode_kembali),
    UNIQUE KEY pengembalian_kode_pinjam_unique (kode_pinjam),
    CONSTRAINT pengembalian_peminjaman_fk FOREIGN KEY (kode_pinjam) REFERENCES peminjaman (id_peminjaman)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE penyetujuan (
    id_penyetujuan BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_peminjaman VARCHAR(30) NOT NULL,
    nip VARCHAR(20) NOT NULL,
    status VARCHAR(50) NOT NULL,
    catatan TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_penyetujuan),
    KEY penyetujuan_id_peminjaman_index (id_peminjaman),
    KEY penyetujuan_nip_index (nip),
    CONSTRAINT penyetujuan_peminjaman_fk FOREIGN KEY (id_peminjaman) REFERENCES peminjaman (id_peminjaman),
    CONSTRAINT penyetujuan_staff_sarana_fk FOREIGN KEY (nip) REFERENCES staff_sarana (nip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifikasi (
    id_notifikasi BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_akun BIGINT UNSIGNED NOT NULL,
    pesan TEXT NOT NULL,
    status_baca BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id_notifikasi),
    KEY notifikasi_id_akun_index (id_akun),
    CONSTRAINT notifikasi_akun_fk FOREIGN KEY (id_akun) REFERENCES akun (id_akun)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

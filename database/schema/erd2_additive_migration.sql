-- One-time SQL record of the local ERD2 conversion, already applied to sinfas.
-- Do not rerun: ALTER/CREATE statements are intentionally not idempotent.
-- New deployments should use database/migrations/2026_10_10_000001_add_erd2_account_relations.php.
-- Keeps all legacy tables and columns.
-- Unlike the clean prototype ERD, the additive schema keeps nullable links during backfill;
-- the Laravel migration is the canonical repeatable deployment path.

-- Added by 2026_10_10_000002_add_must_change_password_to_akun.php.
ALTER TABLE akun
    ADD COLUMN must_change_password BOOLEAN NOT NULL DEFAULT FALSE AFTER is_active;

ALTER TABLE akun
    MODIFY role ENUM('siswa', 'pegawai', 'admin_sarana', 'admin_sistem') NOT NULL;

ALTER TABLE siswa
    ADD COLUMN id_akun BIGINT UNSIGNED NULL AFTER nis,
    ADD COLUMN kelas VARCHAR(50) NULL AFTER email,
    ADD UNIQUE KEY siswa_id_akun_unique (id_akun),
    ADD CONSTRAINT siswa_akun_fk FOREIGN KEY (id_akun) REFERENCES akun (id_akun);

ALTER TABLE pegawai
    ADD COLUMN id_akun BIGINT UNSIGNED NULL AFTER nip,
    ADD COLUMN jabatan VARCHAR(100) NULL AFTER email,
    ADD UNIQUE KEY pegawai_id_akun_unique (id_akun),
    ADD CONSTRAINT pegawai_akun_fk FOREIGN KEY (id_akun) REFERENCES akun (id_akun);

ALTER TABLE peminjaman
    ADD COLUMN id_peminjaman VARCHAR(30) NULL AFTER kode_pinjam,
    ADD COLUMN id_akun BIGINT UNSIGNED NULL AFTER nis;

UPDATE peminjaman SET id_peminjaman = kode_pinjam;
ALTER TABLE peminjaman
    MODIFY id_peminjaman VARCHAR(30) NOT NULL,
    ADD UNIQUE KEY peminjaman_id_peminjaman_unique (id_peminjaman),
    ADD KEY peminjaman_id_akun_index (id_akun),
    ADD CONSTRAINT peminjaman_akun_fk FOREIGN KEY (id_akun) REFERENCES akun (id_akun);

-- NIS becomes optional because staff loans identify the borrower by id_akun.
ALTER TABLE peminjaman MODIFY nis VARCHAR(20) NULL;

-- Keep the old FK to kode_pinjam and add the new ERD2 relation to its alias.
ALTER TABLE pengembalian
    ADD CONSTRAINT pengembalian_peminjaman_erd2_fk
    FOREIGN KEY (kode_pinjam) REFERENCES peminjaman (id_peminjaman);

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

START TRANSACTION;

-- Existing student accounts already carry their NIS; map the profile FK.
UPDATE siswa s
JOIN akun a ON a.nis = s.nis
SET s.id_akun = a.id_akun;

-- Preserve account IDs 4 and 5 as sarana accounts, and use ID 8 as the
-- separate teacher account for the same NIP as account 4.
UPDATE akun
SET username = 'ADMIN198501012010011001', role = 'admin_sarana'
WHERE id_akun = 4;

UPDATE akun
SET username = 'GURU198501012010011001', role = 'pegawai'
WHERE id_akun = 8;

UPDATE akun
SET username = 'ADMIN198802022012022002', role = 'admin_sarana'
WHERE id_akun = 5;

-- Create a separate teacher login for the second existing sarana staff member.
-- It inherits the existing password hash; the user should change it afterward.
INSERT INTO akun
    (nis, nip, nama, nomor_kontak, email, role, username, password, foto,
     is_active, created_at, updated_at)
SELECT NULL, nip, nama, nomor_kontak, NULL, 'pegawai', CONCAT('GURU', nip),
       password, foto, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM akun
WHERE id_akun = 5;

-- Keep the third duplicate account and its notification/history references,
-- but disable it and remove its staff identity link.
UPDATE akun
SET username = 'ARSIP-DUP-11', nip = NULL, is_active = 0
WHERE id_akun = 11;

UPDATE pegawai p
JOIN akun a ON a.nip = p.nip AND a.role = 'pegawai' AND a.is_active = 1
SET p.id_akun = a.id_akun;

INSERT INTO staff_sarana (nip, id_akun, nama, no_hp)
SELECT nip, id_akun, nama, nomor_kontak
FROM akun
WHERE role = 'admin_sarana' AND is_active = 1 AND nip IS NOT NULL;

UPDATE peminjaman p
JOIN akun a ON a.nis = p.nis
SET p.id_akun = a.id_akun;

COMMIT;

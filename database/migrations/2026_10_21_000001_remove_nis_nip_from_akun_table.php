<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menghapus kolom nis dan nip dari tabel akun sesuai arsitektur ERD 2,
     * sehingga relasi siswa, pegawai, dan staff_sarana terhubung sepenuhnya
     * melalui id_akun pada tabel masing-masing profil tanpa meninggalkan
     * kolom bernilai null (ghost data) pada tabel akun.
     */
    public function up(): void
    {
        // 1. Pastikan seluruh profil siswa, pegawai, staff_sarana, dan transaksi peminjaman tersinkronisasi ke id_akun
        if (Schema::hasColumn('akun', 'nis')) {
            DB::statement('UPDATE siswa s JOIN akun a ON a.nis = s.nis SET s.id_akun = a.id_akun WHERE s.id_akun IS NULL');
            DB::statement('UPDATE peminjaman p JOIN akun a ON a.nis = p.nis SET p.id_akun = a.id_akun WHERE p.id_akun IS NULL');
        }

        if (Schema::hasColumn('akun', 'nip')) {
            DB::statement("UPDATE pegawai p JOIN akun a ON a.nip = p.nip AND a.role = 'pegawai' AND a.is_active = 1 SET p.id_akun = a.id_akun WHERE p.id_akun IS NULL");
            DB::statement("INSERT INTO staff_sarana (nip, id_akun, nama, no_hp)
                SELECT a.nip, a.id_akun, a.nama, a.nomor_kontak
                FROM akun a
                JOIN (SELECT nip, MIN(id_akun) AS id_akun FROM akun
                      WHERE role = 'admin_sarana' AND is_active = 1 AND nip IS NOT NULL GROUP BY nip) active_staff
                  ON active_staff.id_akun = a.id_akun
                WHERE NOT EXISTS (SELECT 1 FROM staff_sarana s WHERE s.nip = a.nip)");
        }

        // 2. Lepas foreign key constraint pada akun.nis dan akun.nip jika masih ada
        if ($this->foreignKeyExists('akun', 'akun_nis_foreign')) {
            Schema::table('akun', function (Blueprint $table) {
                $table->dropForeign('akun_nis_foreign');
            });
        }

        if ($this->foreignKeyExists('akun', 'akun_nip_foreign')) {
            Schema::table('akun', function (Blueprint $table) {
                $table->dropForeign('akun_nip_foreign');
            });
        }

        // 3. Drop kolom nis dan nip dari tabel akun
        Schema::table('akun', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('akun', 'nis')) {
                $columnsToDrop[] = 'nis';
            }
            if (Schema::hasColumn('akun', 'nip')) {
                $columnsToDrop[] = 'nip';
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            if (! Schema::hasColumn('akun', 'nis')) {
                $table->string('nis', 20)->nullable()->after('id_akun');
            }
            if (! Schema::hasColumn('akun', 'nip')) {
                $table->string('nip', 20)->nullable()->after('nis');
            }
        });

        // Pulihkan relasi foreign key
        Schema::table('akun', function (Blueprint $table) {
            if (! $this->foreignKeyExists('akun', 'akun_nis_foreign')) {
                $table->foreign('nis')->references('nis')->on('siswa')->nullOnDelete();
            }
            if (! $this->foreignKeyExists('akun', 'akun_nip_foreign')) {
                $table->foreign('nip')->references('nip')->on('pegawai')->nullOnDelete();
            }
        });

        // Salin kembali nilai nis dan nip dari tabel relasi
        DB::statement('UPDATE akun a JOIN siswa s ON s.id_akun = a.id_akun SET a.nis = s.nis WHERE a.nis IS NULL');
        DB::statement('UPDATE akun a JOIN pegawai p ON p.id_akun = a.id_akun SET a.nip = p.nip WHERE a.nip IS NULL');
        DB::statement('UPDATE akun a JOIN staff_sarana ss ON ss.id_akun = a.id_akun SET a.nip = ss.nip WHERE a.nip IS NULL');
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};

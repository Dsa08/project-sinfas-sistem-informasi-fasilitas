<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ERD2 is additive here: keep legacy identifiers so older records and code remain readable.
        DB::statement("ALTER TABLE akun MODIFY role ENUM('siswa', 'pegawai', 'admin_sarana', 'admin_sistem') NOT NULL");

        if (! Schema::hasColumn('siswa', 'id_akun')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->unsignedBigInteger('id_akun')->nullable()->after('nis');
                $table->string('kelas', 50)->nullable()->after('email');
                $table->unique('id_akun', 'siswa_id_akun_unique');
                $table->foreign('id_akun', 'siswa_akun_fk')->references('id_akun')->on('akun');
            });
        }

        if (! Schema::hasColumn('pegawai', 'id_akun')) {
            Schema::table('pegawai', function (Blueprint $table) {
                $table->unsignedBigInteger('id_akun')->nullable()->after('nip');
                $table->string('jabatan', 100)->nullable()->after('email');
                $table->unique('id_akun', 'pegawai_id_akun_unique');
                $table->foreign('id_akun', 'pegawai_akun_fk')->references('id_akun')->on('akun');
            });
        }

        if (! Schema::hasColumn('peminjaman', 'id_peminjaman')) {
            Schema::table('peminjaman', function (Blueprint $table) {
                $table->string('id_peminjaman', 30)->nullable()->after('kode_pinjam');
                $table->unsignedBigInteger('id_akun')->nullable()->after('nis');
                $table->unique('id_peminjaman', 'peminjaman_id_peminjaman_unique');
                $table->index('id_akun', 'peminjaman_id_akun_index');
                $table->foreign('id_akun', 'peminjaman_akun_fk')->references('id_akun')->on('akun');
            });
        }

        // Keep the ERD2 ID as an alias of the established transaction code during transition.
        DB::statement('UPDATE peminjaman SET id_peminjaman = kode_pinjam WHERE id_peminjaman IS NULL');
        DB::statement('ALTER TABLE peminjaman MODIFY id_peminjaman VARCHAR(30) NOT NULL');
        DB::statement('ALTER TABLE peminjaman MODIFY nis VARCHAR(20) NULL');

        if (! $this->foreignKeyExists('pengembalian', 'pengembalian_peminjaman_erd2_fk')) {
            Schema::table('pengembalian', function (Blueprint $table) {
                $table->foreign('kode_pinjam', 'pengembalian_peminjaman_erd2_fk')
                    ->references('id_peminjaman')->on('peminjaman');
            });
        }

        if (! Schema::hasTable('staff_sarana')) {
            Schema::create('staff_sarana', function (Blueprint $table) {
                $table->string('nip', 20)->primary();
                $table->unsignedBigInteger('id_akun')->unique('staff_sarana_id_akun_unique');
                $table->string('nama');
                $table->string('no_hp', 20)->nullable();
                $table->foreign('id_akun', 'staff_sarana_akun_fk')->references('id_akun')->on('akun');
                $table->foreign('nip', 'staff_sarana_pegawai_fk')->references('nip')->on('pegawai');
            });
        }

        if (! Schema::hasTable('penyetujuan')) {
            Schema::create('penyetujuan', function (Blueprint $table) {
                $table->bigIncrements('id_penyetujuan');
                $table->string('id_peminjaman', 30);
                $table->string('nip', 20);
                $table->string('status', 50);
                $table->text('catatan')->nullable();
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->index('id_peminjaman', 'penyetujuan_id_peminjaman_index');
                $table->index('nip', 'penyetujuan_nip_index');
                $table->foreign('id_peminjaman', 'penyetujuan_peminjaman_fk')
                    ->references('id_peminjaman')->on('peminjaman');
                $table->foreign('nip', 'penyetujuan_staff_sarana_fk')
                    ->references('nip')->on('staff_sarana');
            });
        }

        DB::statement('UPDATE siswa s JOIN akun a ON a.nis = s.nis SET s.id_akun = a.id_akun WHERE s.id_akun IS NULL');
        DB::statement("UPDATE pegawai p JOIN akun a ON a.nip = p.nip AND a.role = 'pegawai' AND a.is_active = 1 SET p.id_akun = a.id_akun WHERE p.id_akun IS NULL");
        DB::statement('UPDATE peminjaman p JOIN akun a ON a.nis = p.nis SET p.id_akun = a.id_akun WHERE p.id_akun IS NULL');

        DB::statement("INSERT INTO staff_sarana (nip, id_akun, nama, no_hp)
            SELECT a.nip, a.id_akun, a.nama, a.nomor_kontak
            FROM akun a
            JOIN (SELECT nip, MIN(id_akun) AS id_akun FROM akun
                  WHERE role = 'admin_sarana' AND is_active = 1 AND nip IS NOT NULL GROUP BY nip) active_staff
              ON active_staff.id_akun = a.id_akun
            WHERE NOT EXISTS (SELECT 1 FROM staff_sarana s WHERE s.nip = a.nip)");
    }

    public function down(): void
    {
        // This migration copies and links live records; a blind rollback could orphan that data.
        throw new RuntimeException('ERD2 account migration is intentionally irreversible because it can contain new user and approval data. Restore a verified backup to roll it back.');
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

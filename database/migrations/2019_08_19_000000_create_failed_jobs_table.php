<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Pembuatan Tabel Antrean Pekerjaan yang Mengalami Kegagalan (Failed Jobs).
 *
 * Tujuan:
 * Menampung rekaman pekerjaan asinkron (*queue jobs*) yang gagal dieksekusi oleh queue worker
 * lengkap dengan jejak stack trace exception untuk kebutuhan debugging teknis administrator.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel failed_jobs.
     *
     * Efek Samping:
     * - Membuat tabel baru `failed_jobs` di basis data.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Membatalkan migrasi dan menghapus tabel failed_jobs.
     *
     * Efek Samping:
     * - Menghapus tabel `failed_jobs` dari basis data.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
    }
};

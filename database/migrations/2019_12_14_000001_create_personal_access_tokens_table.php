<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Pembuatan Tabel Pengelolaan Token Akses API (Laravel Sanctum).
 *
 * Tujuan:
 * Mengelola penerbitan, pelacakan masa berlaku, dan pencabutan token otentikasi bearer API
 * untuk pertukaran data stateless atau integrasi klien pihak ketiga.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel personal_access_tokens.
     *
     * Efek Samping:
     * - Membuat tabel baru `personal_access_tokens` di basis data.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Membatalkan migrasi dan menghapus tabel personal_access_tokens.
     *
     * Efek Samping:
     * - Menghapus tabel `personal_access_tokens` dari basis data.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};

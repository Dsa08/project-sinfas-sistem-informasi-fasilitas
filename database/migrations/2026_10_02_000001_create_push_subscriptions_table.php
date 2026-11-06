<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Pembuatan tabel push_subscriptions untuk Web Push Notifications (PWA).
 *
 * Tujuan:
 * Menyimpan data endpoint unik browser dan kunci kriptografi VAPID (`p256dh` & `auth`)
 * untuk penyaluran notifikasi instan langsung ke perangkat siswa atau staf sarana.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel push_subscriptions.
     *
     * Efek Samping:
     * - Membuat tabel baru `push_subscriptions` dengan relasi foreign key ke tabel `akun`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_akun')->constrained('akun', 'id_akun')->cascadeOnDelete();
            $table->text('endpoint');
            $table->char('endpoint_hash', 64)->unique();
            $table->string('public_key', 255);
            $table->string('auth_token', 255);
            $table->string('content_encoding', 30)->nullable();
            $table->timestamps();

            $table->index('id_akun');
        });
    }

    /**
     * Membatalkan migrasi dan menghapus tabel push_subscriptions.
     *
     * Efek Samping:
     * - Menghapus tabel `push_subscriptions` dari basis data.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};

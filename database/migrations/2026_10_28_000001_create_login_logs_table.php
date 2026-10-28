<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabel Audit Log Aktivitas Login (login_logs)
 * 
 * Mencatat seluruh riwayat aktivitas otentikasi (sukses, gagal sandi, akun tidak ditemukan,
 * akun dinonaktifkan, dan pemblokiran rate limit) beserta alamat IP dan user agent perangkat.
 * Berguna untuk kepatuhan audit keamanan sekolah dan investigasi akses tanpa izin.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_akun')->nullable();
            $table->string('identifier', 100)->comment('Input identifier yang dimasukkan pengguna (username/NIS/NIP/email)');
            $table->string('ip_address', 45)->nullable()->comment('Alamat IP klien (IPv4 / IPv6)');
            $table->text('user_agent')->nullable()->comment('Data browser dan sistem operasi perangkat klien');
            $table->enum('status', [
                'sukses',
                'gagal_password',
                'gagal_akun_tidak_ditemukan',
                'gagal_nonaktif',
                'terkunci_rate_limit',
            ])->default('sukses')->comment('Status hasil autentikasi');
            $table->string('keterangan', 255)->nullable()->comment('Catatan atau alasan hasil autentikasi');
            $table->timestamp('login_at')->useCurrent()->comment('Waktu percobaan login');

            // Relasi foreign key ke tabel akun jika akun terdaftar
            $table->foreign('id_akun')
                ->references('id_akun')
                ->on('akun')
                ->onDelete('cascade');

            // Indeks performa query pencarian log
            $table->index(['id_akun', 'login_at'], 'idx_login_logs_akun_waktu');
            $table->index(['status', 'login_at'], 'idx_login_logs_status_waktu');
            $table->index('login_at', 'idx_login_logs_waktu');
            $table->index('ip_address', 'idx_login_logs_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};

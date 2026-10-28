<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi: Menambahkan Kolom current_session_id pada Tabel akun.
 * 
 * Bertujuan untuk menerapkan fitur Single Session Lock (Satu Akun Satu Perangkat).
 * Saat pengguna login di perangkat baru, session ID aktif akan diperbarui di kolom ini,
 * sehingga sesi pengguna pada perangkat atau peramban sebelumnya otomatis tidak valid (ter-logout).
 */
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            // Menyimpan ID sesi aktif Laravel (panjang string session Laravel umumnya 40 karakter, disiapkan 255)
            $table->string('current_session_id', 255)
                ->nullable()
                ->after('must_change_password')
                ->comment('ID session aktif untuk mencegah penggunaan akun bersamaan pada banyak perangkat');
        });
    }

    /**
     * Kembalikan migrasi (rollback).
     */
    public function down(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            $table->dropColumn('current_session_id');
        });
    }
};

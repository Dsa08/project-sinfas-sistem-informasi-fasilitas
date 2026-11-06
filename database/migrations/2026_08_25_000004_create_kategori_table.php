<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi Basis Data: Pembuatan Tabel Master Kategori Sarana.
 *
 * Tujuan:
 * Mengelompokkan jenis inventaris sarana dan prasarana sekolah (misal: Audio & Sound System, Proyektor, dll).
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel kategori.
     *
     * Efek Samping:
     * - Membuat tabel baru `kategori` dengan primary key `id_kategori`.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('kategori', function (Blueprint $table) {
            $table->id('id_kategori');
            $table->string('nama_kategori');
            $table->timestamps();
        });
    }

    /**
     * Membatalkan migrasi dan menghapus tabel kategori.
     *
     * Efek Samping:
     * - Menghapus tabel `kategori` dari basis data.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori');
    }
};

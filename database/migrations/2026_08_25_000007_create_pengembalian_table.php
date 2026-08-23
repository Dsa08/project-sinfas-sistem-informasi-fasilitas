<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengembalian', function (Blueprint $table) {
            $table->string('kode_kembali', 30)->primary();
            // unique() -> menegakkan relasi 1 : 0..1 (satu peminjaman maksimal satu pengembalian,
            // dan baris ini baru dibuat setelah barang benar-benar dikembalikan, bukan mandatory sejak awal)
            $table->string('kode_pinjam', 30)->unique();
            $table->date('tanggal_kembali');
            $table->string('kondisi_barang')->nullable();
            $table->string('bukti_foto_video')->nullable();
            $table->timestamps();

            $table->foreign('kode_pinjam')->references('kode_pinjam')->on('peminjaman');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengembalian');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->string('kode_pinjam', 30)->primary();
            $table->string('nis', 20);
            $table->string('kode_barang', 30);
            $table->date('tanggal_pinjam');
            $table->text('keterangan_penggunaan')->nullable();
            $table->enum('status_pengajuan', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->timestamps();

            $table->foreign('nis')->references('nis')->on('siswa');
            $table->foreign('kode_barang')->references('kode_barang')->on('barang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};

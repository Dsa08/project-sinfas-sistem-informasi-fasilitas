<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->string('kode_barang', 30)->primary();
            $table->foreignId('id_kategori')->constrained('kategori', 'id_kategori');
            $table->string('nama_barang');
            $table->string('merk_model')->nullable();
            $table->string('no_seri_pabrik')->nullable();
            $table->string('ukuran_dimensi')->nullable();
            $table->string('bahan')->nullable();
            $table->year('tahun_pembelian')->nullable();
            $table->unsignedInteger('jumlah_baik')->default(0);
            $table->unsignedInteger('jumlah_kurang_baik')->default(0);
            $table->unsignedInteger('jumlah_rusak_berat')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};

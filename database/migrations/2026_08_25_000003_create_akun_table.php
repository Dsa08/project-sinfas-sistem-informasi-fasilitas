<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akun', function (Blueprint $table) {
            $table->id('id_akun');
            $table->string('nis', 20)->nullable();
            $table->string('nip', 20)->nullable();
            $table->string('nama');
            $table->string('nomor_kontak', 20)->nullable();
            $table->enum('role', ['siswa', 'admin_sarana', 'admin_sistem']);
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamps();

            $table->foreign('nis')->references('nis')->on('siswa')->nullOnDelete();
            $table->foreign('nip')->references('nip')->on('pegawai')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akun');
    }
};

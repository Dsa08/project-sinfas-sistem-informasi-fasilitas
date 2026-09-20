<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->string('tipe', 50)->default('info_sistem')->after('id_akun');
            $table->string('judul', 255)->after('tipe');
            $table->string('kode_pinjam', 30)->nullable()->after('pesan');
            $table->json('data_tambahan')->nullable()->after('kode_pinjam');

            $table->foreign('kode_pinjam')
                  ->references('kode_pinjam')
                  ->on('peminjaman')
                  ->nullOnDelete();
                  
            $table->index(['id_akun', 'status_baca']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->dropForeign(['kode_pinjam']);
            $table->dropIndex(['id_akun', 'status_baca']);
            $table->dropColumn(['tipe', 'judul', 'kode_pinjam', 'data_tambahan']);
        });
    }
};

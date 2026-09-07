<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('nomor_kontak');
            $table->string('foto')->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('foto');
        });
    }

    public function down(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            $table->dropColumn(['email', 'foto', 'is_active']);
        });
    }
};

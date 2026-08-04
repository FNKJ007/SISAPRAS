<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // NIP = Nomor Induk Pegawai (unik, dipakai untuk login)
            $table->string('nip')->unique()->after('name');
            // Role: 'admin' atau 'user'
            $table->enum('role', ['admin', 'user'])->default('user')->after('nip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'role']);
        });
    }
};

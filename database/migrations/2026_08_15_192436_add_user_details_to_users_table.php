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
        Schema::table('users', function (Blueprint $table) {
            $table->string('jabatan', 100)->nullable()->after('role');
            $table->string('bidang', 100)->nullable()->after('jabatan');
            $table->string('pos', 100)->nullable()->after('bidang');
            $table->string('regu', 50)->nullable()->after('pos');
            $table->string('no_hp', 30)->nullable()->after('regu');
            $table->string('status', 20)->default('aktif')->after('no_hp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['jabatan', 'bidang', 'pos', 'regu', 'no_hp', 'status']);
        });
    }
};

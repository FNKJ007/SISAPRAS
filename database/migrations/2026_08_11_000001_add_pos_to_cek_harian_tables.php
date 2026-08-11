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
        if (!Schema::hasColumn('cek_harian_alats', 'pos')) {
            Schema::table('cek_harian_alats', function (Blueprint $table) {
                $table->string('pos')->nullable()->after('jabatan');
            });
        }

        if (!Schema::hasColumn('cek_harian_units', 'pos')) {
            Schema::table('cek_harian_units', function (Blueprint $table) {
                $table->string('pos')->nullable()->after('jabatan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('cek_harian_alats', 'pos')) {
            Schema::table('cek_harian_alats', function (Blueprint $table) {
                $table->dropColumn('pos');
            });
        }

        if (Schema::hasColumn('cek_harian_units', 'pos')) {
            Schema::table('cek_harian_units', function (Blueprint $table) {
                $table->dropColumn('pos');
            });
        }
    }
};

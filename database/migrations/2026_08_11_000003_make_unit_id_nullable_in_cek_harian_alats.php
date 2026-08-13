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
        Schema::table('cek_harian_alats', function (Blueprint $table) {
            $table->unsignedInteger('unit_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cek_harian_alats', function (Blueprint $table) {
            $table->unsignedInteger('unit_id')->nullable(false)->change();
        });
    }
};

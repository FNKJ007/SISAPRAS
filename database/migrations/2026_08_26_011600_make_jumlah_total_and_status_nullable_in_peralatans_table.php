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
        Schema::table('peralatans', function (Blueprint $table) {
            $table->integer('jumlah_total')->nullable()->change();
            $table->string('status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peralatans', function (Blueprint $table) {
            $table->integer('jumlah_total')->default(1)->change();
            $table->string('status')->default('baik')->change();
        });
    }
};

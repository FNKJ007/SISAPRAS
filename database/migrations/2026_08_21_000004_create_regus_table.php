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
        if (!Schema::hasTable('regus')) {
            Schema::create('regus', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('pos')->nullable();
                $table->string('bidang')->nullable();
                $table->string('danru')->nullable();
                $table->string('nip_danru')->nullable();
                $table->string('status')->default('aktif');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regus');
    }
};
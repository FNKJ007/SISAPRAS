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
        Schema::dropIfExists('pos');

        Schema::create('pos', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode_pos')->nullable();
            
            // Personil Counts
            $table->integer('personil_pemadam')->default(0);
            $table->integer('personil_rescue')->default(0);
            $table->integer('personil_cc')->default(0);

            // Unit Operasional Counts
            $table->integer('unit_truck_pancar')->default(0);
            $table->integer('unit_motor_roda3')->default(0);
            $table->integer('unit_motor_roda2')->default(0);
            $table->integer('unit_pompa')->default(0);
            $table->integer('unit_rescue')->default(0);
            $table->integer('unit_water_supply')->default(0);
            $table->integer('unit_lainnya')->default(0);

            $table->string('alamat')->nullable();
            $table->string('wilayah')->nullable();
            $table->string('telepon')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos');
    }
};

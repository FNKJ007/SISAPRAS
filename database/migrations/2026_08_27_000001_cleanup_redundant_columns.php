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
        // 1. Drop redundant jenis_peruntukan from units table
        if (Schema::hasColumn('units', 'jenis_peruntukan')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropColumn('jenis_peruntukan');
            });
        }

        // 2. Drop static unit count columns from pos table
        $posColumnsToDrop = [
            'unit_truck_pancar',
            'unit_motor_roda3',
            'unit_motor_roda2',
            'unit_pompa',
            'unit_rescue',
            'unit_water_supply',
            'unit_lainnya',
        ];

        $existingPosColumns = array_filter($posColumnsToDrop, function ($col) {
            return Schema::hasColumn('pos', $col);
        });

        if (!empty($existingPosColumns)) {
            Schema::table('pos', function (Blueprint $table) use ($existingPosColumns) {
                $table->dropColumn(array_values($existingPosColumns));
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'jenis_peruntukan')) {
                $table->string('jenis_peruntukan')->nullable()->after('cc');
            }
        });

        Schema::table('pos', function (Blueprint $table) {
            $table->integer('unit_truck_pancar')->default(0);
            $table->integer('unit_motor_roda3')->default(0);
            $table->integer('unit_motor_roda2')->default(0);
            $table->integer('unit_pompa')->default(0);
            $table->integer('unit_rescue')->default(0);
            $table->integer('unit_water_supply')->default(0);
            $table->integer('unit_lainnya')->default(0);
        });
    }
};

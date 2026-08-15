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
        Schema::table('units', function (Blueprint $table) {
            $table->string('jenis_kendaraan')->nullable()->after('cc');
            $table->string('peruntukan')->nullable()->after('jenis_kendaraan');
        });

        // Populate existing units by splitting jenis_peruntukan
        $units = \Illuminate\Support\Facades\DB::table('units')->get();
        foreach ($units as $u) {
            $parts = explode('/', $u->jenis_peruntukan ?? '');
            $jenis = trim($parts[0] ?? '');
            $peruntukan = trim($parts[1] ?? '');
            
            \Illuminate\Support\Facades\DB::table('units')
                ->where('id', $u->id)
                ->update([
                    'jenis_kendaraan' => $jenis ?: null,
                    'peruntukan'      => $peruntukan ?: null,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['jenis_kendaraan', 'peruntukan']);
        });
    }
};

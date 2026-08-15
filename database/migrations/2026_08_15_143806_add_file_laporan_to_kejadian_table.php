<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kejadian', function (Blueprint $table) {
            $table->string('file_laporan')->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('kejadian', function (Blueprint $table) {
            $table->dropColumn('file_laporan');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kejadian', function (Blueprint $table) {
            $table->string('komandan_regu')->nullable()->after('pos_regu');
        });
    }

    public function down(): void
    {
        Schema::table('kejadian', function (Blueprint $table) {
            $table->dropColumn('komandan_regu');
        });
    }
};

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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'kategori_monitoring')) {
                $table->string('kategori_monitoring', 50)->default('invoice')->nullable()->after('status');
            }
            if (!Schema::hasColumn('invoices', 'pengajuan_id')) {
                $table->foreignId('pengajuan_id')->nullable()->after('kategori_monitoring')->constrained('pengajuans')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['pengajuan_id']);
            $table->dropColumn(['kategori_monitoring', 'pengajuan_id']);
        });
    }
};

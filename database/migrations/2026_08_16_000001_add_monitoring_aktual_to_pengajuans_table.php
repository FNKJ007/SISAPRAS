<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            $table->string('status_pengerjaan')->default('belum_mulai')->after('status'); // belum_mulai, proses, selesai
            $table->date('tanggal_mulai_pengerjaan')->nullable()->after('status_pengerjaan');
            $table->date('tanggal_selesai_pengerjaan')->nullable()->after('tanggal_mulai_pengerjaan');
            $table->integer('progress_persen')->default(0)->after('tanggal_selesai_pengerjaan');
            $table->text('progress_catatan')->nullable()->after('progress_persen');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            $table->dropColumn(['status_pengerjaan', 'tanggal_mulai_pengerjaan', 'tanggal_selesai_pengerjaan', 'progress_persen', 'progress_catatan']);
        });
    }
};

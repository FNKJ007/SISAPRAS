<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add 3NF columns to users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'bidang_id')) {
                $table->unsignedBigInteger('bidang_id')->nullable()->index();
            }
            if (!Schema::hasColumn('users', 'pos_id')) {
                $table->unsignedBigInteger('pos_id')->nullable()->index();
            }
            if (!Schema::hasColumn('users', 'regu_id')) {
                $table->unsignedBigInteger('regu_id')->nullable()->index();
            }
        });

        // 2. Add 3NF columns to regus
        Schema::table('regus', function (Blueprint $table) {
            if (!Schema::hasColumn('regus', 'bidang_id')) {
                $table->unsignedBigInteger('bidang_id')->nullable()->index();
            }
            if (!Schema::hasColumn('regus', 'pos_id')) {
                $table->unsignedBigInteger('pos_id')->nullable()->index();
            }
            if (!Schema::hasColumn('regus', 'danru_user_id')) {
                $table->unsignedBigInteger('danru_user_id')->nullable()->index();
            }
        });

        // 3. Add 3NF columns to units
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'bidang_id')) {
                $table->unsignedBigInteger('bidang_id')->nullable()->index();
            }
            if (!Schema::hasColumn('units', 'pos_id')) {
                $table->unsignedBigInteger('pos_id')->nullable()->index();
            }
            if (!Schema::hasColumn('units', 'pengemudi_utama_id')) {
                $table->unsignedBigInteger('pengemudi_utama_id')->nullable()->index();
            }
            if (!Schema::hasColumn('units', 'pengemudi_cadangan_id')) {
                $table->unsignedBigInteger('pengemudi_cadangan_id')->nullable()->index();
            }
        });

        // 4. Add 3NF columns to peralatans
        Schema::table('peralatans', function (Blueprint $table) {
            if (!Schema::hasColumn('peralatans', 'bidang_id')) {
                $table->unsignedBigInteger('bidang_id')->nullable()->index();
            }
            if (!Schema::hasColumn('peralatans', 'pos_id')) {
                $table->unsignedBigInteger('pos_id')->nullable()->index();
            }
        });

        // 5. Add 3NF columns to pengajuans
        Schema::table('pengajuans', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuans', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->index();
            }
            if (!Schema::hasColumn('pengajuans', 'bidang_id')) {
                $table->unsignedBigInteger('bidang_id')->nullable()->index();
            }
            if (!Schema::hasColumn('pengajuans', 'pos_id')) {
                $table->unsignedBigInteger('pos_id')->nullable()->index();
            }
            if (!Schema::hasColumn('pengajuans', 'regu_id')) {
                $table->unsignedBigInteger('regu_id')->nullable()->index();
            }
            if (!Schema::hasColumn('pengajuans', 'danru_user_id')) {
                $table->unsignedBigInteger('danru_user_id')->nullable()->index();
            }
            if (!Schema::hasColumn('pengajuans', 'kabid_user_id')) {
                $table->unsignedBigInteger('kabid_user_id')->nullable()->index();
            }
        });

        // 6. Create pengajuan_items table (1NF & 3NF atomic rows)
        if (!Schema::hasTable('pengajuan_items')) {
            Schema::create('pengajuan_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pengajuan_id')->index();
                $table->string('deskripsi_kerusakan');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_items');
    }
};
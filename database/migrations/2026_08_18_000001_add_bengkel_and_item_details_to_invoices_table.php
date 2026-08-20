<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'nama_bengkel')) {
                $table->string('nama_bengkel', 150)->nullable()->after('tanggal_invoice');
            }
            if (!Schema::hasColumn('invoices', 'potongan')) {
                $table->decimal('potongan', 15, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('invoices', 'pajak')) {
                $table->decimal('pajak', 15, 2)->default(0)->after('potongan');
            }
            if (!Schema::hasColumn('invoices', 'biaya_lain')) {
                $table->decimal('biaya_lain', 15, 2)->default(0)->after('pajak');
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_items', 'kode_item')) {
                $table->string('kode_item', 50)->nullable()->after('tanggal');
            }
            if (!Schema::hasColumn('invoice_items', 'potongan_persen')) {
                $table->decimal('potongan_persen', 5, 2)->default(0)->after('harga_satuan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['nama_bengkel', 'potongan', 'pajak', 'biaya_lain']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['kode_item', 'potongan_persen']);
        });
    }
};

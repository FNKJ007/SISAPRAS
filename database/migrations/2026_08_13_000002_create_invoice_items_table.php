<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();

            $table->date('tanggal');
            $table->string('jenis_perbaikan', 150);
            $table->decimal('vol', 10, 2)->default(1);
            $table->string('satuan', 20)->default('PCS');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('total_biaya', 15, 2)->default(0); // vol * harga_satuan

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};

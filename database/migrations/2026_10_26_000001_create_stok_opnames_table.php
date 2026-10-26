<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('barang_type')->default('reagen'); // 'reagen', 'baku-pembanding', 'atk', 'perlengkapan', 'suku-cadang'
            $table->unsignedBigInteger('barang_id');
            $table->string('kode_barang')->nullable();
            $table->string('nama_barang');
            $table->string('satuan')->nullable();
            $table->string('lokasi')->nullable();
            $table->integer('stok_sistem')->default(0);
            $table->integer('stok_fisik')->default(0);
            $table->integer('selisih')->default(0); // stok_fisik - stok_sistem
            $table->text('keterangan')->nullable();
            $table->string('petugas_name')->nullable();
            $table->string('petugas_external_id')->nullable();
            $table->timestamp('tanggal_opname')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_opnames');
    }
};

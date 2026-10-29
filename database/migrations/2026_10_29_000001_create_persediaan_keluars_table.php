<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersediaanKeluarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('persediaan_keluars')) {
            Schema::create('persediaan_keluars', function (Blueprint $table) {
                $table->id();
                $table->string('barang_type', 50)->index(); // reagen, baku-pembanding, atk, perlengkapan, suku-cadang
                $table->unsignedBigInteger('barang_id')->index();
                $table->string('kode_barang', 100)->nullable();
                $table->string('nama_barang', 255);
                $table->string('satuan', 50)->nullable();
                $table->string('lokasi', 255)->nullable();
                $table->string('kategori_keluar', 50)->index(); // usang, rusak
                $table->integer('jumlah')->default(1);
                $table->integer('stok_awal')->default(0);
                $table->integer('stok_sisa')->default(0);
                $table->text('keterangan')->nullable();
                $table->string('petugas_name', 255)->nullable();
                $table->dateTime('tanggal_keluar')->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('persediaan_keluars');
    }
}


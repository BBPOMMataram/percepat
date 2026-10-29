<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStokOpnameBeritaAcarasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('stok_opname_berita_acaras')) {
            Schema::create('stok_opname_berita_acaras', function (Blueprint $table) {
                $table->id();
                $table->string('judul', 255)->nullable();
                $table->string('nomor_ba', 150)->nullable();
                $table->date('tanggal_ba')->nullable();
                $table->string('file_name', 255);
                $table->string('file_path', 500);
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('uploaded_by', 255)->nullable();
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
        Schema::dropIfExists('stok_opname_berita_acaras');
    }
}


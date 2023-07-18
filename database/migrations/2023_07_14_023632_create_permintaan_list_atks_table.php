<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermintaanListAtksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permintaan_list_atks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('permintaan_id');
            $table->bigInteger('atk_id');
            $table->integer('jumlahpermintaan')->unsigned();
            $table->integer('jumlahrealisasi')->unsigned()->nullable();
            $table->string('keterangan', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permintaan_list_atks');
    }
}

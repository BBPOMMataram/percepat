<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermintaansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permintaans', function (Blueprint $table) {
            $table->id();
            $table->integer('nourut')->unsigned();
            $table->dateTime('tgl_permintaan')->useCurrent();
            // $table->string('bidang', 100);
            // $table->bigInteger('kabid_id')->unsigned();
            $table->bigInteger('bidang_id')->unsigned();
            $table->bigInteger('status_id')->unsigned()->default(1);
            $table->dateTime('tgl_penyerahan')->nullable();
            $table->string('jenis', 100)->default('Reagen dan Bahan Laboratorium Lain');
            $table->bigInteger('created_by')->unsigned();
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
        Schema::dropIfExists('permintaans');
    }
}

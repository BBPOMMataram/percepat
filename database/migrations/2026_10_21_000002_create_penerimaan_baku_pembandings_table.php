<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaanBakuPembandingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaan_baku_pembandings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('baku_pembanding_id');
            $table->string('vendor', 100)->nullable();
            $table->dateTime('expired')->nullable();
            $table->integer('jumlah')->unsigned()->default(0);
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
        Schema::dropIfExists('penerimaan_baku_pembandings');
    }
}

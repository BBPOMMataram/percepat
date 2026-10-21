<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBakuPembandingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baku_pembandings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->nullable();
            $table->string('kode_baru', 100)->nullable();
            $table->string('name', 255);
            $table->string('letak', 255)->nullable();
            $table->string('satuan', 100);
            $table->dateTime('expired')->nullable();
            $table->integer('stock')->unsigned()->default(0);
            $table->text('msds')->nullable();
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
        Schema::dropIfExists('baku_pembandings');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaanPerlengkapanKebersihansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaan_perlengkapan_kebersihans', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('perlengkapan_kebersihan_id');
            $table->string('vendor', 100)->nullable();
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
        Schema::dropIfExists('penerimaan_perlengkapan_kebersihans');
    }
}

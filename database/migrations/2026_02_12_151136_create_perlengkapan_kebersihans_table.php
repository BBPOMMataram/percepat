<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerlengkapanKebersihansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perlengkapan_kebersihans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->nullable();
            $table->string('name', 255);
            $table->string('satuan', 100);
            $table->integer('stock')->unsigned()->default(0);
            $table->text('description')->nullable();
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
        Schema::dropIfExists('perlengkapan_kebersihans');
    }
}

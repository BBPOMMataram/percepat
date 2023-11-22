<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPenyerahIdToPermintaansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permintaans', function (Blueprint $table) {
            $table->bigInteger('penyerah_id')->default(18);
            $table->bigInteger('kasubbagumum_id')->default(0);
            $table->bigInteger('kabid_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permintaans', function (Blueprint $table) {
            $table->dropColumn('penyerah_id');
            $table->dropColumn('kasubbagumum_id');
            $table->dropColumn('kabid_id');
        });
    }
}

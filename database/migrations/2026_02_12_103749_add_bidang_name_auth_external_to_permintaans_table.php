<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBidangNameAuthExternalToPermintaansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permintaans', function (Blueprint $table) {
            $table->string('bidang_name_auth_external')->after('bidang_id_auth_external')->nullable();
            $table->string('katim_selected')->after('bidang_name_auth_external')->nullable();
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
            $table->dropColumn('bidang_name_auth_external');
            $table->dropColumn('katim_selected');
        });
    }
}

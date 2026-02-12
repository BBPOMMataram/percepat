<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBidangIdNullableAndAddBidangIdAuthExternalToPermintaansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permintaans', function (Blueprint $table) {
            $table->unsignedTinyInteger('bidang_id_auth_external')->nullable()->after('bidang_id');
            $table->unsignedBigInteger('bidang_id')->nullable()->change();
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
            $table->dropColumn('bidang_id_auth_external');
            $table->unsignedBigInteger('bidang_id')->nullable(false)->change();
        });
    }
}

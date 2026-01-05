<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateBidangIdNullableToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('bidang_id', 'bidang_id_old');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('bidang_id')->nullable();
        });

        DB::statement('UPDATE users SET bidang_id = bidang_id_old');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bidang_id_old');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 1. Buat kolom lama kembali
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('bidang_id_old')
                ->after('id');
        });

        // 2. Copy data dari kolom baru
        DB::statement('UPDATE users SET bidang_id_old = bidang_id');

        // 3. Hapus kolom baru
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bidang_id');
        });

        // 4. Rename kembali ke nama semula
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('bidang_id_old', 'bidang_id');
        });
    }
}

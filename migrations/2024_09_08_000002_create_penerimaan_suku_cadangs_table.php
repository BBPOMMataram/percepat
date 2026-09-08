<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('penerimaan_suku_cadangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suku_cadang_id')->constrained('suku_cadangs')->onDelete('cascade');
            $table->integer('jumlah');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('penerimaan_suku_cadangs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurveyPelananPublicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_pelanan_publics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hp');
            $table->string('instansi')->nullable();
            $table->string('email')->nullable();
            $table->tinyInteger('age')->nullable();
            $table->text('comment')->nullable();
            $table->tinyInteger('rating')->comment('1 = Tidak Puas, 2 = Puas, 3 = Sangat Puas');
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
        Schema::dropIfExists('survey_pelanan_publics');
    }
}

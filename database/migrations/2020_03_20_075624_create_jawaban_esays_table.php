<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJawabanEsaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jawaban_esays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('banksoal_id');
            $table->unsignedBigInteger('peserta_id');
            $table->unsignedBigInteger('jawab_id');
            $table->unsignedBigInteger('corrected_by');
            $table->float('point');
            $table->timestamps();

            $table->foreign('banksoal_id')->references('id')->on('banksoals');
            $table->foreign('peserta_id')->references('id')->on('pesertas');
            $table->foreign('jawab_id')->references('id')->on('jawaban_pesertas');
            $table->foreign('corrected_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jawaban_esays');
    }
}

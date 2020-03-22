<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJawabanPesertas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jawaban_pesertas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('banksoal_id');
            $table->unsignedBigInteger('soal_id');
            $table->unsignedBigInteger('peserta_id');
            $table->unsignedBigInteger('jadwal_id');
            $table->bigInteger('jawab');
            $table->longText('esay')->nullable()->default(null);
            $table->char('ragu_ragu',1)->default(0);
            $table->char('iscorrect',1)->default(0);
            $table->timestamps();

            $table->foreign('banksoal_id')->references('id')->on('banksoals');
            $table->foreign('soal_id')->references('id')->on('soals');
            $table->foreign('peserta_id')->references('id')->on('pesertas');
            $table->foreign('jadwal_id')->references('id')->on('jadwals');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jawaban_pesertas');
    }
}

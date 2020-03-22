<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJadwalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jadwals', function (Blueprint $table) {
            $table->id();
            $table->string('banksoal_id');
            $table->striing('server_id')->nullable()->default(0);
            $table->string('alias', 50)->nullable();
            $table->date('tanggal');
            $table->time('mulai');
            $table->time('berakhir');
            $table->integer('lama');
            $table->char('status_ujian',1);
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
        Schema::dropIfExists('jadwals');
    }
}

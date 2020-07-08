<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAchievesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('achieves', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('banksoal_id');
            $table->bigInteger('peserta_id');
            $table->bigInteger('jadwal_id');
            $table->text('achieve');
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
        Schema::dropIfExists('achieves');
    }
}

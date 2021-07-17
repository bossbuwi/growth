<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvnthistEvnttypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evnthist_evnttype', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evnthist_id');
            $table->unsignedBigInteger('evnttype_id');
            $table->unique(['evnthist_id', 'evnttype_id']);
            $table->foreign('evnthist_id')->references('id')->on('eventshistory');
            $table->foreign('evnttype_id')->references('id')->on('eventtypes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evnthist_evnttype');
    }
}

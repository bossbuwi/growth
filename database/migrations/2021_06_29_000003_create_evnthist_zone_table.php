<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvnthistZoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evnthist_zone', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evnthist_id');
            $table->unsignedBigInteger('zone_id');
            $table->unique(['evnthist_id', 'zone_id']);
            $table->foreign('evnthist_id')->references('id')->on('eventshistory');
            $table->foreign('zone_id')->references('id')->on('zones');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evnthist_zone');
    }
}

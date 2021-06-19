<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventtypeRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventtype_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('eventtype_id');
            $table->unsignedBigInteger('role_id');
            $table->unique(['eventtype_id', 'role_id']);
            $table->foreign('eventtype_id')->references('id')->on('eventtypes');
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_role');
    }
}

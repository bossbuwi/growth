<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventshistory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('system_id');
            $table->string('jira_case')->nullable();
            $table->string('api_used')->nullable();
            $table->string('compiled_sources')->nullable();
            $table->string('feature_on')->nullable();
            $table->string('feature_off')->nullable();
            $table->string('details')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            //timestamps and users
            $table->string('status');
            $table->string('executed_by');
            $table->timestamp('executed_at')->nullable();
            //other attributes
            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('system_id')->references('id')->on('systems');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eventshistory');
    }
}

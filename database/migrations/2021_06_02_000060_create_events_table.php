<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
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
            $table->string('created_by');
            $table->timestamp('created_at')->nullable();
            $table->string('last_modified_by');
            $table->timestamp('updated_at')->nullable();
            $table->string('deleted_by')->nullable();
            //other attributes
            $table->foreign('system_id')->references('id')->on('systems');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}

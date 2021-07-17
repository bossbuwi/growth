<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('app');
            $table->string('name');
            $table->string('description');
            $table->string('type');
            $table->string('current_value');
            $table->string('default_value');
            $table->string('accepted_values');
            $table->string('last_modified_by');
            $table->timestamp('last_modified_on')->nullable();
        });

        Artisan::call('db:seed', [
            '--class' => 'ConfigurationSeeder',
            '--force' => true
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configurations');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageFlowOutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_flow_out', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->timestamps();

            $table->string('status', 20)->default('new')->comment('The status used to trigger actions.');
            $table->string('name', 60)->default('default')->comment('A name used for routing.');

            $table->string('queue_connection', 100)->nullable()->comment('The queue connection to send the message on.');
            $table->string('queue_name', 100)->nullable()->comment('The queue name to send the message on.');

            $table->json('payload')->comment('The JSON message payload.');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('message_flow_out');
    }
}

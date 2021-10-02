<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageFlowInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_flow_in', function (Blueprint $table) {
            $table->uuid('uuid')->primary()->comment('The primary key will match the key of the original source message.');
            $table->timestamps();

            $table->string('status', 20)->default('new')->comment('The status used to track the handling of the message.');
            $table->string('name', 60)->comment('The original name of the message, for routing to a specific handler.');

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
        Schema::dropIfExists('message_flow_in');
    }
}

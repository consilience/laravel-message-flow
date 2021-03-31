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
            $table->uuid('uuid')->primary();
            $table->timestamps();

            $table->string('status', 20)->default('new');
            $table->string('name', 60);

            $table->json('payload');
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

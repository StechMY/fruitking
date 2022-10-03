<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('agent_id');
            $table->unsignedBigInteger('fruit_id');
            $table->foreign('fruit_id')->references('id')->on('fruits');
            $table->integer('stock_pack');
            $table->char('status', 1)->default(1);
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
        Schema::dropIfExists('agent_stocks');
    }
}

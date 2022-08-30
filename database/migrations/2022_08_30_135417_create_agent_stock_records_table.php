<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentStockRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_stock_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agentstock_id');
            $table->foreign('agentstock_id')->references('id')->on('agent_stocks');
            $table->integer('stock_before');
            $table->integer('quantity');
            $table->integer('stock_after');
            $table->string('remarks');
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
        Schema::dropIfExists('agent_stock_records');
    }
}

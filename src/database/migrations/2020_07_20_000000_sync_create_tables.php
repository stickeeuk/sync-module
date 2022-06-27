<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SyncCreateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sync_tests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('test_1');
            $table->string('test_2');
            $table->string('test_3')->nullable();
            $table->enum('test_4', ['A', 'B', 'C'])->nullable();
        });

        Schema::create('sync_tests_client', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('test_1');
            $table->string('test_2');
            $table->string('test_3')->nullable();
            $table->enum('test_4', ['A', 'B', 'C'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sync_tests');
        Schema::dropIfExists('sync_tests_client');
    }
}

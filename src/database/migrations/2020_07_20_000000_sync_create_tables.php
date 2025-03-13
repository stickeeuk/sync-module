<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SyncCreateTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sync_tests', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->integer('test_1');
            $table->string('test_2');
            $table->string('test_3')->nullable();
            $table->enum('test_4', ['A', 'B', 'C'])->nullable();
        });

        Schema::create('sync_tests_client', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->integer('test_1');
            $table->string('test_2');
            $table->string('test_3')->nullable();
            $table->enum('test_4', ['A', 'B', 'C'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_tests');
        Schema::dropIfExists('sync_tests_client');
    }
}

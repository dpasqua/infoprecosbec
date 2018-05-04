<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Oc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('situacoes', function(Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->timestamps();
            $table->collation = 'utf8mb4_unicode_ci';
        });

        Schema::create('ocs', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_uge');
            $table->unsignedInteger('id_situacao');
            $table->string('codigo');
            $table->string('procedimento');
            $table->timestamp('dt_encerramento')->nullable();
            $table->timestamps();
            $table->collation = 'utf8mb4_unicode_ci';
        });

        Schema::table('ocs', function(Blueprint $table) {
            $table->foreign('id_uge')->references('id')->on('uges')->onDelete('cascade');
            $table->foreign('id_situacao')->references('id')->on('situacoes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ocs');
        Schema::drop('situacoes');
    }
}

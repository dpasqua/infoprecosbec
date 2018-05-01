<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Uges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orgaos', function(Blueprint $table) {
            $table->increments('id');
            $table->string('codigo');
            $table->string('nome')->nullable();
            $table->timestamps();
            $table->collation = 'utf8mb4_unicode_ci';
        });

        Schema::create('gestoes', function(Blueprint $table) {
            $table->increments('id');
            $table->string('codigo');
            $table->string('info')->nullable();
            $table->timestamps();
            $table->collation = 'utf8mb4_unicode_ci';
        });

        Schema::create('uges', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_orgao');
            $table->unsignedInteger('id_gestao');
            $table->unsignedInteger('id_municipio');
            $table->string('uc');
            $table->string('nome');
            $table->string('endereco');
            $table->timestamps();
            $table->collation = 'utf8mb4_unicode_ci';
        });

        Schema::table('uges', function(Blueprint $table) {
            $table->foreign('id_orgao')->references('id')->on('orgaos')->onDelete('cascade');
            $table->foreign('id_gestao')->references('id')->on('gestoes')->onDelete('cascade');
            $table->foreign('id_municipio')->references('id')->on('municipios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('uges');
        Schema::drop('gestoes');
        Schema::drop('orgaos');
    }
}

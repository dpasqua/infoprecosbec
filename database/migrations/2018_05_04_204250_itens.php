<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Itens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classes', function(Blueprint $table) {
            $table->increments('id');
            $table->string('codigo');
            $table->string('descricao');
            $table->timestamps();
            $table->collation = 'utf8mb4_unicode_ci';
        });

        Schema::create('itens', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_classe');
            $table->unsignedInteger('id_oc');
            $table->string('nr_sequencia_item');
            $table->string('codigo');
            $table->string('descricao');
            $table->string('unidade_fornecimento');
            $table->string('quantidade');
            $table->timestamps();
            $table->collation = 'utf8mb4_unicode_ci';
        });

        Schema::table('itens', function(Blueprint $table) {
            $table->foreign('id_classe')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('id_oc')->references('id')->on('ocs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('itens');
        Schema::drop('classes');
    }
}

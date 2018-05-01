<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Municipios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('municipios', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_regiao');
            $table->string('codigo')->nullable();
            $table->string('nome');
            $table->timestamps();
            $table->collation = 'utf8mb4_unicode_ci';
        });

        Schema::table('municipios', function(Blueprint $table) {
            $table->foreign('id_regiao')->references('id')->on('regioes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('municipios');
    }
}

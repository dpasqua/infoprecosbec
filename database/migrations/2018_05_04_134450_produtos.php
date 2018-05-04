<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Produtos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produtos', function(Blueprint $table) {
            $table->unsignedInteger('codigo');
            $table->string('desc_item');
            $table->unsignedInteger('qtd_oc');
            $table->timestamps();
            $table->collation = 'utf8mb4_unicode_ci';            
        });

        Schema::table('produtos', function(Blueprint $table) {
            $table->primary('codigo');
            $table->index('qtd_oc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('produtos');
    }
}
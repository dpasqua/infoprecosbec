<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Fornecedores extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cnpj');
            $table->string('nome');
            $table->string('apelido');
            $table->string('porte');
            $table->timestamps();
            $table->collation = 'utf8mb4_unicode_ci';
        });

        Schema::table('itens', function(Blueprint $table) {
            $table->double('menor_valor', 8, 2)->after('quantidade');
            $table->unsignedInteger('id_fornecedor_vencedor')->after('menor_valor');
        });

        Schema::table('itens', function(Blueprint $table) {
            $table->foreign('id_fornecedor_vencedor', 'fornecedor_vencedor')->references('id')->on('fornecedores')->onDelete('cascade');
        });

        Schema::create('propostas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_item');
            $table->unsignedInteger('id_fornecedor');
            $table->double('valor', 8, 2);
            $table->timestamps();
            $table->collation = 'utf8mb4_unicode_ci';
        });

        Schema::table('propostas', function(Blueprint $table) {
            $table->foreign('id_item')->references('id')->on('itens')->onDelete('cascade');
            $table->foreign('id_fornecedor')->references('id')->on('fornecedores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('propostas');

        Schema::table('itens', function(Blueprint $table) {
            $table->dropColumn('menor_valor');
            $table->dropForeign('fornecedor_vencedor');
            $table->dropColumn('id_fornecedor_vencedor');
        });

        Schema::drop('fornecedores');
    }
}

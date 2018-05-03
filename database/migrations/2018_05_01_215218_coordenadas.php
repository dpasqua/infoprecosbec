<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Coordenadas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('uges', function(Blueprint $table) {
            $table->string('cep')->nullable()->after('endereco');
            $table->string('email')->nullable()->after('cep');
            $table->string('telefone')->nullable()->after('email');
            $table->string('fax')->nullable()->after('telefone');
            $table->string('cnpj')->nullable()->after('fax');
            $table->point('coordenadas')->nullable()->after('cnpj');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('uges', function(Blueprint $table) {
            $table->dropColumn('cep');
            $table->dropColumn('email');
            $table->dropColumn('telefone');
            $table->dropColumn('fax');
            $table->dropColumn('cnpj');
            $table->dropColumn('coordenadas');
        });
    }
}

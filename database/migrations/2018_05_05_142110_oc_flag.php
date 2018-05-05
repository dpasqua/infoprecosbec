<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OcFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ocs', function(Blueprint $table) {
            $table->boolean('detalhe_processado', false)->after('dt_encerramento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ocs', function(Blueprint $table) {
            $table->dropColumn('detalhe_processado');
        });
    }
}

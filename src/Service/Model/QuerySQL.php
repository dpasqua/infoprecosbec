<?php

namespace Infoprecos\BEC\Service\Model;

use Illuminate\Support\Facades\DB;
use Infoprecos\BEC\Service\Char\Formatter;

class QuerySQL
{
    public static function queryCoordenadas($uge_nome)
    {
        $sql = 'SELECT AsText(`coordenadas`) AS coordenadas FROM uges WHERE nome = \'' . $uge_nome . '\'';
        $result = DB::select($sql);
        return $result[0]['coordenadas'];
    }

    //public static function queryUGEsRaio($raio)
    //{
    //
    //}

    public static function queryOCs($dt_inicial, $dt_final)
    {
        $dt_inicial = Formatter::formataDataParaMySQL($dt_inicial);
        $dt_final = Formatter::formataDataParaMySQL($dt_final);

        $sql = 'select * from ocs where dt_encerramento between \'' . $dt_inicial . '\' and \'' . $dt_final . '\'';

        $result = DB::select($sql);
        var_dump($result);
    }


    //// SELECT * FROM image WHERE ST_Distance(GeomFromText('POINT(13.430692 52.518139)', 4326), location) <= 5000


}
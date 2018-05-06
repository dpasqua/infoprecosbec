<?php

namespace Infoprecos\BEC\Service\Model;

use Illuminate\Support\Facades\DB;
use Infoprecos\BEC\Service\Char\Formatter;

class QuerySQL
{
    public static function coordenadas($uge_nome)
    {
        $sql = 'SELECT AsText(`coordenadas`) AS coordenadas FROM uges WHERE nome = \'' . $uge_nome . '\'';
        $result = DB::select($sql);
        return $result[0]->coordenadas;
    }

    /**
     * @param $dt_inicial
     * @param $dt_final
     * @return array
     */
    public static function valoresOCs($codigo, $dt_inicial, $dt_final, $uc_nome = null)
    {
        $dt_inicial = Formatter::formataDataParaMySQL($dt_inicial);
        $dt_final = Formatter::formataDataParaMySQL($dt_final);

        $sql = '
            select u.uc, u.nome, X(coordenadas) as lat, Y(coordenadas) as log,
                min(menor_valor) as valor_min, 
                max(menor_valor) as valor_max, 
                avg(menor_valor)  as valor_media,
                count(o.id) as ocs,
                sum(quantidade) as qtde
            from uges u
            inner join ocs o on u.id=o.id_uge
            inner join itens i on o.id = i.id_oc
            where i.codigo = :codigo';

        if($uc_nome) {
            $sql .= ' AND u.nome = :nome';
        }

        $sql .= '
            AND dt_encerramento BETWEEN :dt_inicial AND :dt_final
            GROUP BY u.id
            ORDER BY u.nome
        ';

        $where = [
                'codigo' => $codigo,
                'dt_inicial' => $dt_inicial, 
                'dt_final' => $dt_final,
            ];

        if($uc_nome) {
            $where['nome'] = $uc_nome;
        }

        $result = DB::select(DB::raw($sql), $where);
        return $result;
    }

    //// coordenadas raio
    //// SELECT * FROM image WHERE ST_Distance(GeomFromText('POINT(13.430692 52.518139)', 4326), location) <= 5000

    public static function graficoPrecoMedioTotalOCs($codigo, $dt_inicial, $dt_final)
    {
        $dt_inicial = Formatter::formataDataParaMySQL($dt_inicial);
        $dt_final = Formatter::formataDataParaMySQL($dt_final);

        $sql = 'select MONTH(dt_encerramento) as mes, YEAR(dt_encerramento) as ano, count(*) as qtd
                from ocs o 
                inner join (select distinct id_oc as id_oc from itens where codigo = :codigo) i
                on o.id=i.id_oc
                WHERE dt_encerramento BETWEEN :dt_inicial AND :dt_final
                GROUP BY YEAR(dt_encerramento), MONTH(dt_encerramento)';

        $result = DB::select(DB::raw($sql), ['codigo' => $codigo, 'dt_inicial' => $dt_inicial, 'dt_final' => $dt_final]);
        return $result;
    }

    public static function graficoPrecoMedio($codigo, $dt_inicial, $dt_final)
    {
        $dt_inicial = Formatter::formataDataParaMySQL($dt_inicial);
        $dt_final = Formatter::formataDataParaMySQL($dt_final);

        $sql = 'select MONTH(dt_encerramento) as mes, YEAR(dt_encerramento) as ano,
                min(menor_valor) as menor_valor, avg(menor_valor) as media 
                from itens i inner join ocs o on i.id_oc=o.id 
                where i.codigo = :codigo AND dt_encerramento BETWEEN :dt_inicial AND :dt_final 
                GROUP BY YEAR(dt_encerramento), MONTH(dt_encerramento)';

        $result = DB::select(DB::raw($sql), ['codigo' => $codigo, 'dt_inicial' => $dt_inicial, 'dt_final' => $dt_final]);
        return $result;
    }

    public static function graficoRegioes($codigo, $dt_inicial, $dt_final)
    {
        $dt_inicial = Formatter::formataDataParaMySQL($dt_inicial);
        $dt_final = Formatter::formataDataParaMySQL($dt_final);

        $sql = 'select count(*) as total, r.nome as nome from ocs o 
                inner join uges u on o.id_uge = u.id 
                inner join municipios m on u.id_municipio = m.id  
                inner join regioes r on m.id_regiao=r.id
                inner join itens i on o.id = i.id_oc
                where i.codigo = :codigo
                AND dt_encerramento BETWEEN :dt_inicial AND :dt_final
                group by r.id';

        $result = DB::select(DB::raw($sql), ['codigo' => $codigo, 'dt_inicial' => $dt_inicial, 'dt_final' => $dt_final]);
        return $result;
    }

    public static function graficoMunicipios($codigo, $dt_inicial, $dt_final)
    {
        $dt_inicial = Formatter::formataDataParaMySQL($dt_inicial);
        $dt_final = Formatter::formataDataParaMySQL($dt_final);

        $sql = 'select max(i.menor_valor), r.nome from ocs o 
                inner join uges u on o.id_uge = u.id 
                inner join municipios m on u.id_municipio = m.id  
                inner join regioes r on m.id_regiao=r.id
                inner join itens i on o.id = i.id_oc
                where i.codigo = :codigo
                AND dt_encerramento BETWEEN :dt_inicial AND :dt_final
                group by r.id';

        $result = DB::select(DB::raw($sql), ['codigo' => $codigo, 'dt_inicial' => $dt_inicial, 'dt_final' => $dt_final]);
        return $result;
    }

    public static function totalOCs($codigo, $dt_inicial, $dt_final)
    {
        $dt_inicial = Formatter::formataDataParaMySQL($dt_inicial);
        $dt_final = Formatter::formataDataParaMySQL($dt_final);

        $sql = 'select count(*) from ocs 
                where id in (select distinct id_oc from itens where codigo = :codigo)
                AND dt_encerramento BETWEEN :dt_inicial AND :dt_final';

        $result = DB::select(DB::raw($sql), ['codigo' => $codigo, 'dt_inicial' => $dt_inicial, 'dt_final' => $dt_final]);
        return $result;
    }

    public static function totalFornecedores($codigo)
    {
        $sql = 'select count(distinct id_fornecedor) from itens i
                inner join propostas p on i.id = p.id_item
                where codigo = :codigo';

        $result = DB::select(DB::raw($sql), ['codigo' => $codigo]);
        return $result;
    }

}
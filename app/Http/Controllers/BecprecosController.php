<?php
namespace App\Http\Controllers;

use App\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Infoprecos\BEC\Service\Char\Formatter;
use Infoprecos\BEC\Service\Model\QuerySQL;

class BecprecosController extends Controller
{
    /** 
     * autocomplete prefeitura
     */
    public function autoCompletePrefeituras()
    {
        $prefeituras = DB::select('select nome from uges');
        $data = [];
        foreach($prefeituras as $prefeitura) {
            $data[$prefeitura->nome] = null;
        }
        return response()->json($data);
    }

    /** 
     * autocomplete prefeitura
     */
    public function autoCompleteProdutos()
    {
        $produtos = DB::select('select desc_item from produtos where qtd_oc >= 5');
        $data = [];
        foreach($produtos as $produto) {
            $data[$produto->desc_item] = null;
        }
        return response()->json($data);
    }

    /**
     * buscar referencias
     */
    public function buscarReferencias(Request $request)
    { 
        $input = $request->all();

        // $input['produto'] $input['uc'] $input['data_inicial'] $input['data_final'] $input['raio']

        $table = $this->pegaTableDados($input);

        $data = [
            'mapa' => $this->pegaMapaDados($input['raio']),
            'table' => $table,
            'chart1' => $this->pegaChart1Dados(),
            'chart2' => $this->pegaChart2Dados(),
            'chart3' => $this->pegaChart3Dados(),
            'infoGeral' => $this->pegaInfoGeral(),
        ];
        return response()->json($data);
    }

    /**
     * dados gerais
     */     
    private function pegaInfoGeral()
    {
        $dados = [
            'unitario_min_mes' => 'Jan/2018',
            'unitario_min_vl' => 'R$ 4,56',
            'localidade_max_regiao1' => 'São Paulo',
            'localidade_max_regiao2' => 'Campinas',
            'localidade_max_regiao3' => 'Marília',
            'investimento_municipio' => 'São Paulo',
            'investimento_valor' => 'R$ 10.000.000,00',
            'orgao_comprador_max' => 'Prefeitura Municipal de São Paulo',
            'oc_num' => '32',
            'fornecedores_participantes' => '53',
            'vencedores_diferentes' => '18',
            'fornecedores_epp' => '33 EPP/ME (62%)',
            'fornecedores_outros' => '20 Outros (38%)'
        ];

        return $dados;
    }

    /**
     * comparativo de preços médios
     */    
    private function pegaChart1Dados()
    {
        $dados = [
            'qtde_oc' => [20, 30, 40, 50, 30, 20, 35, 45, 55, 60, 48, 30],
            'preco_min' => [ 6, 6, 6, 11.3, 17, 22, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5 ],
            'preco_medio' => [ 6, 6.5, 8, 8.4, 13.5, 17, 18.6, 17.9, 14.3, 9, 3.9, 1 ],
            'bgcolor' => array_fill(0, 12, 'rgba(54, 162, 235, 0.2)'),
            'labels' => [ 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez' ],
        ];
        return $dados;
    }

    /**
     * Unidades compradas por Regiao Geografica
     */
    private function pegaChart2Dados()
    {
        $dados = [
            'labels' => [
                'São Paulo',
                'Sorocaba',
                'Bauru',
                'Marília',
                'Presidente Prudente',
                'Araçatuba',
                'São José do Rio Preto',
                'Ribeirão Preto',
                'Araraquara',
                'Campinas',
                'São José dos Campos'],
            'porcentagem' => [ 45, 26.8, 12.8, 8.5, 6.2, 0.7, 0.7, 0.7, 0.7, 0.7, 0.7 ],
        ];

        return $dados;
    }

    /**
     * Top 10 Municípios que mais compraram
     */
    private function pegaChart3Dados()
    {
        $dados = [
            'data' => [ 10000000, 7500000, 5500000, 4000000, 1500000, 1000000, 800000, 500000, 200000, 100000 ],
            'labels' => [ 'São Paulo', 'Osasco', 'Campinas', 'Jundiaí', 'Guarulhos', 'Ribeirão Preto', 'São Bernardo do Campo', 'Bauru', 'Sorocaba', 'Americana' ]
        ];
        return $dados;
    }

    /**
     * dados da tabela
     */
    private function pegaTableDados(array $input)
    {
        $coordenadas_uc = QuerySQL::queryCoordenadas($input['uc']);
        //$ucs = QuerySQL::queryUGEsRaio($coordenadas_uc, $input['raio']);

        // pegar UGES no raio com OCs dentro de datas especificadas
        $ocs = QuerySQL::queryOCs($input['data_inicial'], $input['data_final']);

        $dados = [
            ['Todos', 30, 50000, '10,18', '4,56', '6,84', '-'],
            ['13374 -  Av. Mariana Ubaldina do Espírito Santo', 30, 50000, '10,18', '4,56', '6,84', '-'],
            ['13375 - PREF A', 30, 50000, '10,18', '4,56', '6,84', '-'],
            ['13376 - PREF B', 30, 50000, '10,18', '4,56', '6,84', '-'],
            ['13377 - PREF C', 30, 50000, '10,18', '4,56', '6,84', '-'],
            ['13378 - PREF D', 30, 50000, '10,18', '4,56', '6,84', '-'],
            ['13379 - PREF E', 30, 50000, '10,18', '4,56', '6,84', '-'],
        ];

        return $dados;
    }

    /**
     * dados do mapa
     */
    private function pegaMapaDados($raio)
    {
        $mapa = [
            'center' => [ -23.45646630689063, -46.5166256, "Av. Mariana Ubaldina do Espírito Santo"],
            'points' => [
                [-23.37132835,-46.50763057, "PREF A", 2],
                [-23.38068342,-46.51933063, "PREF B", 3],
                [-23.50887798,-46.49556224, "PREF C", 4],
                [-23.39671372,-46.56571462, "PREF D", 5],
                [-23.4004857,-46.48551614, "PREF E", 6],
            ],
            'raio' => $raio
        ];
        return $mapa;         
    }
}
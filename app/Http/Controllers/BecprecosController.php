<?php
namespace App\Http\Controllers;

use App\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BecprecosController extends Controller
{
    /** 
     * autocomplete prefeitura
     */
    public function autoCompletePrefeituras()
    {
        $data = [
            'PREF A',
            'PREF B',
            'PREF C',
            'PREF D',
            'PREF E',
            'PREF F',
            'PREF G',
            'PREF H',
        ];
        return response()->json($data);
    }

    /** 
     * autocomplete prefeitura
     */
    public function autoCompleteProdutos()
    {
        $produtos = DB::select('select nome from produto');
        $produtos = json_decode(json_encode($produtos), true);

        $produtos = array_map(function ($reg) {
            return $reg['nome'];
        }, $produtos);

        return response()->json($produtos);
    }

    /**
     * buscar referencias
     */
    public function buscarReferencias(Request $request)
    { 
        $input = $request->all();

        //print_r($input);

        $data = [
            'mapa' => $this->pegaMapaDados($input['raio']),
            'table' => $this->pegaTableDados(),
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
            ['', 'Qtd. OC', 'Preço mais baixo licitado', 'Preço médio licitado'],
            ['jan',  20, 6, 6],
            ['fev',  30, 6, 6.50],
            ['mar',  40,  6, 8],
            ['abr',  50, 11.3, 8.4],
            ['mai',  30, 17.0, 13.5],
            ['jun',  20, 22.0, 17.0],
            ['jul',  35, 24.8, 18.6],
            ['ago',  45, 24.1, 17.9],
            ['set',  55, 20.1, 14.3],
            ['out',  60, 14.1, 9.0],
            ['nov',  48,  8.6, 3.9],
            ['dez',  30,  2.5,  1.0]
        ];
        return $dados;
    }

    /**
     * Unidades compradas por Regiao Geografica
     */
    private function pegaChart2Dados()
    {
        $dados = [
            ['São Paulo', 45.0],
            ['Sorocaba', 26.8],  
            ['Bauru', 12.8],
            ['Marília', 8.5],
            ['Presidente Prudente', 6.2],
            ['Araçatuba', 0.7],
            ['São José do Rio Preto', 0.7],
            ['Ribeirão Preto', 0.7],
            ['Araraquara', 0.7],
            ['Campinas', 0.7],
            ['São José dos Campos', 0.7],
        ];

        return $dados;
    }

    /**
     * Top 10 Municípios que mais compraram
     */
    private function pegaChart3Dados()
    {
        $dados = [
            ['São Paulo', 10000000, '#7030A0'],
            ['Osasco', 7500000, '#0F2D69'],
            ['Campinas', 5500000, '#89BC01'],
            ['Jundiaí', 4000000, '#00B0F0'],
            ['Guarulhos', 1500000, '#00B050'],
            ['Ribeirão Preto', 1000000, '#92D050'],
            ['São Bernardo do Campo', 800000, '#FFFF00'],
            ['Bauru', 500000, '#FFC000'],
            ['Sorocaba', 200000, '#FF0000'],
            ['Americana', 100000, '#C74444']
        ];
        return $dados;
    }

    /**
     * dados da tabela
     */
    private function pegaTableDados()
    {
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
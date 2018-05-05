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
        foreach ($prefeituras as $prefeitura) {
            $data[$prefeitura->nome] = null;
        }
        return response()->json($data);
    }

    /**
     * autocomplete prefeitura
     */
    public function autoCompleteProdutos()
    {
        $produtos = DB::select('select codigo, desc_item from produtos where qtd_oc >= 20');
        $data = [];
        foreach($produtos as $produto) {
            $data[$produto->codigo . ' - ' . $produto->desc_item] = null;
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
            'chart4' => $this->pegaChart4Dados(),
            'chart5' => $this->pegaChart5Dados(),
            'infoGeral' => $this->pegaInfoGeral(),
            'tableFornecedor' => $this->pegaTableFornecedorDados()
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
            'preco_min' => [6, 6, 6, 11.3, 17, 22, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5],
            'preco_medio' => [6, 6.5, 8, 8.4, 13.5, 17, 18.6, 17.9, 14.3, 9, 3.9, 1],
            'bgcolor' => array_fill(0, 12, 'rgba(54, 162, 235, 0.2)'),
            'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        ];
        return $dados;
    }

    private function pegaChart4Dados()
    {
        $dados = [
            'qtde_oc' => [20, 30, 40, 50, 30, 20, 35, 45, 55, 60, 48],
            'bgcolor' => array_fill(0, 11, 'rgba(54, 162, 235, 0.2)'),
            'labels' => ['12345678901', '1234 67890', '12345678901', '12 4567 90', '1234567890 ', '123 567890', '1234567890 ', '12345 7890', '1234567890 ', '12345 78901', '12345678901'],
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
            'porcentagem' => [45, 26.8, 12.8, 8.5, 6.2, 0.7, 0.7, 0.7, 0.7, 0.7, 0.7],
        ];

        return $dados;
    }

    /**
     * Top 10 Municípios que mais compraram
     */
    private function pegaChart3Dados()
    {
        $dados = [
            'data' => [10000000, 7500000, 5500000, 4000000, 1500000, 1000000, 800000, 500000, 200000, 100000],
            'labels' => ['São Paulo', 'Osasco', 'Campinas', 'Jundiaí', 'Guarulhos', 'Ribeirão Preto', 'São Bernardo do Campo', 'Bauru', 'Sorocaba', 'Americana']
        ];
        return $dados;
    }

    private function pegaChart5Dados()
    {
        $dados = [
            'labels' => [
                'Cooperativas',
                'EPP',
                'ME',
                'Outros'],
            'porcentagem' => [45, 26.8, 12.8, 10],
        ];
        return $dados;
    }

    /**
     * dados da tabela
     */
    private function pegaTableDados(array $input)
    {
        $coordenadas_uc = QuerySQL::coordenadas($input['uc']);
        //var_dump($coordenadas_uc);

        // pegar UGES no raio com OCs dentro de datas especificadas
        $ocs = QuerySQL::valoresOCs($input['data_inicial'], $input['data_final']);
        //var_dump($ocs); die;
        $dados = [];

        $dados[] = ['Todos', 30, 50000, '10,18', '4,56', '6,84', '-'];
        foreach ($ocs as $oc) {
            $dados[] = [$oc->uc . ' - ' . $oc->nome_uc, 30, 50000, '10,18', '4,56', '6,84', '-'];
        }

        return $dados;
    }

    /**
     * dados do mapa
     */
    private function pegaMapaDados($raio)
    {
        $mapa = [
            'center' => [-23.45646630689063, -46.5166256, "Av. Mariana Ubaldina do Espírito Santo"],
            'points' => [
                [-23.37132835, -46.50763057, "PREF A", 2],
                [-23.38068342, -46.51933063, "PREF B", 3],
                [-23.50887798, -46.49556224, "PREF C", 4],
                [-23.39671372, -46.56571462, "PREF D", 5],
                [-23.4004857, -46.48551614, "PREF E", 6],
            ],
            'raio' => $raio
        ];
        return $mapa;
    }

    private function pegaTableFornecedorDados()
    {
        $dados = [
            ['AAAAAAA', '00.000.000/0001-00', 50000, '10,18', '10'],
            ['BBBBBBB', '00.000.000/0002-00', 50000, '10,18', '20'],
            ['CCCCCCC', '00.000.000/0003-00', 50000, '10,18', '30'],
            ['DDDDDDD', '00.000.000/0004-00', 50000, '10,18', '40'],
            ['EEEEEE', '00.000.000/00005-00', 50000, '10,18', '50'],
            ['FFFFFF', '00.000.000/0006-00', 50000, '10,18', '60'],
            ['GGGGGG', '00.000.000/0007-00', 50000, '10,18', '70'],
        ];

        return $dados;
    }
}
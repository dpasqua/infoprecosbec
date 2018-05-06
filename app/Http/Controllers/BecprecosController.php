<?php
namespace App\Http\Controllers;

use App\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Infoprecos\BEC\Service\Char\Formatter;
use Infoprecos\BEC\Service\Math\Stats;
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

        // pega o codigo do produto
        list($produto) = explode(' ', $input['produto']);
        $input['produto'] = $produto;
        
        // pegar UGES no raio com OCs dentro de datas especificadas
        $ocs = QuerySQL::valoresOCs(
            $input['produto'],
            $input['data_inicial'], 
            $input['data_final']
        );

        $mapa = $this->pegaMapaDados($input['raio'], $ocs);
        $table = $this->pegaTableDados($input, $ocs);

        $preco_medio = $this->pegaChart1Dados($input);

        $data = [
            'mapa' => $mapa,
            'table' => $table,
            'chart1' => $preco_medio,
            'chart2' => $this->pegaChart2Dados(),
            'chart3' => $this->pegaChart3Dados(),
            'chart4' => $this->pegaChart4Dados(),
            'chart5' => $this->pegaChart5Dados(),
            'tableFornecedor' => $this->pegaTableFornecedorDados(),
            'infoGeral' => $this->pegaInfoGeral()
        ];
        return response()->json($data);
    }

    /**
     * dados do mapa
     */
    private function pegaMapaDados($raio, $ocs)
    {
        $mapa = [
            'center' => [ -23.45646630689063, -46.5166256, "Av. Mariana Ubaldina do Espírito Santo"],
            'raio' => $raio
        ];

        $count = 1;
        $points = [];
        foreach($ocs as $oc) {
            $points[] = [
                $oc->lat, $oc->log, $oc->nome, $count++
            ];
        }

        $mapa['points'] = $points;
        return $mapa;
    }

    /**
     * dados da tabela
     */
    private function pegaTableDados(array $input, $ocs)
    {
        $coordenadas_uc = QuerySQL::coordenadas($input['uc']);

        $dados = [];

        $qtd_ocs = 0;
        $qtd_unit = 0;
        $max = []; $min = []; $med = [];

        foreach ($ocs as $oc) {
            $qtd_ocs += (int) $oc->ocs;
            $qtd_unit += (int) $oc->qtde;

            $max[] = $oc->valor_max;
            $min[] = $oc->valor_min;
            $med[] = $oc->valor_media;

            $dados_uc = [
                $oc->uc . ' - ' . $oc->nome,
                $oc->ocs,
                $oc->qtde,
                number_format($oc->valor_max, 2, ',', '.'),
                number_format($oc->valor_min, 2, ',', '.'),
                number_format($oc->valor_media,2, ',', '.'),
                '-'
            ];

            if ($input['uc'] != $oc->nome) {
                $dados[] = $dados_uc;
            } else {
                array_unshift($dados, $dados_uc);
            }

        }

        if (count($ocs) > 0) {
            $todos = [
                'Todos',
                $qtd_ocs,
                $qtd_unit,
                number_format(max($max), 2, ',', '.'),
                number_format(min($min), 2, ',', '.'),
                number_format(Stats::media($med), 2, ',', '.'),
                '-'
            ];
            array_unshift($dados, $todos);
        }

        return $dados;
    }

    /**
     * comparativo de preços médios
     */
    private function pegaChart1Dados(array $input)
    {
        $dados = [];

        $total_ocs = QuerySQL::graficoPrecoMedioTotalOCs($input['produto'], $input['data_inicial'], $input['data_final']);
        foreach ($total_ocs as $total) {
            $dados[$total->ano . '-' . $total->mes] = [
                'qtd' => $total->qtd,
                'mes' => Formatter::mesCorrespondente($total->mes),
                'ano' => $total->ano
            ];
        }

        $precos_medios = QuerySQL::graficoPrecoMedio($input['produto'], $input['data_inicial'], $input['data_final']);
        foreach ($precos_medios as $preco_medio) {
            $dados[$preco_medio->ano . '-' . $preco_medio->mes]['menor_valor'] = $preco_medio->menor_valor;
            $dados[$preco_medio->ano . '-' . $preco_medio->mes]['media'] = $preco_medio->media;
        }

        $dados_formatados = [
            'qtde_oc' => [], 'preco_min' => [], 'preco_medio' => [], 'labels' => [],
            'bgcolor' => array_fill(0, count($dados), 'rgba(54, 162, 235, 0.2)')
        ];
        foreach ($dados as $ano => $dado) {
            $dados_formatados['qtde_oc'][] = $dado['qtd'];
            $dados_formatados['preco_min'][] = $dado['menor_valor'];
            $dados_formatados['preco_medio'][] = $dado['media'];
            $dados_formatados['labels'][] = $dado['mes'] . '/' . substr($dado['ano'], -2);
        }

        return $dados_formatados;
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

}
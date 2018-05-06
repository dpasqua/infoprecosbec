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
    private $dados_regioes;

    /**
     * autocomplete prefeitura
     */
    public function autoCompletePrefeituras()
    {
        $prefeituras = DB::select('select uc, nome from uges');
        $data = [];
        foreach ($prefeituras as $prefeitura) {
            $data[$prefeitura->uc . ' - ' . $prefeitura->nome] = null;
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

        // prefeitura
        $uc = substr($input['uc'], strpos($input['uc'], '-') + 1);
        $uc = trim($uc);
        
        // pegar UGES no raio com OCs dentro de datas especificadas
        $ocs = QuerySQL::valoresOCs(
            $input['produto'],
            $input['data_inicial'], 
            $input['data_final'],
            $uc,
            (integer)$input['raio']
        );

        $mapa = $this->pegaMapaDados($input['raio'], $ocs, $uc);
        $table = $this->pegaTableDados($input, $ocs);
        $tableFornecedor = $this->pegaTableFornecedorDados($input);

        $preco_medio = $this->pegaChart1Dados($input);
        $regioes = $this->pegaChart2Dados($input);
        $portes = $this->pegaChart5Dados($input);

        $data = [
            'mapa' => $mapa,
            'table' => $table,
            'chart1' => $preco_medio,
            'chart2' => $regioes,
            'chart3' => $this->pegaChart3Dados(),
            'chart4' => $this->pegaChart4Dados(),
            'chart5' => $portes,
            'tableFornecedor' => $tableFornecedor,
            'infoGeral' => $this->pegaInfoGeral()
        ];
        return response()->json($data);
    }

    /**
     * dados do mapa
     */
    private function pegaMapaDados($raio, $ocs, $uc)
    { 
        list($coordenadas_uc) = QuerySQL::selecionaCoordenadasUGE($uc);
        $mapa = [
            'center' => [ $coordenadas_uc->lat, $coordenadas_uc->log, $uc],
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
        $dados_formatados = [
            'valor_medio' => [],
            'labels' => [],
            'bgcolor' => array_fill(0, count($this->dados_regioes), 'rgba(54, 162, 235, 0.2)'),
        ];

        foreach ($this->dados_regioes as $regiao) {
            $dados_formatados['labels'][] = substr($regiao->nome,0,8).'...';
            $dados_formatados['preco_medio'][] = number_format($regiao->preco_medio, 2, '.', ',');
        }

        return $dados_formatados;
    }

    /**
     * Unidades compradas por Regiao Geografica
     */
    private function pegaChart2Dados(array $input)
    {
        $this->dados_regioes = QuerySQL::graficoRegioes($input['produto'], $input['data_inicial'], $input['data_final']);
        $cem_por_cento = 0;

        foreach ($this->dados_regioes as $regiao) {
            $cem_por_cento += $regiao->total;
        }
        foreach ($this->dados_regioes as $k => $regiao) {
            $this->dados_regioes[$k]->porcentagem =
                floatval(number_format(Stats::porcentagem($cem_por_cento, $regiao->total), 2, '.', ','));
        }

        $dados_formatados = [
            'labels' => [],
            'porcentagem' => []
        ];

        foreach ($this->dados_regioes as $regiao) {
            if ($regiao->porcentagem == 0) {
                continue; // nao adiciona
            }
            $dados_formatados['labels'][] = $regiao->nome;
            $dados_formatados['porcentagem'][] = $regiao->porcentagem;
        }

        return $dados_formatados;
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

    private function pegaChart5Dados(array $input)
    {
        $portes = QuerySQL::graficoTotalPorte($input['produto'], $input['data_inicial'], $input['data_final']);
        $cem_por_cento = 0;

        foreach ($portes as $porte) {
            $cem_por_cento += $porte->total;
        }
        foreach ($portes as $k => $porte) {
            $portes[$k]->porcentagem =
                floatval(number_format(Stats::porcentagem($cem_por_cento, $porte->total), 2, '.', ','));
        }

        $dados_formatados = [
            'labels' => [],
            'porcentagem' => []
        ];

        foreach ($portes as $porte) {
            if ($porte->porcentagem == 0) {
                continue; // nao adiciona
            }
            $dados_formatados['labels'][] = $porte->porte;
            $dados_formatados['porcentagem'][] = $porte->porcentagem;
        }

        return $dados_formatados;
    }

    private function pegaTableFornecedorDados(array $input)
    {
        $dados = [];

        $produtos_fornecedores = QuerySQL::precoMedioFornecedorProduto($input['produto'], $input['data_inicial'], $input['data_final']);

        foreach ($produtos_fornecedores as $fornecedor) {
            //var_dump ($fornecedor); die;
            $dados[] = [
                $fornecedor->nome,
                $fornecedor->cnpj,
                $fornecedor->porte,
                number_format($fornecedor->menor_valor, 2, ',', '.'),
                number_format($fornecedor->preco_medio, 2, ',', '.')
            ];
        }
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
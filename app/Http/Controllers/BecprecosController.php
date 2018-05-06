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
    private $porcentagens_fornecs;

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

        $preco_medio = $this->pegaChart1Dados($input, $uc);

        if(isset($preco_medio['preco_min'])
            && !empty($preco_medio['preco_min'])) {
            $min_value = min($preco_medio['preco_min']);
            $key = array_search($min_value, $preco_medio['preco_min']);
            $mes = $preco_medio['labels'][$key];
        } else {
            $min_value = 0;
            $mes = '';
        }
        
        $info = [
            'unitario_min_mes' => $mes,
            'unitario_min_vl' => 'R$ ' . number_format($min_value, 2, ',', '.'),
        ];
                    
        $regioes = $this->pegaChart2Dados($input);

        $porcentagem = $regioes['porcentagem'];
        arsort($porcentagem);
        $pkeys = array_keys($porcentagem);

        if(count($pkeys) >= 3) {
            $regiao1 = $regioes['labels'][$pkeys[0]];
            $regiao2 = $regioes['labels'][$pkeys[1]];
            $regiao3 = $regioes['labels'][$pkeys[2]];
        } else {
            $regiao1 = ''; $regiao2 = ''; $regiao3 = '';
        }
        
        $info['localidade_max_regiao1'] = $regiao1;
        $info['localidade_max_regiao2'] = $regiao2;
        $info['localidade_max_regiao3'] = $regiao3;

        $info_geral = $this->pegaInfoGeral($input, $info);
        $portes = $this->pegaChart5Dados();

        $municipios = $this->pegaChart3Dados($input, $uc);
        if(!empty($municipios['labels'])) {
            $mun = $municipios['labels'][0];
            $valor = number_format($municipios['data'][0], 2, ',', '.');  
        } else {
            $mun = '';
            $valor = 0;
        }
        
        $info['investimento_municipio'] = $mun;
        $info['investimento_valor'] = 'R$ ' . $valor;
        
        $data = [
            'mapa' => $mapa,
            'table' => $table,
            'chart1' => $preco_medio,
            'chart2' => $regioes,
            'chart3' => $municipios,
            'chart4' => $this->pegaChart4Dados(),
            'chart5' => $portes,
            'tableFornecedor' => $tableFornecedor,
            'infoGeral' => $info_geral
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
    private function pegaChart1Dados(array $input, $uc)
    {
        $dados = [];

        $total_ocs = QuerySQL::graficoPrecoMedioTotalOCs($input['produto'], $input['data_inicial'], $input['data_final'], $uc, (integer)$input['raio']);
        foreach ($total_ocs as $total) {
            $dados[$total->ano . '-' . $total->mes] = [
                'qtd' => $total->qtd,
                'mes' => Formatter::mesCorrespondente($total->mes),
                'ano' => $total->ano
            ];
        }

        $precos_medios = QuerySQL::graficoPrecoMedio($input['produto'], $input['data_inicial'], $input['data_final'], $uc, (integer)$input['raio']);
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
    private function pegaChart3Dados(array $input, $uc)
    {
        $municipios = QuerySQL::graficoMunicipios($input['produto'], $input['data_inicial'], $input['data_final'], $uc);
        $dados_formatados = [
            'data' => [],
            'labels' => []
        ];

        foreach($municipios as $m) {
            $dados_formatados['labels'][] = $m->nome;
            $dados_formatados['data'][] = $m->maximo;
        }

        return $dados_formatados;
    }

    private function pegaChart5Dados()
    {
        $dados_formatados = [
            'labels' => [],
            'porcentagem' => []
        ];

        $portes = [
            [
                'porte' => 'EPP',
                'porcentagem' => floatval(number_format($this->porcentagens_fornecs['porcentagem_epp'], 2, '.', ','))
            ],
            [
                'porte' => 'ME',
                'porcentagem' => floatval(number_format($this->porcentagens_fornecs['porcentagem_me'], 2, '.', ','))
            ],
            [
                'porte' => 'Outros',
                'porcentagem' => floatval(number_format($this->porcentagens_fornecs['porcentagem_outros'], 2, '.', ','))
            ]
        ];

        foreach ($portes as $porte) {
            if ($porte['porcentagem'] == 0) {
                continue; // nao adiciona
            }

            $dados_formatados['labels'][] = $porte['porte'];
            $dados_formatados['porcentagem'][] = $porte['porcentagem'];
        }
        return $dados_formatados;
    }

    private function pegaTableFornecedorDados(array $input)
    {
        $dados = [];

        $produtos_fornecedores = QuerySQL::precoMedioFornecedorProduto($input['produto'], $input['data_inicial'], $input['data_final']);

        foreach ($produtos_fornecedores as $fornecedor) {
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
    private function pegaInfoGeral(array $input, array $info)
    {
        $fornecs = $this->pegaTotalFornecedores($input);
        $fornecs_outros = $fornecs['total_outros'] . ' Outros (' .
            number_format($fornecs['porcentagem_outros'], 2, ',', '.') . '%)';
        $fornecs_epp = $fornecs['total_epp'] . ' EPP (' .
            number_format($fornecs['porcentagem_epp'], 2, ',', '.') . '%)';
        $fornecs_me = $fornecs['total_me'] . ' ME (' .
            number_format($fornecs['porcentagem_me'], 2, ',', '.') . '%)';

        $dados = [
            'unitario_min_mes' => $info['unitario_min_mes'],
            'unitario_min_vl' => $info['unitario_min_vl'],
            'localidade_max_regiao1' => $info['localidade_max_regiao1'],
            'localidade_max_regiao2' => $info['localidade_max_regiao2'],
            'localidade_max_regiao3' => $info['localidade_max_regiao3'],
            'investimento_municipio' => $info['investimento_municipio'],
            'investimento_valor' => $info['investimento_valor'],
            'orgao_comprador_max' => 'Prefeitura Municipal de São Paulo',
            'oc_num' => $this->pegaTotalOCs($input),
            'fornecedores_participantes' => $fornecs['total'],
            'vencedores_diferentes' => $fornecs['vencedores'],
            'fornecedores_epp' => $fornecs_epp,
            'fornecedores_me' => $fornecs_me,
            'fornecedores_outros' => $fornecs_outros
        ];

        return $dados;
    }

    private function pegaTotalOCs(array $input)
    {
        return QuerySQL::totalOCs($input['produto'], $input['data_inicial'], $input['data_final']);
    }

    private function pegaTotalFornecedores(array $input)
    {
        $fornecs = QuerySQL::totalFornecedores($input['produto'], $input['data_inicial'], $input['data_final']);
        $dados_formatados = [
            'total' => 0, 'vencedores' => 0
        ];

        foreach ($fornecs as $fornec) {
            if ($fornec->porte == 'Outros') {
                $dados_formatados['total_outros'] = $fornec->total_fornecedores;
            } elseif ($fornec->porte == 'ME') {
                $dados_formatados['total_me'] = $fornec->total_fornecedores;
            } elseif ($fornec->porte == 'EPP') {
                $dados_formatados['total_epp'] = $fornec->total_fornecedores;
            }
            $dados_formatados['total'] += $fornec->total_fornecedores;
            $dados_formatados['vencedores'] += $fornec->total_vencedores;
        }
        $dados_formatados['porcentagem_outros'] =
            Stats::porcentagem($dados_formatados['total'], $dados_formatados['total_outros']);
        $dados_formatados['porcentagem_epp'] =
            Stats::porcentagem($dados_formatados['total'], $dados_formatados['total_epp']);
        $dados_formatados['porcentagem_me'] =
            Stats::porcentagem($dados_formatados['total'], $dados_formatados['total_me']);
        $this->porcentagens_fornecs = $dados_formatados;
        return $dados_formatados;
    }

}
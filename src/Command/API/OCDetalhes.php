<?php
namespace Infoprecos\BEC\Command\API;

use App\OC as OCModel;
use App\Fornecedor as FornecedorModel;
use App\Item as ItemModel;
use App\Proposta as PropostaModel;
use App\Classe as ClasseModel;

use Infoprecos\BEC\Service\Crawler\OCDetalhe;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OCDetalhes extends Command
{
    /**
     * @var Produto
     */
    private $crawler;

    public function __construct()
    {
        parent::__construct();
        $this->crawler = new OCDetalhe();
    }

    public function configure()
    {
        $this->setName('api:ocdetalhes')
            ->setDescription('coleta os detalhes as OCs cadastradas')
            ->setHelp(<<<EOT

<info>php bin/console.php api:ocdetalhes</info>

EOT
            );
    }

    /** FIXME: indicar ocs que nao foram possiveis serem processadas */
	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ocs = OCModel::where('detalhe_processado', 0)->get();
        foreach($ocs as $oc) {
            $ocjson = $this->crawler->getDetalheOC($oc->codigo);
            $ocjson = $ocjson[0];

            $fornecedores = $ocjson->DESC_ATA_GERADAPR->OCCompleta->Fornecedores;
            if(!$fornecedores) {
                continue;
            }

            // parse fornecedores
            $returnFornecedores 
                = $this->fornecedores($fornecedores);

            // itens
            $itens = $ocjson->ITENS;
            $propostasData = $this->itens($itens, $oc, $returnFornecedores, $ocjson);
            
            // propostas
            foreach($propostasData as $itemId => $propostas) {
                foreach($propostas as $proposta) {
                    echo "Cadastrando '{$oc['codigo']}' item '{$proposta['item']}' proposta '{$proposta['valor']}'..." . PHP_EOL;
                    $propostaModel = new PropostaModel();
                    $propostaModel->id_item = $itemId;
                    $propostaModel->id_fornecedor = $proposta['id_fornecedor'];
                    $propostaModel->valor = $proposta['valor'];
                    $propostaModel->save();
                }
            }

            // update oc detalhe processada
            OCModel::where('id', $oc->id)
                ->update(['detalhe_processado' => 1]);
        }
    }

    /* parse itens */
    private function itens($itens, $oc, $returnFornecedores, $ocjson)
    {
        list($returnFornecedor, $returnFornecedor2) = $returnFornecedores;

        $itensData = [];
        foreach($itens as $i) {
            // verifcando item cadastrado
            $itemCheck = ItemModel::where('id_oc', '=', $oc->id)
                ->where('nr_sequencia_item', '=', $i->NR_SEQUENCIA_ITEM) 
                ->first();

            if($itemCheck) {
                // item ja cadastrado
                continue;
            }

            // verificando classe
            $codigo = $i->CD_CLASSE_ITEM;
            $check = ClasseModel::where('codigo', '=', $codigo)->first();

            if(!$check) {
                echo "Cadastrando classe '{$oc['codigo']}' item '{$i->NR_SEQUENCIA_ITEM}' classe '{$i->DESCRICAO_CLASSE}'..." . PHP_EOL;
                $classeModel = new ClasseModel();
                $classeModel->codigo = $codigo;
                $classeModel->descricao = $i->DESCRICAO_CLASSE;
                $classeModel->save();
                $class_id = $classeModel->id;
            } else {
                $class_id = $check->id;
            } 

            $seq = $i->NR_SEQUENCIA_ITEM;
            $data = [];

            $itensData[$seq] = [
                'id_classe' => $class_id,
                'id_oc' => $oc->id,
                'nr_sequencia_item' => $seq,
                'codigo' => $i->CD_ITEM,
                'descricao' => $i->DESCRICAO_ITEM,
                'unidade_fornecimento' => $i->UNIDADE_FORNECIMENTO,
                'quantidade' => $i->QUANTIDADE,
                'menor_valor' => '',
                'id_fornecedor_vencedor' => ''
            ];
        }

        $propostasData = [];
        $ataItens = $ocjson->DESC_ATA_GERADAPR->OCCompleta->AndamentosItensGrupos;
        foreach($ataItens as $ataItem) {
            // pega fornecedor
            $cnpj = $ataItem->FichaItemGrupo->CNPJ;
            $seq = $ataItem->FichaItemGrupo->NrSequencia;

            if(!array_key_exists($seq, $itensData)) {
                continue;
            }

            if(empty(trim($cnpj))) {
                unset($itensData[$seq]);
                continue;
            }
            //var_dump($returnFornecedor, $cnpj);

            $id_fornecedor_vencedor = $returnFornecedor[trim($cnpj)];
            $menorValor = $ataItem->FichaItemGrupo->MenorValor;
            $menorValor = str_replace('.', '', $menorValor);
            $menorValor = str_replace(',', '.', $menorValor);

            $itensData[$seq]['id_fornecedor_vencedor'] = $id_fornecedor_vencedor;
            $itensData[$seq]['menor_valor'] = $menorValor;

            // propostas
            $propostas = $ataItem->ListaPropostas;
            foreach($propostas as $proposta) {
                $valor = $proposta->Valor;
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
                $valor = str_replace('R$ ', '', $valor);
                $valor = trim($valor);

                $licitante = trim($proposta->Licitante);
                if(!isset($returnFornecedor2[md5($licitante)])) {
                    // pula proposta
                    continue;
                }

                $propostasData[$seq][] = [
                    'valor' => $valor,
                    'id_fornecedor' => $returnFornecedor2[md5($licitante)],
                    'item' => $seq,
                ];
            }
        };

        $newPropostasData = [];
        foreach($itensData as $seq => $item) {
            echo "Cadastrando '{$oc['codigo']}' item '{$seq}'..." . PHP_EOL;
            $itemModel = new ItemModel();
            $itemModel->id_classe = $item['id_classe'];
            $itemModel->id_oc = $item['id_oc'];
            $itemModel->nr_sequencia_item = $item['nr_sequencia_item'];
            $itemModel->codigo = $item['codigo'];
            $itemModel->descricao = $item['descricao'];
            $itemModel->unidade_fornecimento = $item['unidade_fornecimento'];
            $itemModel->quantidade = $item['quantidade'];
            $itemModel->menor_valor = $item['menor_valor'];
            $itemModel->id_fornecedor_vencedor = $item['id_fornecedor_vencedor'];
            $itemModel->save();

            $newPropostasData[$itemModel->id] = $propostasData[$seq];
        }

        return $newPropostasData;
    }
    
    /* parse fornecedores */
    private function fornecedores($fornecedores)
    {
        $returnFornecedor = [];
        $returnFornecedor2 = [];

        foreach($fornecedores as $f) {
            $cnpj = preg_replace("/[^0-9]/", "", $f->CNPJ);
            $fcheck = FornecedorModel::where('cnpj', '=', $cnpj)->first();
                
            if($fcheck) {
                // ja cadastrado
                $returnFornecedor[$cnpj] = $fcheck->id;
                $returnFornecedor2[md5($fcheck->nome)] = $fcheck->id;
                continue;
            }    

            $nome = trim($f->Licitante);
            echo "Cadastrando fornecedor '{$nome}'..." . PHP_EOL;
            $model = new FornecedorModel();
            $model->cnpj = $cnpj;
            $model->nome = $nome;
            $model->apelido = $f->Legenda;
            $model->porte = $f->PorteEmpresa;
            $model->save();

            $returnFornecedor[$cnpj] = $model->id;
            $returnFornecedor2[md5($nome)] = $model->id;
        }

        return [ $returnFornecedor, $returnFornecedor2 ];
    }
}
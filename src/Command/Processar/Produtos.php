<?php
namespace Infoprecos\BEC\Command\Processar;

use App\UGE as UGEModel;
use Illuminate\Support\Facades\DB;
use Infoprecos\BEC\Service\Crawler\UGE;
use App\Produto as ProdutoModel;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Produtos extends Command
{
    public function configure()
    {
        $this->setName('processar:produtos')
            ->addArgument('qtd_oc', InputArgument::OPTIONAL, 'Filtro de Busca')
            ->setDescription('sincronizar produtos');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $qtd_oc = $input->getArgument('qtd_oc');
        if($qtd_oc) {
            $qtdeocs = DB::select('select codigo,count(*) as c from itens group by codigo');
            foreach($qtdeocs as $reg) {
                echo "Atualizando {$reg->codigo}..." . PHP_EOL;
                DB::update('UPDATE produtos SET qtd_oc = ? WHERE codigo = ?', [ $reg->c, $reg->codigo ]);
            }

        }
        else {
            $produtos = DB::select('select distinct codigo, descricao from itens');
            foreach($produtos as $produto) {
                echo "inserting {$produto->descricao}..." . PHP_EOL;
                $model = new ProdutoModel();
                $model->codigo = $produto->codigo;
                $model->desc_item = $produto->descricao;
                try {
                    $model->save();
                } catch(\Exception $e) {}
            }
        }
    }
}
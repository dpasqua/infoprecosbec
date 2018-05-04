<?php
namespace Infoprecos\BEC\Command\API;

use App\Produto as ProdutoModel;
use Infoprecos\BEC\Service\Crawler\Produto;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Produtos extends Command
{
    /**
     * @var Produto
     */
    private $crawler;

    public function __construct()
    {
        parent::__construct();
        $this->crawler = new Produto();
    }

    public function configure()
    {
        $this->setName('api:produtos')
            ->setDescription('coleta as infos de Produtos')
            ->addArgument('desc_item', InputArgument::REQUIRED, 'Filtro de Busca')
            ->setHelp(<<<EOT

<info>php bin/console.php api:produtos caneta</info>

EOT
            );
    }

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $desc_item = $input->getArgument('desc_item');

        $list = $this->crawler->getProdutos($desc_item);
        foreach($list as $data) {
        	$p = ProdutoModel::where('codigo', '=', $data->Codigo)->first();
        	if($p) {
        		// ja exite na base
        		//echo "produto '{$data->DescItem}' ja consta na base...";
        		//echo PHP_EOL;
        		continue;
        	}

        	echo "salvando produto {$data->DescItem}..." . PHP_EOL;
        	$model = new ProdutoModel();
        	$model->codigo = $data->Codigo;
        	$model->desc_item = $data->DescItem;
        	$model->qtd_oc = $data->QtdOC;
        	$model->save();
        }
    }
}
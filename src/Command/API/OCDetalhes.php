<?php
namespace Infoprecos\BEC\Command\API;

use App\OC as OCModel;
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

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ocs = OCModel::all();
        foreach($ocs as $oc) {
            $oc = $this->crawler->getDetalheOC($oc->codigo);
            var_dump($oc);
        }
    }
}
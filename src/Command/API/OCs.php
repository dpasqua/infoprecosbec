<?php

namespace Infoprecos\BEC\Command\API;

use App\OC as OCModel;
use App\Situacao;
use App\UGE;
use Infoprecos\BEC\Service\Crawler\OC;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OCs extends Command
{
    /**
     * @var OC
     */
    private $crawler_ocs;

    public function __construct()
    {
        parent::__construct();
        $this->crawler_ocs = new OC();
    }

    public function configure()
    {
        $this->setName('api:ocs')
            ->setDescription('coleta as infos de OCs')
            ->addArgument('dt_inicial', InputArgument::REQUIRED, 'data inicial')
            ->addArgument('dt_final', InputArgument::REQUIRED, 'data final')
            ->setHelp(<<<EOT

<info>php bin/console.php api:ocs ddmmyyyy ddmmyyyy</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dt_ini = $input->getArgument('dt_inicial');
        $dt_fim = $input->getArgument('dt_final');

        $oc_list = $this->crawler_ocs->getOcEncerradas($dt_ini, $dt_fim);
        foreach ($oc_list as $oc) {
            $this->processaOC($oc);
        }
    }

    private function processaOC(\stdClass $oc)
    {
        $oc_model = OCModel::where('codigo', '=', $oc->OC)->first();
        if (!$oc_model) {
            $oc_model = new OCModel();
        }

        $id_situacao = $this->processaSituacao($oc->SITUACAO);
        $id_uge = UGE::where('nome', '=', trim($oc->UNIDADE_COMPRADORA))->first()->id;

        $oc_model->id_uge = $id_uge;
        $oc_model->id_situacao = $id_situacao;
        $oc_model->codigo = $oc->OC;
        $oc_model->procedimento = $oc->PROCEDIMENTO;

        $dt = date_create_from_format('m/d/Y H:i:s A', $oc->DT_ENCERRAMENTO);
        $oc_model->dt_encerramento = $dt->getTimestamp();

        $oc_model->save();
    }

    private function processaSituacao($nome)
    {
        $situacao = Situacao::where('nome', '=', $nome)->first();
        if ($situacao) {
            return $situacao->id;
        }
        $situacao = new Situacao();
        $situacao->nome = $nome;
        $situacao->save();
        return $situacao->id;
    }

}
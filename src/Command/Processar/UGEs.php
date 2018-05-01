<?php

namespace Infoprecos\BEC\Command\Processar;

use App\Gestao;
use App\Municipio;
use App\Orgao;
use App\UGE as UGEModel;
use Infoprecos\BEC\Service\Crawler\UGE;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UGEs extends Command
{
    /**
     * @var UGE
     */
    private $crawler_uges;

    public function __construct()
    {
        parent::__construct();
        $this->crawler_uges = new UGE();
    }

    public function configure()
    {
        $this->setName('processar:uges')
            ->setDescription('coleta as infos de UGEs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $municipios = $this->crawler_uges->getUGEs();

        foreach ($municipios as $municipio) {
            $this->processaMunicipio($municipio);
        }
    }

    private function processaMunicipio(array $municipio) {
        echo "\n\n" . $municipio['codigo'] . " --> " . $municipio['nome'] . "\n";
        foreach ($municipio['orgaos'] as $uc => $uge) {
            if (strpos($municipio['nome'], 'BRASILIA') !== false) {
                continue;
            }
            $this->processaUGE($uc, $uge, $municipio);
        }
    }

    private function processaUGE($uc, $uge, $municipio)
    {
        $municipio_model = Municipio::where('nome', '=', $municipio['nome'])->first();
        if (!$municipio_model) {
            $municipio_model = Municipio::where('nome', 'like', '%' . $municipio['nome'] . '%')->first();
        }
        $municipio_model->codigo = $municipio['codigo'];
        $municipio_model->save();

        $uge_model = new UGEModel();
        $uge_model->id_orgao = $this->processaOrgao($uge['orgao']);
        $uge_model->id_gestao = $this->processaGestao($uge['gestao']);
        $uge_model->id_municipio = $municipio_model->id;
        $uge_model->uc = $uc;
        $uge_model->nome = $uge['nome'];
        $uge_model->endereco = $uge['endereco'];
        $uge_model->save();
    }

    private function processaOrgao($cod_orgao)
    {
        $orgao = Orgao::where('codigo', '=', $cod_orgao)->first();
        if ($orgao) {
            return $orgao->id;
        }
        $orgao = new Orgao();
        $orgao->codigo = $cod_orgao;
        $orgao->save();
        return $orgao->id;
    }

    private function processaGestao($cod_gestao)
    {
        $gestao = Gestao::where('codigo', '=', $cod_gestao)->first();
        if ($gestao) {
            return $gestao->id;
        }
        $gestao = new Gestao();
        $gestao->codigo = $cod_gestao;
        $gestao->save();
        return $gestao->id;
    }

}
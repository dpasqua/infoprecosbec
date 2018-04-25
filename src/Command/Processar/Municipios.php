<?php

namespace Infoprecos\BEC\Command\Processar;

use Infoprecos\BEC\Service\Crawler\Municipios as ServiceMunicipios;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Municipios extends Command
{

    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('processar:municipios')
            ->setDescription('coleta as infos de municipios');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $crawler_municipios = new ServiceMunicipios();
        $dados = $crawler_municipios->getMunicipios();

        foreach ($dados as $regiao) {
            $this->processaRegiao($regiao);
        }

    }

    private function processaRegiao(array $regiao) {
        $dados = [
            'codigo' => $regiao['codigo'],
            'nome' => $regiao['nome']
        ];

        // salvar $dados regiao

        foreach ($regiao['cidades'] as $municipio) {
            // salvar dados $municipio
            echo $municipio;
            // TODO
        }
    }

}
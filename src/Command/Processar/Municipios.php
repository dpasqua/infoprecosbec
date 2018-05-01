<?php

namespace Infoprecos\BEC\Command\Processar;

use App\Municipio;
use App\Regiao;
use Infoprecos\BEC\Service\Char\Formatter;
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
        $regiao_nome = Formatter::maiusculo($regiao['nome']);
        // salvar regiao
        $regiao_model = new Regiao();
        $regiao_model->codigo = $regiao['codigo'];
        $regiao_model->nome = $regiao_nome;
        $regiao_model->save();

        // salvar dados municipios
        foreach ($regiao['cidades'] as $municipio) {
            $nome = Formatter::maiusculo($municipio);

            $municipio_model = new Municipio();
            $municipio_model->id_regiao = $regiao_model->id;
            $municipio_model->nome = $nome;
            $municipio_model->save();
        }
    }

}
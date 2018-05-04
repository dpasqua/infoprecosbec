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

        // deixar municipios com nome igual a site do BEC
        $this->fixMunicipios();
    }

    private function processaRegiao(array $regiao)
    {
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

    // ajustar nomes para ficar como no portal
    private function fixMunicipios()
    {
        $municipio_model = Municipio::where('nome', '=', 'MOGI MIRIM')->first();
        $municipio_model->nome = 'MOGI-MIRIM';
        $municipio_model->save();

        $municipio_model = Municipio::where('nome', '=', 'MOGI GUACU')->first();
        $municipio_model->nome = 'MOGI-GUACU';
        $municipio_model->save();

        $municipio_model = Municipio::where('nome', '=', 'LUIZ ANTONIO')->first();
        $municipio_model->nome = 'LUIS ANTONIO';
        $municipio_model->save();

        $regiao = new Regiao();
        $regiao->codigo = '5301';
        $regiao->nome = 'DISTRITO FEDERAL';
        $regiao->save();

        $municipio_model = new Municipio();
        $municipio_model->id_regiao = $regiao->id;
        $municipio_model->codigo = '9001';
        $municipio_model->nome = 'MUNICIPIO BRASILIA';
        $municipio_model->save();
    }

}
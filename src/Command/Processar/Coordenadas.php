<?php

namespace Infoprecos\BEC\Command\Processar;

use App\UGE as UGEModel;
use Illuminate\Support\Facades\DB;
use Infoprecos\BEC\Service\Crawler\UGE;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Coordenadas extends Command
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
        $this->setName('processar:coordenadas')
            ->setDescription('coleta as infos de UGEs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $uges = UGEModel::where('coordenadas', '=', null)->get();
        foreach ($uges as $uge) {
            $this->processarUGE($uge);
        }
    }

    private function processarUGE(UGEModel $uge)
    {
        // dados complementares
        $info = $this->crawler_uges->getUGEInfo($uge->uc);

        $uge->cep = $info['cep'];
        $uge->email = $info['email'];

        $telefone = $info['primeiro_telefone'];
        if (!empty($info['segundo_telefone'])) {
            $telefone .= ' / ' . $info['segundo_telefone'];
        }
        $uge->telefone = $telefone;

        $uge->fax = $info['fax'];
        $uge->cnpj = $info['cnpj'];

        $uge->save();

        // coordenadas
        $json = $this->crawler_uges->getCoordenadas($uge);
        if (!is_null($json) && property_exists($json, 'location')) {
            $latitude =  $json->location->lat;
            $longitude =  $json->location->lng;
            $cd = $latitude . ' ' . $longitude;

            $uge->coordenadas = DB::raw("GeomFromText('POINT(" . $cd . ")')");
            $uge->save();
        }
    }

}
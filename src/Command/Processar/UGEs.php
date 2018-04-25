<?php

namespace Infoprecos\BEC\Command\Processar;

use Infoprecos\BEC\Service\Crawler\UGE;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UGEs extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('processar:uges')
            ->setDescription('coleta as infos de UGEs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $crawler_uges = new UGE();
        $crawler_uges->getUGEs();

    }
}
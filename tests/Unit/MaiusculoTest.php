<?php

namespace Infoprecos\BEC\Test\Unit;

use Infoprecos\BEC\Service\Char\Formatter;

class MaiusculoTest extends \TestCase
{

    public function testMaiusculo()
    {
        $this->assertEquals('SAO PAULO', Formatter::maiusculo('São Paulo'));
        $this->assertEquals('ARACATUBA', Formatter::maiusculo('Araçatuba'));
        $this->assertEquals('HORTOLANDIA', Formatter::maiusculo('Hortolândia'));
        $this->assertEquals('ITANHAEM', Formatter::maiusculo('Itanhaém'));
        $this->assertEquals('IBIUNA', Formatter::maiusculo('Ibiúna'));
        $this->assertEquals('LENCOIS PAULISTA', Formatter::maiusculo('Lençóis Paulista'));
        $this->assertEquals('JUNDIAI', Formatter::maiusculo('Jundiaí'));
        $this->assertEquals('ALVARO DE CARVALHO', Formatter::maiusculo('Álvaro de Carvalho'));
        $this->assertEquals('SANTO ANDRE', Formatter::maiusculo('Santo André'));
        $this->assertEquals('IPERO', Formatter::maiusculo('Iperó'));
        $this->assertEquals('MOGI-GUACU', Formatter::maiusculo('Mogi-Guaçu'));
    }

}
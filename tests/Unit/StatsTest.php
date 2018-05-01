<?php

namespace Infoprecos\BEC\Test\Unit;

use Infoprecos\BEC\Service\Math\Stats;

class StatsTest extends \TestCase
{

    public function testMedia()
    {
        $arr = [5, 7, 8];
        $this->assertEquals(6.67, Stats::media($arr));

        $arr = ['11', '12', '13', '14'];
        $this->assertEquals(12.5, Stats::media($arr));

        $arr = [122450.0, 143646.0, 123525.0, 175000.0, 152000.5];
        $this->assertEquals(143324.3, Stats::media($arr));

        $arr = [122450.0, '143646.0', 123525.0, 175000.0, '152000.5'];
        $this->assertEquals(143324.3, Stats::media($arr));
    }

}
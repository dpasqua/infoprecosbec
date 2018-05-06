<?php

namespace Infoprecos\BEC\Service\Math;

class Stats
{
    /**
     * calcula a media de um array com n valores
     * @param array $arr
     * @return float
     */
    public static function media(array $arr)
    {
        $sum = array_sum($arr);
        $size = sizeof($arr);
        $media = $sum / $size;
        return round($media, 2);
    }

    public static function porcentagem($total, $parcial)
    {
        if ($parcial == 0) {
            return 0;
        }

        $parcial_100 = $parcial * 100;
        return $parcial_100 / $total;
    }
}
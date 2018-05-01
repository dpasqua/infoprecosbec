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
}
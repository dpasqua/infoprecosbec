<?php

namespace Infoprecos\BEC\Service\Char;


class Formatter
{
    const ACENTOS = ['À', 'Á', 'Â', 'Ã', 'Ç', 'É', 'Ê', 'Í', 'Ó', 'Ô', 'Õ', 'Ú', 'à', 'á', 'â', 'ã', 'ç', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú'];
    const SEM_ACENTOS = ['A', 'A', 'A', 'A', 'C', 'E', 'E', 'I', 'O', 'O', 'O', 'U', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'i', 'o', 'o', 'o', 'u'];

    /**
     * @return string
     * @param $string
     */
    public static function maiusculo($string)
    {
        $string = str_replace(Formatter::ACENTOS, Formatter::SEM_ACENTOS, $string);
        $string = strtoupper($string);
        return $string;
    }

    public static function formataDataParaMySQL($dt)
    {
        return implode('-', array_reverse(explode('/', $dt)));
    }

}
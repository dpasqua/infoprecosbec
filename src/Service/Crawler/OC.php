<?php

namespace Infoprecos\BEC\Service\Crawler;

use GuzzleHttp\Client;

class OC
{
    const URL_ENCERRADA = 'https://www.bec.sp.gov.br/BEC_API/api/pregao_encerrado/OC_Encerrada?dt_inicial={ini}&dt_final={fim}';

    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param string $dt_inicial
     * @param string $dt_final
     * @return array
     */
    public function getOcEncerradas($dt_inicial, $dt_final)
    {
        $url = str_replace(['{ini}', '{fim}'], [$dt_inicial, $dt_final], self::URL_ENCERRADA);

        $request = $this->client->get($url);
        $json = \GuzzleHttp\json_decode($request->getBody()->getContents());
        return $json;
    }

}
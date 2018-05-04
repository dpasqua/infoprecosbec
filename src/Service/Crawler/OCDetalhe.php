<?php

namespace Infoprecos\BEC\Service\Crawler;

use GuzzleHttp\Client;

class OCDetalhe
{
    const URL_ENCERRADA = 'https://www.bec.sp.gov.br/BEC_API/api/pregao_encerrado/OC_Encerrada?OC={OC}';

    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param string oc
     * @return array
     */
    public function getDetalheOC($oc)
    {
        $url = str_replace(['{OC}'], [$oc], self::URL_ENCERRADA);

        $request = $this->client->get($url);
        $json = \GuzzleHttp\json_decode($request->getBody()->getContents());
        return $json;
    }
}
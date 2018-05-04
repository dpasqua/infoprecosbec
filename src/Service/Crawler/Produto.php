<?php

namespace Infoprecos\BEC\Service\Crawler;

use GuzzleHttp\Client;

class Produto
{
    const URL_ENCERRADA = 'https://www.bec.sp.gov.br/BEC_API/api/pregao_encerrado/OC_Encerrada?DescItem={produto}';

    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param string produto
     * @return array
     */
    public function getProdutos($produto)
    {
        $url = str_replace(['{produto}'], [$produto], self::URL_ENCERRADA);

        $request = $this->client->get($url);
        $json = \GuzzleHttp\json_decode($request->getBody()->getContents());
        return $json;
    }
}
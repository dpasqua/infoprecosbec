<?php

namespace Infoprecos\BEC\Service\Crawler;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class UGE
{
    const URL = 'https://www.bec.sp.gov.br/becsp/UGE/UGEPesquisa.aspx?chave=';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var string
     */
    private $eventvalidation;
    private $viewstate;
    private $viewstategenerator;

    public function __construct()
    {
        $this->client = new Client();
        $this->crawler = new Crawler();
    }

    public function getUGEs()
    {
        $request = $this->client->get(self::URL);
        $this->crawler->addHtmlContent($request->getBody()->getContents());

        $this->extractFormParams();



    }


    private function extractFormParams()
    {
        $this->eventvalidation = $this->crawler->filterXPath('//input[@id="__EVENTVALIDATION"]')->attr('value');
        $this->viewstate = $this->crawler->filterXPath('//input[@id="__VIEWSTATE"]')->attr('value');
        $this->viewstategenerator = $this->crawler->filterXPath('//input[@id="__VIEWSTATEGENERATOR"]')->attr('value');
    }
}
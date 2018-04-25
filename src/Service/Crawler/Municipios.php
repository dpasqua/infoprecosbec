<?php

namespace Service\Crawler;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class Municipios
{
    const URL_WIKIPEDIA = 'https://pt.wikipedia.org/wiki/Lista_de_regi%C3%B5es_geogr%C3%A1ficas_imediatas_de_S%C3%A3o_Paulo';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var array
     */
    private $regioes = [];

    public function __construct()
    {
        $this->client = new Client();
        $this->crawler = new Crawler();
    }

    public function getMunicipios()
    {
        $request = $this->client->get(self::URL_WIKIPEDIA);
        $this->crawler->addHtmlContent($request->getBody()->getContents());

        // regioes e codigos
        $spans = $this->crawler->filterXPath('//ul/li[contains(@class, "toclevel-2")]/a/span[2]');
        foreach ($spans as $span) {
            $this->acessaSpan($span);
        }

        // municipios
        $tables = $this->crawler->filterXPath('//table[@class="wikitable sortable"]');
        $index_regiao = 0; // mesma ordem da lista, de acordo com a pagina do wikipedia
        foreach ($tables as $table) {
            $this->acessaTabela($table, $index_regiao++);
        }

        // retorna dados em array
        return $this->regioes;
        /* teste para confirmar
        $total_cidades = 0;
        foreach ($this->regioes as $regiao) {
            $total_cidades += count($regiao['cidades']);
        }
        echo "total de cidades --> " . $total_cidades; // 645
        */
    }

    private function acessaSpan(\DOMElement $span)
    {
        $text = $span->textContent;

        // separa codigo e nome da regiao
        preg_match('/(\d+)/', $text, $match);
        preg_match('/(.*)\(/', $text, $match2);

        $this->regioes[count($this->regioes)] = [
            'codigo' => $match[1],
            'nome' => trim($match2[1]),
            'cidades' => []
        ];
    }

    private function acessaTabela(\DOMElement $table, $index_regiao)
    {
        $trs = $table->getElementsByTagName('tr');
        foreach ($trs as $tr) {
            $tds = $tr->getElementsByTagName('td');
            if (count($tds) == 0) {
                continue; // primeira tr == cabecalho
            }

            // nomes de municipios estao na ultima <td>
            $municipio = trim($tds->item($tds->length - 1)->textContent);
            $this->regioes[$index_regiao]['cidades'][] = $municipio;
        }
    }

}
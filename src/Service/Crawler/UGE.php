<?php

namespace Infoprecos\BEC\Service\Crawler;

use GuzzleHttp\Client;
use Infoprecos\BEC\Service\Parser\BecParser;
use Symfony\Component\DomCrawler\Crawler;

class UGE
{
    const URL = 'https://www.bec.sp.gov.br/becsp/UGE/UGEPesquisa.aspx?chave=';
    const EVENT_TARGET_PESQUISA_AVANCADA = 'ctl00$ContentPlaceHolder1$PesquisaAvancada';
    const EVENT_TARGET_PESQUISAR = 'ctl00$ContentPlaceHolder1$btnPesquisar';

    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->crawler = new Crawler();
    }

    /**
     * retorna array de municipios com orgaos
     * @return array
     */
    public function getUGEs()
    {
        $request = $this->client->get(self::URL);
        $parser = new BecParser($request->getBody()->getContents());

        $parser_form = new BecParser($this->postFormCompleto($parser));
        $municipios = $parser_form->getOptionsMunicipios();

        foreach ($municipios as $k => $municipio) {
            $parser_list = new BecParser($this->postListOrgaosMunicipio($municipio['codigo'], $parser_form));
            $municipios[$k]['orgaos'] = $parser_list->getTableOrgaos();
        }
        return $municipios;
    }

    /**
     * realiza post para trazer formulario com nomes das cidades
     * @param BecParser $parser
     * @return string
     */
    private function postFormCompleto(BecParser $parser)
    {
        $request = $this->client->post(self::URL, [
            'form_params' => [
                '__EVENTARGUMENT' => '',
                '__EVENTTARGET' => self::EVENT_TARGET_PESQUISA_AVANCADA,
                '__VIEWSTATE' => $parser->extractViewState(),
                '__VIEWSTATEGENERATOR' => $parser->extractViewStateGenerator(),
                '__EVENTVALIDATION' => $parser->extractEventValidation(),
                'ctl00_ToolkitScriptManager1_HiddenField' => '',
                'ctl00$ContentPlaceHolder1$txtUge' => '',
            ],
            'headers' => [
                'Referer' => self::URL
            ]
        ]);
        return $request->getBody()->getContents();
    }

    /**
     * realiza post para trazer orgaos de um municipio
     * @param string $cod_municipio
     * @param BecParser $parser
     * @return string
     */
    private function postListOrgaosMunicipio($cod_municipio, BecParser $parser)
    {
        $request = $this->client->post(self::URL, [
            'form_params' => [
                '__EVENTARGUMENT' => '',
                '__EVENTTARGET' => self::EVENT_TARGET_PESQUISAR,
                '__VIEWSTATE' => $parser->extractViewState(),
                '__VIEWSTATEGENERATOR' => $parser->extractViewStateGenerator(),
                '__EVENTVALIDATION' => $parser->extractEventValidation(),
                'ctl00_ToolkitScriptManager1_HiddenField' => '',
                'ctl00$ContentPlaceHolder1$txtUge' => '',
                'ctl00$ContentPlaceHolder1$txtNomeUge' => '',
                'ctl00$ContentPlaceHolder1$cmbMunicipio' => $cod_municipio
            ],
            'headers' => [
                'Referer' => self::URL
            ]
        ]);
        return $request->getBody()->getContents();
    }

}
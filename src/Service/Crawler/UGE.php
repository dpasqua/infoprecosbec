<?php

namespace Infoprecos\BEC\Service\Crawler;

use App\UGE as UGEModel;

use GuzzleHttp\Client;
use Infoprecos\BEC\Service\Parser\BecParser;
use Symfony\Component\DomCrawler\Crawler;

class UGE
{
    const URL = 'https://www.bec.sp.gov.br/becsp/UGE/UGEPesquisa.aspx?chave=';
    const URL_UGE = 'https://www.bec.sp.gov.br/becsp/UGE/UGEResultado.aspx?chave=&CdUge=';

    const GOOGLE_MAPS_API_JSON = 'https://maps.googleapis.com/maps/api/geocode/json?address={address}&key={key}';

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

    /**
     * @param $cod_uge
     * @return array
     */
    public function getUGEInfo($cod_uge)
    {
        $request = $this->client->get(self::URL_UGE . $cod_uge);
        $parser = new BecParser($request->getBody()->getContents());
        return $parser->getTableUGE();
    }

    /**
     * @param UGEModel $uge
     * @return mixed
     */
    public function getCoordenadas(UGEModel $uge)
    {
        $municipio = $uge->municipio()->first();
        $municipio_nome = $municipio->nome;
        $municipio_nome = str_replace(' ', '+', $municipio_nome);

        $url_key = str_replace('{key}', env('GOOGLE_API_KEY'), self::GOOGLE_MAPS_API_JSON);
        $endereco = str_replace(' ', '+', $uge->endereco) . '+' . $municipio_nome;
        $url = str_replace('{address}', $endereco, $url_key);

        $json = $this->requestCoordenadas($url);
        if ($json->status == 'OK') { // != 'ZERO_RESULTS'
            return $json->results[0]->geometry;
        }

        // tenta consultar por nome do orgao
        $endereco = str_replace(' ', '+', $uge->nome) . '+' . $municipio_nome;
        $url = str_replace('{address}', $endereco, $url_key);

        $json = $this->requestCoordenadas($url);
        if ($json->status == 'OK') { // != 'ZERO_RESULTS'
            return $json->results[0]->geometry;
        }

        // tenta consultar endereco e nome
        $endereco = str_replace(' ', '+', $uge->endereco) . '+' .
            str_replace(' ', '+', $uge->nome) . '+' . $municipio_nome;
        $url = str_replace('{address}', $endereco, $url_key);

        $json = $this->requestCoordenadas($url);
        if ($json->status == 'OK') { // != 'ZERO_RESULTS'
            return $json->results[0]->geometry;
        }

        return null;
    }

    /**
     * @param string $url
     * @return mixed
     */
    private function requestCoordenadas($url)
    {
        $request = $this->client->get($url);
        return \GuzzleHttp\json_decode($request->getBody()->getContents());
    }

}
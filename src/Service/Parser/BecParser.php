<?php

namespace Infoprecos\BEC\Service\Parser;

use Symfony\Component\DomCrawler\Crawler;

class BecParser
{
    /*
     * todos os metodos do parser manipulam diferentes instancias da pagina
     * https://www.bec.sp.gov.br/becsp/UGE/UGEPesquisa.aspx?chave=
     */

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * BecParser constructor.
     * @param string $html
     */
    public function __construct($html)
    {
        $this->crawler = new Crawler();
        $this->crawler->addHtmlContent($html);
    }

    /**
     * @return null|string
     */
    public function extractEventValidation()
    {
        return $this->getValue('//input[@id="__EVENTVALIDATION"]');
    }

    /**
     * @return null|string
     */
    public function extractViewState()
    {
        return $this->getValue('//input[@id="__VIEWSTATE"]');
    }

    /**
     * @return null|string
     */
    public function extractViewStateGenerator()
    {
        return $this->getValue('//input[@id="__VIEWSTATEGENERATOR"]');
    }

    private function getValue($xpath)
    {
        return $this->crawler->filterXPath($xpath)->attr('value');
    }

    /**
     * @return array
     */
    public function getOptionsMunicipios()
    {
        $municipios = [];
        $options = $this->crawler->filterXPath('//select[@id="ctl00_ContentPlaceHolder1_cmbMunicipio"]/option');
        foreach ($options as $option) {
            $value = $option->getAttribute('value');
            if ($value == '999' || empty($value)) {
                continue; // A DEFINIR | Todos...
            }
            $municipios[] = [
                'codigo' => trim($value),
                'nome' => trim($option->textContent)
            ];
        }
        return $municipios;
    }

    /**
     * retorna tabela formatada em array de orgaos
     * @return array
     */
    public function getTableOrgaos()
    {
        $orgaos = [];
        $trs = $this->crawler->filterXPath('//table[@id="ctl00_ContentPlaceHolder1_dtgUge"]/tr[position()>1]');
        foreach ($trs as $tr) {
            $tds = $tr->getElementsByTagName('td');
            $uc = trim($tds->item(0)->textContent);
            $orgaos[$uc] = [
                'orgao' => trim($tds->item(1)->textContent),
                'gestao' => trim($tds->item(2)->textContent),
                'nome' => trim($tds->item(3)->textContent),
                'endereco' => trim($tds->item(4)->textContent)
            ];
        }
        return $orgaos;
    }

    /**
     * retorna tabela de dados de uma UC/UGE
     * @return array
     */
    public function getTableUGE()
    {
        $trs = $this->crawler->filterXPath('//div[@id="divTela"]/div/table/tr[position()>2]');
        return [
            'uge' => $trs->eq(0)->filterXPath('//td/input')->eq(0)->attr('value'),
            'orgao' => $trs->eq(1)->filterXPath('//td/input')->eq(0)->attr('value'),
            'gestao' => $trs->eq(2)->filterXPath('//td/input')->eq(0)->attr('value'),
            'nome' => trim($trs->eq(3)->filterXPath('//td/input')->eq(0)->attr('value')),
            'endereco' => trim($trs->eq(4)->filterXPath('//td/input')->eq(0)->attr('value')),
            'municipio' => trim($trs->eq(5)->filterXPath('//td/input')->eq(0)->attr('value')),
            'cep' => trim($trs->eq(6)->filterXPath('//td/input')->eq(0)->attr('value')),
            'email' => trim($trs->eq(7)->filterXPath('//td/input')->eq(0)->attr('value')),
            'primeiro_telefone' => trim($trs->eq(8)->filterXPath('//td/input')->eq(0)->attr('value')),
            'segundo_telefone' => trim($trs->eq(9)->filterXPath('//td/input')->eq(0)->attr('value')),
            'fax' => trim($trs->eq(10)->filterXPath('//td/input')->eq(0)->attr('value')),
            'cnpj' => trim($trs->eq(11)->filterXPath('//td/input')->eq(0)->attr('value')),
        ];
    }

    public function getHtml()
    {
        return $this->crawler->html();
    }

}
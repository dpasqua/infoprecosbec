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

    public function getHtml()
    {
        return $this->crawler->html();
    }

}
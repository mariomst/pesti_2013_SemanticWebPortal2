<?php

/*
 * Helper com as funções:
 * - Carregar ficheiro de configuração;
 * - Carregar queries através de ficheiros;
 * - Conversão de XML para HTML;
 * 
 * Versão 1.0
 * 
 * @author Mário Teixeira   1090626     1090626@isep.ipp.pt
 * @author Marta Graça      1100640     1100640@isep.ipp.pt
 */

function test_helper($arg1) {
    $th = 'O helper recebeu o argumento ' . $arg1;
    return $th;
}

function readConfigFile() {
    //Definição das variáveis a serem usadas.
    $configFile = 'configs/connections.ini';
    $url_fuseki = '';
    $result = array();

    if (!file_exists($configFile)) {
        print_r("<br><font color=\"red\"><b>Erro: O ficheiro de configura&ccedil;&atilde;o connections.ini n&atilde;o foi encontrado na pasta configs!");
        exit;
    } else {
        //Abrir o ficheiro para leitura.
        $readFile = fopen($configFile, "r");
        //Leitura até ao fim do ficheiro.
        while (!feof($readFile)) {
            //Obter a linha a ser processada.
            $line = fgets($readFile);
            //Ignorar comentários no ficheiro de configuração.
            if (strpos($line, "Comentário") == false) {
                $result[] = $line;
            }
        }
        //Fechar o ficheiro.
        fclose($readFile);
        //Remover o que não interessa.
        foreach ($result as $line) {
            $aux = explode("=", $line);
            if ($aux[0] == 'url_fuseki ' || $aux[0] == 'dataset ') {
                $url_fuseki = $url_fuseki . $aux[1];
                //Remover possíveis espaços
                $url_fuseki = preg_replace('/\s+/', '', $url_fuseki);
            }
        }

        return $url_fuseki;
    }
}

function readQueryFile($filename) {
    $queryfile = 'queries/' . $filename . '.query';
    $query = '';

    if (!file_exists($queryfile)) {
        print_r("<br><font color=\"red\"><b>Erro: O ficheiro Query indicado n&atilde;o foi encontrado na pasta queries!");
        exit;
    } else {
        //Abrir o ficheiro para leitura.
        $readFile = fopen($queryfile, "r");
        //Leitura até ao fim do ficheiro.
        while (!feof($readFile)) {
            //Obter a linha a ser processada.
            $line = fgets($readFile);
            $result[] = $line;
        }
        //Fechar o ficheiro.
        fclose($readFile);
        //Retornar uma única string.
        foreach ($result as $line) {
            $query = $query . $line . ' ';
        }
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';
        return $query;
    }
}

function useXSLT($xmlfile, $xslfile) {
    $xsl = new XSLTProcessor;

    $xsl->importStylesheet(DOMDocument::load($xslfile));

    $result = $xsl->transformToXml(simplexml_load_string($xmlfile));

    return $result;
}

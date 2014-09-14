<?php

/*
 * Consult Model
 * - Modelo responsável pelas consultas feitas à ontologia.
 * 
 * Versão 1.1
 * 
 * @author Mário Teixeira   1090626     1090626@isep.ipp.pt
 * @author Marta Graça      1100640     1100640@isep.ipp.pt
 * 
 * Funções Públicas:
 * + __construct              -> construtor.
 * + listClasses              -> recebe um xml com as super classes existentes na ontologia e retorna uma lista.
 * + listSubClasses           -> recebe um xml com as subclasses da classe indicada e retorna uma lista.
 * + selectSubClasses         -> recebe um xml com as subclasses da classe indicada e retorna opções para inserir num select.
 * + getSubClasses            -> recebe um xml com as subclasses da classe indicada e retorna uma tabela.
 * + getMembers               -> recebe um xml com todos os membros da classe indicada.
 * + getProperties            -> recebe um xml com as propriedades existentes na ontologia.
 * + getPropertyRange         -> recebe um xml com o range da propriedade dada.
 * + getPropertyInfo          -> recebe um xml com informações de uma dada propriedade.
 * + getClassProperty         -> recebe um xml com informações de algumas das propriedades da classe.
 * + getMemberProperty        -> recebe um xml com as propriedades de um determinado membro.
 * + getCommentary            -> recebe o comentário associado ao elemento indicado.
 * + getURI                   -> retorna a uri da ontolgia.
 * 
 * Funções Privadas:
 * - SetAddress               -> define o endereço do servidor Fuseki e o repositório a ser utilizado.
 * - getOntologyURI           -> define a URI da ontologia existente no repositório
 * - consultData              -> instruções para efetuar os pedidos ao servidor FUSEKI 
 */

class Consult_Model extends CI_Model {
    
    //Váriáveis Globais
    protected $url_db_consult = ""; //endereço fuseki para consultas.
    protected $ontologyURI = ""; //URI da ontologia.
    
    //Funções Públicas
    public function __construct() {
        parent::__construct();
        $this->load->helper('portal_helper');     
        $this->setAddress();
        $this->getOntologyURI();
    }
    
    public function listClasses($call){
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('consults/query_listClasses');
        
        //Obter a URI completa e adicionar à variável $temVisibilidade
        $temVisibilidade = '<' . $this->ontologyURI . '#temVisibilidade>';
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1 pelo $temVisibilidade
        $transformed_query = str_replace('$argumento1', $temVisibilidade, $query);
        
        //Ficheiro XSL a ser usado para a transformação do XML
        if($call == 0){            
            $xslfile = "assets/xsl/lista_classes(nonUsers).xsl";            
        } else if($call == 1){
            $xslfile = "assets/xsl/lista_classes.xsl";
        } else {
            $xslfile = "assets/xsl/p_topclasses.xsl";
        }     
  
        $xml = $this->consultData($this->url_db_consult, $transformed_query);
        
        if (!$xml) {
            $result = "<br><font color=\"red\"><b>Erro SPARQL: Ocorreu um erro a retornar a informa&ccedil;&atilde;o, verifique se o endere&ccedil;o est&aacute; correcto.</b></font>";
        } else {
            $result = useXSLT($xml, $xslfile);
        }

        return $result;
    }
    
    public function listSubClasses($classParent, $call){
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('consults/query_listSubClasses');
      
        //Obter a URI completa e adicionar à variável $classParentURI e $temVisibilidade
        $classParentURI = '<' . $this->ontologyURI . '#' . $classParent . '>';
        $temVisibilidade = '<' . $this->ontologyURI . '#temVisibilidade>';

        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1 e $argumento2
        $tq1 = str_replace('$argumento1', $classParentURI, $query);
        $tq2 = str_replace('$argumento2', $temVisibilidade, $tq1);
        
        //Ficheiro XSL a ser usado para a transformação do XML
        if ($call == 0) {
            $xslfile = "assets/xsl/lista_subclasses(nonUsers).xsl";          
        } else if ($call == 1) {
            $xslfile = "assets/xsl/lista_subclasses.xsl";    
        }
        
        $xml = $this->consultData($this->url_db_consult, $tq2);
        
        if (!$xml) {
            $result = "<br><font color=\"red\"><b>Erro SPARQL: Ocorreu um erro a retornar a informa&ccedil;&atilde;o, verifique se o endere&ccedil;o est&aacute; correcto.</b></font>";
        } else {
            $result = useXSLT($xml, $xslfile);
        }

        return $result;
    }
    
    public function selectSubClasses($classParent){
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('consults/query_selectSubClasses');
      
        //Obter a URI completa e adicionar a variável $classParentURI
        $classParentURI = '<' . $this->ontologyURI . '#' . $classParent . '>';

        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1
        $tq = str_replace('$argumento1', $classParentURI, $query);
        
        //Ficheiro XSL a ser usado para a transformação do XML
        $xslfile = "assets/xsl/select_subclasses.xsl";
        
        $xml = $this->consultData($this->url_db_consult, $tq);
        
        if (!$xml) {
            $result = "<br><font color=\"red\"><b>Erro SPARQL: Ocorreu um erro a retornar a informa&ccedil;&atilde;o, verifique se o endere&ccedil;o est&aacute; correcto.</b></font>";
        } else {
            $result = useXSLT($xml, $xslfile);
        }

        return $result;
    }
    
    public function getSubClasses($classParent, $call){
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('consults/query_getSubClasses');
      
        //Obter a URI completa e adicionar a variável $classParentURI
        $classParentURI = '<' . $this->ontologyURI . '#' . $classParent . '>';

        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1
        $tq = str_replace('$argumento1', $classParentURI, $query);
        
        //Ficheiro XSL a ser usado para a transformação do XML
        if ($call == 0) {
            $xslfile = "assets/xsl/tabela_subclasses(nonUsers).xsl";
        } else {
            $xslfile = "assets/xsl/tabela_subclasses.xsl";                    
        }
        
        $xml = $this->consultData($this->url_db_consult, $tq);
        
        if (!$xml) {
            $result = "<br><font color=\"red\"><b>Erro SPARQL: Ocorreu um erro a retornar a informa&ccedil;&atilde;o, verifique se o endere&ccedil;o est&aacute; correcto.</b></font>";
        } else {
            $result = useXSLT($xml, $xslfile);
        }

        return $result;
    }

    public function getMembers($classParent, $call){
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('consults/query_getMembers');
      
        //Obter a URI completa e adicionar a variável $classParentURI
        $classParentURI = '<' . $this->ontologyURI . '#' . $classParent . '>';
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1
        $tq = str_replace('$argumento1', $classParentURI, $query);

        //Ficheiro XSL a ser usado para a transformação do XML
        if ($call == 0) {
            $xslfile = "assets/xsl/tabela_membros(nonUsers).xsl";
        } else if ($call == 1) {
            $xslfile = "assets/xsl/tabela_membros.xsl";    
        } else if ($call == 2) {
            $xslfile = 'assets/xsl/select_membros.xsl';  
        } else {
            $xslfile = 'assets/xsl/lista_membros.xsl';     
        }
        
        $xml = $this->consultData($this->url_db_consult, $tq);
        
        if (!$xml) {
            $result = "<br><font color=\"red\"><b>Erro SPARQL: Ocorreu um erro a retornar a informa&ccedil;&atilde;o, verifique se o endere&ccedil;o est&aacute; correcto.</b></font>";
        } else {
            $result = useXSLT($xml, $xslfile);
        }

        return $result;
    }

    public function getProperties(){
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('consults/query_getProperties');                
        
        //Ficheiro XSL a ser usado para a transformação do XML
        $xslfile = "assets/xsl/checkboxes_propriedades.xsl"; 
  
        $xml = $this->consultData($this->url_db_consult, $query);
        
        if (!$xml) {
            $result = "<br><font color=\"red\"><b>Erro SPARQL: Ocorreu um erro a retornar a informa&ccedil;&atilde;o, verifique se o endere&ccedil;o est&aacute; correcto.</b></font>";
        } else {
            $result = useXSLT($xml, $xslfile);
        }

        return $result;
    }
    
    public function getPropertyRange($property, $call){
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('consults/query_getPropertyRange');  
        
        //Obter a URI completa e adicionar a variável $propertyURI
        $propertyURI = '<' . $this->ontologyURI . '#' . $property . '>';
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1
        $tq = str_replace('$argumento1', $propertyURI, $query);        
  
        $xml = $this->consultData($this->url_db_consult, $tq);
        
        //Obter o tipo de propriedade
        $literal2 = explode("<binding name=\"Tipo\">", $xml);
        $aux2 = $literal2[1];
        $getLiteral2 = explode("</binding>", $aux2);
        $getLiteral3 = explode("<literal>", $getLiteral2[0]);
        $getLiteral4 = explode("</literal>", $getLiteral3[1]);

        //Obter o range da propriedade
        $literal = explode("<binding name=\"AlgunsValoresDe\">", $xml);
        $aux = $literal[1];
        $getLiteral = explode("</binding>", $aux);
        $getLiteral1 = explode("<literal>", $getLiteral[0]);
        $getLiteral11 = explode("</literal>", $getLiteral1[1]);

        if ($getLiteral4[0] == "DatatypeProperty") {
            return "DatatypeProperty-" . $getLiteral11[0];
        } else {
            if ($call == 1) {
                $membros = $this->getMembers($getLiteral11[0], 2);
                return $membros;
            } else if ($call == 2) {
                return $getLiteral11[0];
            }
        }
    }
    
    public function getPropertyInfo($property){
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('consults/query_getPropertyInfo');  
        
        //Obter a URI completa e adicionar a variável $propertyURI
        $propertyURI = '<' . $this->ontologyURI . '#' . $property . '>';
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1
        $tq = str_replace('$argumento1', $propertyURI, $query);
        
        //Ficheiro XSL a ser usado para a transformação do XML
        $xslfile = "assets/xsl/informacoes_propriedade.xsl";
        
        $xml = $this->consultData($this->url_db_consult, $tq);
        
        if (!$xml) {
            $result = "<br><font color=\"red\"><b>Erro SPARQL: Ocorreu um erro a retornar a informa&ccedil;&atilde;o, verifique se o endere&ccedil;o est&aacute; correcto.</b></font>";
        } else {
            $result = useXSLT($xml, $xslfile);
        }

        return $result;              
    }
    
    public function getClassProperty($classParent, $call){
        //Carregar query através do ficheiro externo indicado.
        $query1 = readQueryFile('consults/query_getSimpleClassProperties');  
        $query2 = readQueryFile('consults/query_getComplexClassProperties');
        
        //Obter a URI completa e adicionar a variável $classParentURI
        $classParentURI = '<' . $this->ontologyURI . '#' . $classParent . '>';
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1
        $tq1 = str_replace('$argumento1', $classParentURI, $query1);
        $tq2 = str_replace('$argumento1', $classParentURI, $query2);
        
        //Ficheiro XSL a ser usado para a transformação do XML
        if ($call == 0) {
            $xslfile1 = "assets/xsl/tabela_propriedades_classes(nonUsers).xsl";   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.
            $xslfile2 = "assets/xsl/tabela_propriedades_classes(nonUsers)2.xsl";   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.
        } else {
            $xslfile1 = "assets/xsl/tabela_propriedades_classes.xsl";
            $xslfile2 = "assets/xsl/tabela_propriedades_classes2.xsl";
        }
        
        $xml1 = $this->consultData($this->url_db_consult, $tq1);
        $xml2 = $this->consultData($this->url_db_consult, $tq2);
        
        if (!$xml1 || !$xml2) {
            $result = "<br><font color=\"red\"><b>Erro SPARQL: Ocorreu um erro a retornar a informa&ccedil;&atilde;o, verifique se o endere&ccedil;o est&aacute; correcto.</b></font>";
        } else {
            $result1 = useXSLT($xml1, $xslfile1);
            $result2 = useXSLT($xml2, $xslfile2);
        }

        $result = '<table border=1>';
        $result = $result . $result1 . $result2;
        $result = $result . '</table>';

        return $result;
    }
    
    public function getMemberProperty($member, $call){
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('consults/query_getMembersProperty');
      
        //Obter a URI completa e adicionar a variável $memberURI
        $memberURI = '<' . $this->ontologyURI . '#' . $member . '>';
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1
        $tq = str_replace('$argumento1', $memberURI, $query);

        //Ficheiro XSL a ser usado para a transformação do XML
        if ($call == 0) {
            $xslfile = "assets/xsl/tabela_propriedades(nonUsers).xsl";
        } else if ($call == 1) {
            $xslfile = "assets/xsl/tabela_propriedades.xsl";
        } else {
            $xslfile = "assets/xsl/lista_propriedadesMembros.xsl";
        }
        
        $xml = $this->consultData($this->url_db_consult, $tq);
        
        if (!$xml) {
            $result = "<br><font color=\"red\"><b>Erro SPARQL: Ocorreu um erro a retornar a informa&ccedil;&atilde;o, verifique se o endere&ccedil;o est&aacute; correcto.</b></font>";
        } else {
            $result = useXSLT($xml, $xslfile);
        }

        return $result;
    }
    
    public function getComment($subject){
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('consults/query_getComment');
      
        //Obter a URI completa e adicionar a variável $subjectURI
        $subjectURI = '<' . $this->ontologyURI . '#' . $subject . '>';
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1
        $tq = str_replace('$argumento1', $subjectURI, $query);
        
        //variável XML recebe o resultado da query
        $xml = $this->consultData($this->url_db_consult, $tq);
        
        //retirar o comentário do XML
        if (strpos($xml, 'literal') !== false) {
            $getComment1 = explode("<literal>", $xml);          // -> primeiro explode (split em JS) que vai procurar no XML todas as ocurrências de <literal>.
            $getComment2 = explode("<", $getComment1[1]);        // -> voltamos a fazer explode para remover o codigo xml que restou.
            $result = htmlentities($getComment2[0]);
        } else {
            $result = "<font color=\"red\">N&atilde;o foi encontrado nenhum coment&aacute;rio...</font>";
        }
        
        return $result;
    }
    
    public function getURI(){
        return $this->ontologyURI;    
    }
    
    //Funções Privadas
    private function setAddress(){
        $url = readConfigFile();        
        $this->url_db_consult = $url . "/sparql";        
    }
    
    private function getOntologyURI(){
        $query = readQueryFile('consults/query_obtainURI');
        
        //Variável XML recebe o resultado da query
        $xml = $this->consultData($this->url_db_consult, $query);
        
        //Retirar a URI do resultado XML
        //Primeiro explode (split em JS) que vai procurar no XML todas as ocurrências de <uri>.
        $getfullURI = explode("<uri>", $xml);
        //Em príncipio todas as classe mae pertencem a mesma ontologia, logo só nos interessa a que esta na posição 1 do array retornado pelo explode anterior.
        $fullURI = $getfullURI[1];
        //Voltamos a fazer um explode para obter apenas o URI da ontologia (ex: http://www.semanticweb.org/ontologies/2012/3/Ontology1334263618896.owl).
        $getURI = explode("#", $fullURI);

        $this->ontologyURI = $getURI[0];
    }
    
    private function consultData($url, $query){
        $post = curl_init();
        $fields = 'query=' . $query;
        
        curl_setopt($post, CURLOPT_URL, $url);
        curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($post, CURLOPT_CONNECTTIMEOUT, 2);
        
        $response = curl_exec($post);
        
        curl_close($post);
        
        return $response;
    }
}
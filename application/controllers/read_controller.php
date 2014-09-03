<?php

/*
 * Read Controller
 * - Controller responsável pelas consultas feitas à ontologia.
 * 
 * Versão 1.0
 * 
 * @author Mário Teixeira   1090626     1090626@isep.ipp.pt
 * @author Marta Graça      1100640     1100640@isep.ipp.pt
 * 
 * =============================   Descrição: ==================================
 * Funções Públicas:
 * + __construct              -> construtor.
 * + getFusekiAddress         -> retorna o url do Servidor Fuseki.
 * + checkFusekiStatus        -> Verifica se o servidor Fuseki esta vivo fazendo um pedido GET.
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
 * + printURI                 -> imprime a uri da ontolgia.
 * 
 * Funções Privadas:
 * - readConfigFile           -> carrega o endereço do servidor Fuseki apartir de um ficheiro .ini.
 * - sendQuery                -> envio da query para o Fuseki (esse processo é tratado pelo modelo).
 * - getURI                   -> retorna de forma dinamica a uri da ontologia.
 * - getTipoDatatype          -> retorna o tipo de datatype da propriedade indicada.
 * - useXSLT                  -> carrega o xsl indicado e processa à transformação do xml indicado.
 */

//Configurações do PHP
error_reporting(1);         // -> 0 - desactivo; 1 - activo.

class Read_Controller extends CI_Controller {

    //================= Variaveis Globais ===================//
    protected $url_db_consult = "";     // -> endereço do Fuseki para consultas
    protected $url_db_insert = "";      // -> endereço do Fuseki para inserções

    //================= Funções Públicas ====================//    
    public function __construct() {
        parent::__construct();
        $this->load->model('pesti_model');
        $this->readConfigFile();
    }

    /**********************************************************
     *                   CONSULTAS FUSEKI                     *
     **********************************************************/
    public function listClasses($chamada) {
        /*
          SPARQL QUERY:

          PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#>
          PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
          PREFIX a: <http://www.w3.org/2002/07/owl#>
          PREFIX : <http://www.semanticweb.org/ontologies/2012/3/Ontology1334263618896.owl#>

          SELECT ?classeMae (strafter(str(?classeMae), "#") AS ?localName) (STR(?v) AS ?visivel)

          WHERE{
          {
          ?classeMae rdfns:type a:Class .
          ?classeMae :temVisibilidade ?v.
          FILTER (!isBlank(?classeMae))
          FILTER NOT EXISTS{
          {?classeMae rdf:subClassOf ?classe .
          ?classe rdfns:type a:Class .}
          UNION
          {?classeMae a:equivalentClass ?classe2}
          }
          }UNION{
          ?classeMae rdfns:type a:Class .
          FILTER NOT EXISTS {?classeMae :temVisibilidade ?v.}
          FILTER (!isBlank(?classeMae))
          FILTER NOT EXISTS{
          {?classeMae rdf:subClassOf ?classe .
          ?classe rdfns:type a:Class .}
          UNION
          {?classeMae a:equivalentClass ?classe2}
          }
          }
          }
         */

        //Obter a URI completa e adicionar a variável $classeMae
        $ontologyURI = $this->getURI();
        $temVisibilidade = '<' . $ontologyURI . '#temVisibilidade>';

        $query = 'PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX a: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'SELECT ?classeMae (strafter(str(?classeMae), "#") AS ?localName) (STR(?v) AS ?visivel) ';
        $query = $query . 'WHERE { { ?classeMae rdfns:type a:Class . ';
        $query = $query . '?classeMae ' . $temVisibilidade . ' ?v. ';
        $query = $query . 'FILTER (!isBlank(?classeMae)) ';
        $query = $query . 'FILTER NOT EXISTS{ ';
        $query = $query . '{?classeMae rdf:subClassOf ?classe. ';
        $query = $query . '?classe rdfns:type a:Class.} ';
        $query = $query . 'UNION {?classeMae a:equivalentClass ?classe2}}} ';
        $query = $query . 'UNION { ?classeMae rdfns:type a:Class . ';
        $query = $query . 'FILTER NOT EXISTS {?classeMae ' . $temVisibilidade . ' ?v.} ';
        $query = $query . 'FILTER (!isBlank(?classeMae)) ';
        $query = $query . 'FILTER NOT EXISTS{ ';
        $query = $query . '{?classeMae rdf:subClassOf ?classe. ';
        $query = $query . '?classe rdfns:type a:Class. }';
        $query = $query . 'UNION {?classeMae a:equivalentClass ?classe2}}}}';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        if ($chamada == 0) {
            //Ficheiro XSL a ser usado para a transformação do XML
            $xslfile = "http://localhost/assets/xsl/lista_classes(nonUsers).xsl";     // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
            //Enviar a query e o ficheiro XSL para o método privado
            $result = $this->sendQuery($query, $xslfile);
            print_r($result);
        } else if ($chamada == 1) {
            //Ficheiro XSL a ser usado para a transformação do XML
            $xslfile = "http://localhost/assets/xsl/lista_classes.xsl";     // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
            //Enviar a query e o ficheiro XSL para o método privado
            $result = $this->sendQuery($query, $xslfile);
            print_r($result);
        } else {
            //Ficheiro XSL a ser usado para a transformação do XML
            $xslfile = "http://localhost/assets/xsl/p_topclasses.xsl";     // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
            //Enviar a query e o ficheiro XSL para o método privado
            $result = $this->sendQuery($query, $xslfile);
            print_r($result);
        }
    }

    public function listSubClasses($classeMae, $chamada) {
        /*
          SPARQL QUERY:

          PREFIX : <http://www.semanticweb.org/ontologies/2012/3/Ontology1334263618896.owl#>
          PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#>
          PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
          PREFIX a: <http://www.w3.org/2002/07/owl#>

          select distinct ?subClasse (strafter(str(?subClasse), "#") AS ?localName) (STR(?v) AS ?visivel)

          where{
          {
          {?subClasse rdf:subClassOf ?classe .
          ?subClasse :temVisibilidade ?v.
          ?classe rdfns:type a:Class .}
          UNION
          {?subClasse rdf:subClassOf ?classe .
          FILTER NOT EXISTS {?subClasse :temVisibilidade ?v.}
          ?classe rdfns:type a:Class .}
          }
          UNION
          {
          {
          ?subClasse a:equivalentClass ?classe1 .
          ?classe1 a:intersectionOf ?classe2 .
          ?classe2 rdfns:first ?classe .
          ?classe rdfns:type a:Class .
          ?subClasse :temVisibilidade ?v.
          }
          UNION
          {
          ?subClasse a:equivalentClass ?classe1 .
          ?classe1 a:intersectionOf ?classe2 .
          ?classe2 rdfns:first ?classe .
          ?classe rdfns:type a:Class .
          FILTER NOT EXISTS {?subClasse :temVisibilidade ?v.}
          }
          }
          }
         */

        //Obter a URI completa e adicionar a variável $classeMae.
        $ontologyURI = $this->getURI();
        $fullURI = '<' . $ontologyURI . '#' . $classeMae . '>';
        //Obter a URI completa e adicionar a propriedade temVisibilidade.
        $temVisibilidade = '<' . $ontologyURI . '#temVisibilidade>';

        $query = 'PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX a: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'select distinct ?subClasse (strafter(str(?subClasse), "#") AS ?localName) (STR(?v) AS ?visivel) ';
        $query = $query . 'where{{ ';
        $query = $query . '{?subClasse rdf:subClassOf ' . $fullURI . '. ';
        $query = $query . '?subClasse ' . $temVisibilidade . ' ?v. ';
        $query = $query . $fullURI . ' rdfns:type a:Class.} ';
        $query = $query . 'UNION ';
        $query = $query . '{?subClasse rdf:subClassOf ' . $fullURI . '. ';
        $query = $query . 'FILTER NOT EXISTS {?subClasse ' . $temVisibilidade . ' ?v.} ';
        $query = $query . $fullURI . ' rdfns:type a:Class.} ';
        $query = $query . '}UNION{{ ';
        $query = $query . '?subClasse a:equivalentClass ?classe1. ';
        $query = $query . '?classe1 a:intersectionOf ?classe2. ';
        $query = $query . '?classe2 rdfns:first ' . $fullURI . '. ';
        $query = $query . $fullURI . ' rdfns:type a:Class. ';
        $query = $query . '?subClasse ' . $temVisibilidade . ' ?v. ';
        $query = $query . '}UNION{ ';
        $query = $query . '?subClasse a:equivalentClass ?classe1. ';
        $query = $query . '?classe1 a:intersectionOf ?classe2. ';
        $query = $query . '?classe2 rdfns:first ' . $fullURI . '. ';
        $query = $query . $fullURI . ' rdfns:type a:Class. ';
        $query = $query . 'FILTER NOT EXISTS {?subClasse ' . $temVisibilidade . ' ?v.}}}}';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //Ficheiro XSL a ser usado para a transformação do XML
        if ($chamada == 0) {
            $xslfile = "http://localhost/assets/xsl/lista_subclasses(nonUsers).xsl";      // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML            
        } else if ($chamada == 1) {
            $xslfile = "http://localhost/assets/xsl/lista_subclasses.xsl";  // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML           
        }

        //Enviar a query e o ficheiro XSL para o método privado
        $result = $this->sendQuery($query, $xslfile);
        print_r($result);
    }

    public function selectSubClasses($classeMae) {
        /*
          SPARQL QUERY:

          PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#>
          PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
          PREFIX a: <http://www.w3.org/2002/07/owl#>

          select ?subClasse (strafter(str(?subClasse), "#") AS ?localName)
          where{
          {?subClasse rdf:subClassOf ?classe .
          ?classe rdfns:type a:Class .}

          UNION

          {?subClasse a:equivalentClass ?classe1 .
          ?classe1 a:intersectionOf ?classe2 .
          ?classe2 rdfns:first ?classe .
          ?classe rdfns:type a:Class .}
          }
         */

        //Obter a URI completa e adicionar a variável $classeMae
        $ontologyURI = $this->getURI();
        $fullURI = '<' . $ontologyURI . '#' . $classeMae . '>';

        $query = 'PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX a: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'select ?subClasse (strafter(str(?subClasse), "#") AS ?localName) ';
        $query = $query . 'where{ ';
        $query = $query . '{?subClasse rdf:subClassOf ' . $fullURI . '. ';
        $query = $query . $fullURI . ' rdfns:type a:Class.} ';
        $query = $query . 'UNION ';
        $query = $query . '{?subClasse a:equivalentClass ?classe1. ';
        $query = $query . '?classe1 a:intersectionOf ?classe2. ';
        $query = $query . '?classe2 rdfns:first ' . $fullURI . '. ';
        $query = $query . $fullURI . ' rdfns:type a:Class.}}';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //Ficheiro XSL a ser usado para a transformação do XML
        $xslfile = "http://localhost/assets/xsl/select_subclasses.xsl";     // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
        //Enviar a query e o ficheiro XSL para o método privado
        $result = $this->sendQuery($query, $xslfile);

        print_r($result);
    }

    public function getSubClasses($classeMae, $chamada) {
        /*
          SPARQL QUERY:

          PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#>
          PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
          PREFIX a: <http://www.w3.org/2002/07/owl#>

          select ?subClasse (strafter(str(?subClasse), "#") AS ?localName)
          where{
          {?subClasse rdf:subClassOf ?classe .
          ?classe rdfns:type a:Class .}

          UNION

          {?subClasse a:equivalentClass ?classe1 .
          ?classe1 a:intersectionOf ?classe2 .
          ?classe2 rdfns:first ?classe .
          ?classe rdfns:type a:Class .}
          }
         */

        //Obter a URI completa e adicionar a variável $classeMae
        $ontologyURI = $this->getURI();
        $fullURI = '<' . $ontologyURI . '#' . $classeMae . '>';

        $query = 'PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX a: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'select ?subClasse (strafter(str(?subClasse), "#") AS ?localName) ';
        $query = $query . 'where{ ';
        $query = $query . '{?subClasse rdf:subClassOf ' . $fullURI . '. ';
        $query = $query . $fullURI . ' rdfns:type a:Class.} ';
        $query = $query . 'UNION ';
        $query = $query . '{?subClasse a:equivalentClass ?classe1. ';
        $query = $query . '?classe1 a:intersectionOf ?classe2. ';
        $query = $query . '?classe2 rdfns:first ' . $fullURI . '. ';
        $query = $query . $fullURI . ' rdfns:type a:Class.}}';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //Ficheiro XSL a ser usado para a transformação do XML
        if ($chamada == 0) {
            $xslfile = "http://localhost/assets/xsl/tabela_subclasses(nonUsers).xsl";     // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
        } else {
            $xslfile = "http://localhost/assets/xsl/tabela_subclasses.xsl";     // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML                     
        }

        //Enviar a query e o ficheiro XSL para o método privado
        $result = $this->sendQuery($query, $xslfile);
        print_r($result);
    }

    public function getMembers($classe, $chamada) {
        /*
          SPARQL QUERY:

          PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#>
          PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
          PREFIX a: <http://www.w3.org/2002/07/owl#>

          select ?membro (strafter(str(?membro), "#") AS ?localName)
          where{
          ?membro rdfns:type a:NamedIndividual .
          ?membro rdfns:type ?classe .
          ?classe rdfns:type a:Class .
          }
         */

        //Obter a URI completa e adicionar a variável $classe
        $ontologyURI = $this->getURI();
        $fullURI = '<' . $ontologyURI . '#' . $classe . '>';

        $query = 'PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX a: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'select ?membro (strafter(str(?membro), "#") AS ?localName) ';
        $query = $query . 'where{ ?membro rdfns:type a:NamedIndividual .';
        $query = $query . '?membro rdfns:type ' . $fullURI . '.';
        $query = $query . $fullURI . ' rdfns:type a:Class .}';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //Ficheiro XSL a ser usado para a transformação do XML
        if ($chamada == 0) {
            $xslfile = "http://localhost/assets/xsl/tabela_membros(nonUsers).xsl";
        } else if ($chamada == 1) {
            $xslfile = "http://localhost/assets/xsl/tabela_membros.xsl";    // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
        } else if ($chamada == 2) {
            $xslfile = 'http://localhost/assets/xsl/select_membros.xsl';    // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
        } else {
            $xslfile = 'http://localhost/assets/xsl/lista_membros.xsl';     // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
        }

        //Enviar a query e o ficheiro XSL para o método privado
        $result = $this->sendQuery($query, $xslfile);

        print_r($result);
    }

    public function getProperties() {
        /*
          SPARQL QUERY:

          PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#>
          PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
          PREFIX owl: <http://www.w3.org/2002/07/owl#>

          select distinct
          (strafter(str(?prop), "#") AS ?Propriedades)
          where
          {
          ?naoInteressa owl:onProperty ?prop.
          }
         */

        $query = 'PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX owl: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'select distinct (strafter(str(?prop), "#") AS ?Propriedades) (strafter(str(?car), "#") AS ?Caracteristica) ';
        $query = $query . 'where{{?prop rdfns:type owl:ObjectProperty. ';
        $query = $query . '{?prop rdfns:type owl:FunctionalProperty. BIND(owl:FunctionalProperty AS ?car).} ';
        $query = $query . 'UNION{FILTER NOT EXISTS{?prop rdfns:type owl:FunctionalProperty.}}} ';
        $query = $query . 'UNION{?prop rdfns:type owl:DatatypeProperty. BIND(owl:DatatypeProperty AS ?value). ';
        $query = $query . '{?prop rdfns:type owl:FunctionalProperty. BIND(owl:FunctionalProperty AS ?car).} ';
        $query = $query . 'UNION{FILTER NOT EXISTS{?prop rdfns:type owl:FunctionalProperty.}}}} ';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //Ficheiro XSL a ser usado para a transformação do XML
        $xslfile = "http://localhost/assets/xsl/checkboxes_propriedades.xsl"; // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
        //Enviar a query e o ficheiro XSL para o método privado
        $result = $this->sendQuery($query, $xslfile);

        print_r($result);
    }

    public function getPropertyRange($property, $chamada) {
        /*
          SPARQL QUERY:

          PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#>
          PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
          PREFIX owl: <http://www.w3.org/2002/07/owl#>

          select
          (strafter(str(?value), "#") AS ?AlgunsValoresDe)
          (strafter(str(?type), "#") AS ?Tipo)

          where{
          {
          ?prop rdfns:type owl:ObjectProperty.
          BIND(owl:ObjectProperty AS ?type).
          {?prop rdf:range ?value.}
          UNION
          {FILTER NOT EXISTS{?prop rdf:range ?value.}}
          }
          UNION
          {
          ?prop rdfns:type owl:DatatypeProperty.
          BIND(owl:DatatypeProperty AS ?type).
          {?prop rdf:range ?value.}
          UNION
          {FILTER NOT EXISTS{?prop rdf:range ?value.}}
          }
          }
         */

        //Obter a URI completa e adicionar a variável $classeMae
        $ontologyURI = $this->getURI();
        $fullURI = '<' . $ontologyURI . '#' . $property . '>';

        $query = 'PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX owl: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'select (strafter(str(?value), "#") AS ?AlgunsValoresDe) (strafter(str(?type), "#") AS ?Tipo) ';
        $query = $query . 'where{{' . $fullURI . ' rdfns:type owl:ObjectProperty. BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . '{' . $fullURI . ' rdf:range ?value.}UNION{FILTER NOT EXISTS{' . $fullURI . ' rdf:range ?value.}}} ';
        $query = $query . 'UNION{' . $fullURI . ' rdfns:type owl:DatatypeProperty. BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . '{' . $fullURI . ' rdf:range ?value.}UNION{FILTER NOT EXISTS{' . $fullURI . ' rdf:range ?value.}}}} ';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //variável XML recebe o resultado da query obtido do método presente no modelo
        $xml = $this->pesti_model->consultar_data($this->url_db_consult, $query);

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
            print_r("DatatypeProperty-" . $getLiteral11[0]);
        } else {
            if ($chamada == 1) {
                $membros = $this->getMembers($getLiteral11[0], 2);
                print_r($membros);
            } else if ($chamada == 2) {
                print_r($getLiteral11[0]);
            }
        }
    }

    public function getPropertyInfo($property) {
        //Obter a URI completa e adicionar a variável $classeMae
        $ontologyURI = $this->getURI();
        $fullURI = '<' . $ontologyURI . '#' . $property . '>';

        $query = 'PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#> PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> PREFIX owl: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'select distinct (strafter(str(?ran), "#") AS ?Range) (strafter(str(?type), "#") AS ?Tipo) (strafter(str(?char), "#") AS ?Caracteristicas) (strafter(str(?prop2), "#") AS ?Propriedade2) ';
        $query = $query . 'where{{{ ' . $fullURI . ' rdfns:type owl:ObjectProperty. BIND(owl:ObjectProperty AS ?type). { ' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION {FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}} FILTER NOT EXISTS{ ' . $fullURI . ' rdfns:type owl:FunctionalProperty.}. ';
        $query = $query . 'FILTER NOT EXISTS{ ' . $fullURI . ' rdfns:type owl:TransitiveProperty.}. FILTER NOT EXISTS{ ' . $fullURI . ' rdfns:type owl:SymmetricProperty.}. ';
        $query = $query . 'FILTER NOT EXISTS{ ' . $fullURI . ' rdfns:type owl:AsymmetricProperty.}. FILTER NOT EXISTS{ ' . $fullURI . ' rdfns:type owl:InverseFunctionalProperty.}. ';
        $query = $query . 'FILTER NOT EXISTS{ ' . $fullURI . ' rdfns:type owl:IrreflexiveProperty.}. FILTER NOT EXISTS{ ' . $fullURI . ' rdfns:type owl:ReflexiveProperty.}. ';
        $query = $query . 'FILTER NOT EXISTS{ ' . $fullURI . ' owl:inverseOf ?prop2.}. FILTER NOT EXISTS{ ' . $fullURI . ' owl:equivalentProperty ?prop2.}. ';
        $query = $query . 'FILTER NOT EXISTS{ ' . $fullURI . ' rdf:subPropertyOf ?prop2.}. }UNION{ ' . $fullURI . ' rdfns:type owl:ObjectProperty. BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . ' rdfns:type owl:FunctionalProperty. BIND(owl:FunctionalProperty AS ?char). { ' . $fullURI . ' rdf:range ?ran.} UNION {FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}} ';
        $query = $query . 'UNION{ ' . $fullURI . ' rdfns:type owl:ObjectProperty. BIND(owl:ObjectProperty AS ?type). ' . $fullURI . ' rdfns:type owl:TransitiveProperty. BIND(owl:TransitiveProperty AS ?char). ';
        $query = $query . '{ ' . $fullURI . ' rdf:range ?ran.}UNION{FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}}UNION{ ' . $fullURI . ' rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ' . $fullURI . ' rdfns:type owl:SymmetricProperty. BIND(owl:SymmetricProperty AS ?char). { ' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION{FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}}UNION{ ' . $fullURI . ' rdfns:type owl:ObjectProperty. BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . ' rdfns:type owl:AsymmetricProperty. BIND(owl:SymmetricProperty AS ?char). { ' . $fullURI . ' rdf:range ?ran.} UNION ';
        $query = $query . '{FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}}UNION{ ' . $fullURI . ' rdfns:type owl:ObjectProperty. BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . ' rdfns:type owl:InverseFunctionalProperty. BIND(owl:InverseFunctionalProperty AS ?char). { ' . $fullURI . ' rdf:range ?ran.} UNION ';
        $query = $query . '{FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}}UNION{ ' . $fullURI . ' rdfns:type owl:ObjectProperty. BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . ' rdfns:type owl:IrreflexiveProperty. BIND(owl:IrreflexiveProperty AS ?char). { ' . $fullURI . ' rdf:range ?ran.} UNION {FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}} ';
        $query = $query . 'UNION{ ' . $fullURI . ' rdfns:type owl:ObjectProperty. BIND(owl:ObjectProperty AS ?type). ' . $fullURI . ' rdfns:type owl:ReflexiveProperty. BIND(owl:ReflexiveProperty AS ?char). ';
        $query = $query . '{ ' . $fullURI . ' rdf:range ?ran.} UNION {FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}} UNION { ' . $fullURI . ' rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ' . $fullURI . ' owl:inverseOf ?prop2. BIND(owl:inverseOf AS ?char). { ' . $fullURI . ' rdf:range ?ran.} UNION ';
        $query = $query . '{FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}} UNION { ' . $fullURI . ' rdfns:type owl:ObjectProperty. BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . ' owl:equivalentProperty ?prop2. BIND(owl:equivalentProperty AS ?char). { ' . $fullURI . ' rdf:range ?ran.} UNION {FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}} ';
        $query = $query . 'UNION { ' . $fullURI . ' rdfns:type owl:ObjectProperty. BIND(owl:ObjectProperty AS ?type). ' . $fullURI . ' rdf:subPropertyOf ?prop2. BIND(rdf:subPropertyOf AS ?char). ';
        $query = $query . '{ ' . $fullURI . ' rdf:range ?ran.}UNION{FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}}UNION{ ' . $fullURI . ' rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ' . $fullURI . ' owl:disjointWith ?prop2. BIND(owl:disjointWith AS ?char). { ' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION {FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}}}UNION{{ ' . $fullURI . ' rdfns:type owl:DatatypeProperty. BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . '{ ' . $fullURI . ' rdf:range ?ran.} UNION {FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}} FILTER NOT EXISTS{ ' . $fullURI . ' rdfns:type owl:FunctionalProperty.}. ';
        $query = $query . 'FILTER NOT EXISTS{ ' . $fullURI . ' owl:equivalentProperty ?prop2.}. FILTER NOT EXISTS{ ' . $fullURI . ' rdf:subPropertyOf ?prop2.}. ';
        $query = $query . 'FILTER NOT EXISTS{ ' . $fullURI . ' owl:disjointWith ?prop2.}. }UNION{ ' . $fullURI . ' rdfns:type owl:DatatypeProperty. BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . $fullURI . ' rdfns:type owl:FunctionalProperty. BIND(owl:FunctionalProperty AS ?char). { ' . $fullURI . ' rdf:range ?ran.} UNION {FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}} ';
        $query = $query . 'UNION{ ' . $fullURI . ' rdfns:type owl:DatatypeProperty. BIND(owl:DatatypeProperty AS ?type). ' . $fullURI . ' owl:equivalentProperty ?prop2. BIND(owl:equivalentProperty AS ?char). ';
        $query = $query . '{ ' . $fullURI . ' rdf:range ?ran.} UNION {FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}}UNION{ ' . $fullURI . ' rdfns:type owl:DatatypeProperty. ';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type). ' . $fullURI . ' rdf:subPropertyOf ?prop2. BIND(rdf:subPropertyOf AS ?char). { ' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION {FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}}UNION{ ' . $fullURI . ' rdfns:type owl:DatatypeProperty. BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . $fullURI . ' owl:disjointWith ?prop2. BIND(owl:disjointWith AS ?char). { ' . $fullURI . ' rdf:range ?ran.} UNION {FILTER NOT EXISTS{ ' . $fullURI . ' rdf:range ?ran.}}}}} ';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //Ficheiro XSL a ser usado para a transformação do XML
        $xslfile = "http://localhost/assets/xsl/informacoes_propriedade.xsl";   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.
        $result = $this->sendQuery($query, $xslfile);

        print_r($result);
    }

    public function getClassProperty($classe, $chamada) {
        //Obter a URI completa e adicionar a variável $classe
        $ontologyURI = $this->getURI();
        $fullURI = '<' . $ontologyURI . '#' . $classe . '>';

        //Query das propriedades simples das classes
        $query1 = 'prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query1 = $query1 . 'prefix owl: <http://www.w3.org/2002/07/owl#> ';
        $query1 = $query1 . 'prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query1 = $query1 . 'prefix xml: <http://www.w3.org/2001/XMLSchema#> ';
        $query1 = $query1 . 'SELECT (strafter(str(?onProperty), "#") AS ?Propriedade) (strafter(str(?someValuesFrom), "#") AS ?AlgunsValoresDe) ';
        $query1 = $query1 . 'WHERE{ ';
        $query1 = $query1 . $fullURI . ' rdfs:subClassOf ?blankNode. ';
        $query1 = $query1 . '?blankNode owl:onProperty ?onProperty. ';
        $query1 = $query1 . '{?blankNode owl:someValuesFrom ?someValuesFrom. FILTER (!isBlank(?someValuesFrom)). } ';
        $query1 = $query1 . 'UNION  ';
        $query1 = $query1 . '{?blankNode owl:onDataRange ?someValuesFrom. }}  ';
        $query1 = $query1 . '&output=xml&stylesheet=xml-to-html.xsl';


        //Query das propriedades complexas das classes
        $query2 = 'prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> prefix owl:  <http://www.w3.org/2002/07/owl#> prefix rdf:  <http://www.w3.org/1999/02/22-rdf-syntax-ns#> prefix xml:  <http://www.w3.org/2001/XMLSchema#> ';
        $query2 = $query2 . 'select distinct (strafter(str(?p), "#") AS ?Propriedade) (?v AS ?AlgunsValoresDe) (strafter(str(?x), "#") AS ?Restricao){ ';
        $query2 = $query2 . $fullURI . ' owl:equivalentClass/owl:intersectionOf/rdf:rest*/rdf:first ?c . ';
        $query2 = $query2 . '{ ?c owl:onProperty ?p. ?c owl:hasValue ?u. BIND(strafter(str(?u), "#") AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?u. FILTER (!isBlank(?u)). BIND(strafter(str(?u), "#") AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:length ?u. BIND(xml:length AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:minLength ?u. BIND(xml:minLength AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:maxLength ?u. BIND(xml:maxLength AS ?x).	BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h.	?h xml:pattern ?u. BIND(xml:pattern AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h.	?h xml:enumeration ?u. BIND(xml:enumeration AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h.	?h xml:whiteSpace ?u. BIND(xml:whiteSpace AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h.	?h xml:maxInclusive ?u. BIND(xml:maxInclusive AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h.	?h xml:maxExclusive ?u. BIND(xml:maxExclusive AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:minExclusive ?u. BIND(xml:minExclusive AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:minInclusive ?u. BIND(xml:minInclusive AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:totalDigits ?u. BIND(xml:totalDigits AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:fractionDigits ?u. BIND(xml:fractionDigits AS ?x). BIND(str(?u) AS ?v). }';
        $query2 = $query2 . 'UNION { ?c owl:unionOf/rdf:rest*/rdf:first ?e. { ?e owl:onProperty ?p. ?e owl:hasValue ?u. BIND(strafter(str(?u), "#") AS ?v). } ';
        $query2 = $query2 . 'UNION { ?e owl:onProperty ?p. ?e owl:someValuesFrom ?u. FILTER (!isBlank(?u)). BIND(strafter(str(?u), "#") AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:length ?u. BIND(xml:length AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:minLength ?u. BIND(xml:minLength AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:maxLength ?u. BIND(xml:maxLength AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h.	?h xml:pattern ?u. BIND(xml:pattern AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:enumeration ?u. BIND(xml:enumeration AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:whiteSpace ?u. BIND(xml:whiteSpace AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:maxInclusive ?u. BIND(xml:maxInclusive AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:maxExclusive ?u. BIND(xml:maxExclusive AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:minExclusive ?u. BIND(xml:minExclusive AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:minInclusive ?u. BIND(xml:minInclusive AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:totalDigits ?u. BIND(xml:totalDigits AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:fractionDigits ?u. BIND(xml:fractionDigits AS ?x). BIND(str(?u) AS ?v). }} ';
        $query2 = $query2 . 'UNION { ?c owl:unionOf/rdf:rest*/rdf:first/owl:intersectionOf/rdf:rest*/rdf:first ?e. { ?e owl:onProperty ?p. ?e owl:hasValue ?u. BIND(strafter(str(?u), "#") AS ?v). } ';
        $query2 = $query2 . 'UNION { ?e owl:onProperty ?p. ?e owl:someValuesFrom ?u. FILTER (!isBlank(?u)). BIND(strafter(str(?u), "#") AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:length ?u. BIND(xml:length AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:minLength ?u. BIND(xml:minLength AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:maxLength ?u. BIND(xml:maxLength AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:pattern ?u. BIND(xml:pattern AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:enumeration ?u. BIND(xml:enumeration AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:whiteSpace ?u. BIND(xml:whiteSpace AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:maxInclusive ?u. BIND(xml:maxInclusive AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:maxExclusive ?u. BIND(xml:maxExclusive AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:minExclusive ?u. BIND(xml:minExclusive AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:minInclusive ?u. BIND(xml:minInclusive AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h.	?h xml:totalDigits ?u. BIND(xml:totalDigits AS ?x). BIND(str(?u) AS ?v). } ';
        $query2 = $query2 . 'UNION { ?c owl:onProperty ?p. ?c owl:someValuesFrom ?j. ?j owl:withRestrictions ?f. ?f rdf:rest*/rdf:first ?h. ?h xml:fractionDigits ?u. BIND(xml:fractionDigits AS ?x). BIND(str(?u) AS ?v). }}}';
        $query2 = $query2 . '&output=xml&stylesheet=xml-to-html.xsl';

        //Ficheiro XSL a ser usado para a transformação do XML
        if ($chamada == 0) {
            $xslfile1 = "http://localhost/assets/xsl/tabela_propriedades_classes(nonUsers).xsl";   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.
            $xslfile2 = "http://localhost/assets/xsl/tabela_propriedades_classes(nonUsers)2.xsl";   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.
        } else {
            $xslfile1 = "http://localhost/assets/xsl/tabela_propriedades_classes.xsl";
            $xslfile2 = "http://localhost/assets/xsl/tabela_propriedades_classes2.xsl";
        }

        $result1 = $this->sendQuery($query1, $xslfile1);
        $result2 = $this->sendQuery($query2, $xslfile2);

        $result = '<table border=1>';
        $result = $result . $result1 . $result2;
        $result = $result . '</table>';

        print_r($result);
    }

    public function getMemberProperty($membro, $chamada) {
        /*
          SPARQL QUERY:

          prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#>
          prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
          prefix owl: <http://www.w3.org/2002/07/owl#>

          SELECT distinct
          (strafter(str(?membro), "#") AS ?Membro)
          (strafter(str(?propriedade), "#") AS ?Propriedade)
          ?Valor
          ?valor
          (strafter(str(?tipo), "#") AS ?Tipo)

          WHERE{
          ?membro rdf:type owl:NamedIndividual.
          {
          ?propriedade rdf:type owl:ObjectProperty.
          BIND(owl:ObjectProperty as ?tipo).
          ?membro ?propriedade ?valor.
          BIND(STRAFTER(STR(?valor), "#") AS ?Valor).
          }
          UNION
          {
          ?propriedade rdf:type owl:DatatypeProperty.
          BIND(owl:DatatypeProperty as ?tipo).
          ?membro ?propriedade ?valor.
          BIND(STR(?valor) AS ?Valor).
          }
          }
         */

        //Obter a URI completa e adicionar a variável $classeMae
        $ontologyURI = $this->getURI();
        $fullURI = '<' . $ontologyURI . '#' . $membro . '>';

        $query = 'prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'prefix owl: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'SELECT distinct (strafter(str(?propriedade), "#") AS ?Propriedade) ?Valor ?valor (strafter(str(?tipo), "#") AS ?Tipo) ';
        $query = $query . 'WHERE{' . $fullURI . ' rdf:type owl:NamedIndividual.{ ?propriedade rdf:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty as ?tipo). ' . $fullURI . ' ?propriedade ?valor. BIND(STRAFTER(STR(?valor), "#") AS ?Valor). ';
        $query = $query . '}UNION{ ?propriedade rdf:type owl:DatatypeProperty. BIND(owl:DatatypeProperty as ?tipo). ';
        $query = $query . $fullURI . ' ?propriedade ?valor. BIND(STR(?valor) AS ?Valor).}}';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //Ficheiro XSL a ser usado para a transformação do XML
        if ($chamada == 0) {
            $xslfile = "http://localhost/assets/xsl/tabela_propriedades(nonUsers).xsl";   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.
        } else if ($chamada == 1) {
            $xslfile = "http://localhost/assets/xsl/tabela_propriedades.xsl";   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.
        } else {
            $xslfile = "http://localhost/assets/xsl/lista_propriedadesMembros.xsl";   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.
        }

        $result = $this->sendQuery($query, $xslfile);
        print_r($result);
    }

    public function getComment($subject) {
        /*
          SPARQL QUERY:

          PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>

          select ?comment
          {
          <$uri . $subject>
          rdfns:comment
          ?comment
          }
         */

        //Obter a URI completa e adicionar a variável $classeMae
        $ontologyURI = $this->getURI();
        $fullURI = '<' . $ontologyURI . '#' . $subject . '>';

        $query = 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'select ?comment { ' . $fullURI . ' rdfns:comment ?comment }';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //variável XML recebe o resultado da query obtido do método presente no modelo
        $xml = $this->pesti_model->consultar_data($this->url_db_consult, $query);

        //retirar o comentário do XML
        if (strpos($xml, 'literal') !== false) {
            $getComment1 = explode("<literal>", $xml);          // -> primeiro explode (split em JS) que vai procurar no XML todas as ocurrências de <literal>.
            $getComment2 = explode("<", $getComment1[1]);        // -> voltamos a fazer explode para remover o codigo xml que restou.
            $result = htmlentities($getComment2[0]);
        } else {
            $result = "<font color=\"red\">N&atilde;o foi encontrado nenhum coment&aacute;rio...</font>";
        }

        print_r($result);
    }

    public function printURI() {
        // -> Query reutilizada da função listClasses...
        $query = 'PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX a: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'SELECT ?classeMae (strafter(str(?classeMae), "#") AS ?localName) ';
        $query = $query . 'WHERE { ?classeMae rdfns:type a:Class. ';
        $query = $query . 'FILTER (!isBlank(?classeMae)) ';
        $query = $query . 'FILTER NOT EXISTS{ ';
        $query = $query . '{?classeMae rdf:subClassOf ?classe. ';
        $query = $query . '?classe rdfns:type a:Class.} ';
        $query = $query . 'UNION {?classeMae a:equivalentClass ?classe2}}}';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //variável XML recebe o resultado da query obtido do método presente no modelo
        $xml = $this->pesti_model->consultar_data($this->url_db_consult, $query);

        //retirar apenas a URI do resultado XML
        $getfullURI = explode("<uri>", $xml);        // -> primeiro explode (split em JS) que vai procurar no XML todas as ocurrências de <uri>.
        $fullURI = $getfullURI[1];                   // -> em príncipio todas as classe mae pertencem a mesma ontologia, logo só nos interessa a que esta na posição 1 do array retornado pelo explode anterior.
        $getURI = explode("#", $fullURI);           // -> voltamos a fazer um explode para obter apenas o URI da ontologia (ex: http://www.semanticweb.org/ontologies/2012/3/Ontology1334263618896.owl).

        print_r($getURI[0]);
    }

    //================= Funções Privadas ====================//
    private function readConfigFile() {
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

            $this->url_db_consult = $url_fuseki . "/sparql";
            $this->url_db_insert = $url_fuseki . "/update";
        }
    }

    /********************************************************
     *                  COMUNICAÇÃO MODEL                   *
     ********************************************************/
    private function sendQuery($query, $xslfile) {
        //Variável XML recebe o resultado da query obtido do método presente no modelo
        $xml = $this->pesti_model->consultar_data($this->url_db_consult, $query);

        if (!$xml) {
            $result = "<br><font color=\"red\"><b>Erro SPARQL: Ocorreu um erro a retornar a informa&ccedil;&atilde;o, verifique se o endere&ccedil;o est&aacute; correcto.</b></font>";
        } else {
            $result = $this->useXSLT($xml, $xslfile);
        }

        return $result;
    }

    private function getURI() {
        //Query reutilizada da função listClasses...
        $query = 'PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX a: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'SELECT ?classeMae (strafter(str(?classeMae), "#") AS ?localName) ';
        $query = $query . 'WHERE { ?classeMae rdfns:type a:Class. ';
        $query = $query . 'FILTER (!isBlank(?classeMae)) ';
        $query = $query . 'FILTER NOT EXISTS{ ';
        $query = $query . '{?classeMae rdf:subClassOf ?classe. ';
        $query = $query . '?classe rdfns:type a:Class.} ';
        $query = $query . 'UNION {?classeMae a:equivalentClass ?classe2}}}';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //Variável XML recebe o resultado da query obtido do método presente no modelo
        $xml = $this->pesti_model->consultar_data($this->url_db_consult, $query);

        //Retirar a URI do resultado XML
        //Primeiro explode (split em JS) que vai procurar no XML todas as ocurrências de <uri>.
        $getfullURI = explode("<uri>", $xml);
        //Em príncipio todas as classe mae pertencem a mesma ontologia, logo só nos interessa a que esta na posição 1 do array retornado pelo explode anterior.
        $fullURI = $getfullURI[1];
        //Voltamos a fazer um explode para obter apenas o URI da ontologia (ex: http://www.semanticweb.org/ontologies/2012/3/Ontology1334263618896.owl).
        $getURI = explode("#", $fullURI);

        return $getURI[0];
    }

    private function useXSLT($xmlfile, $xslfile) {
        $xsl = new XSLTProcessor;

        $xsl->importStylesheet(DOMDocument::load($xslfile));

        $result = $xsl->transformToXml(simplexml_load_string($xmlfile));

        return $result;
    }
}

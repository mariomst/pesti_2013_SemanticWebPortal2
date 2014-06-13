<?php

/*
 * PESTI Controller 
 * - Vai ser o centro de todos os pedidos da aplicação Web;
 *
 * Versão 2.5
 *
 * @author Mário Teixeira    1090626     1090626@isep.ipp.pt     
 * @author Marta Graça       1100640     1100640@isep.ipp.pt
 * 
 * ========================================================   Changelog:   =============================================================================================
 * 1.0 -> criado função consult que recebe do modelo o XML e retornam o XML para a view.
 * 1.1 -> criado função consultXSLT que recebe do modelo o XML e envia para um metodo que utiliza XSLT para retonar uma tabela HTML.
 * 1.2 -> criado função que vai buscar as subClasses das Specs (ver Ontologia) e retorna-os num XML.
 * 1.3 -> criado função insertData que recebe 2 parametros (sujeito e objecto), adiciona as URI's de acordo com a ontologia e chama a função respectiva do modelo.
 * 1.4 -> reconstrução do controller, as query são enviadas do controller para a função "enviar_query" do model.   
 * 1.5 -> criado função listClasses que retorna as classes mãe existentes na ontologia, alterado a função useXSLT para receber como parametro o ficheiro XSL a ser usado.
 * 1.6 -> criado função listSubClasses que retorna as subClasses existentes na ontologia. 
 * 1.7 -> criado função getMembers que retorna os membros da classe selecionada.
 * 1.8 -> adição da função privada getURI com a funcionalidade de obter o URI da ontologia apartir da função ListClasses.
 * 1.9 -> adição da função getProperties que retorna as propriedades existentes na ontologia.
 * 2.0 -> adição da função getPropertyRange que retorna o range da dada propriedade. Adição de um parametro ao getMembers para utilizar diferentes ficheiros XSL.
 * 2.1 -> adição das funções getSubClasses e getMemberProperty, descrição das funcionalidades indicadas em baixo.
 * 2.2 -> atualização do insertData, adicionado a inserção de comentários nas classes e membros da ontologia.
 * 2.3 -> adição da função deleteData, descrição da função indicada em baixo.
 * 2.4 -> alteração da forma como as queries eram enviadas para o Model. Correcção de alguns erros.
 * 2.5 -> adição das funções getClassProperty_M1 e getClassProperty_M2.
 *
 * ========================================================   Descrição:   =============================================================================================
 * Funções Públicas:
 * view                     -> criação da view Página Principal
 * view_insertClass         -> criação da view Inserção
 * listClasses              -> recebe um xml com as super classes existentes na ontologia e retorna uma lista.
 * listSubClasses           -> recebe um xml com as subclasses da classe indicada e retorna uma lista.
 * getSubClasses            -> recebe um xml com as subclasses da classe indicada e retorna uma tabela.
 * getMembers               -> recebe um xml com todos os membros da classe indicada.
 * getProperties            -> recebe um xml com as propriedades existentes na ontologia.
 * getPropertyRange         -> recebe um xml com o range da propriedade dada.
 * getClassProperty_M1      -> recebe um xml com os nodos dos primeiros first e rest.
 * getClassProperty_M2      -> recebe um xml com os nodos first e rest e analisa-os.
 * getMemberProperty        -> recebe um xml com as propriedades de um determinado membro.
 * printURI                 -> imprime a uri da ontolgia.
 * insertData               -> inserção de novos dados na ontologia.
 * deteleData               -> eliminação de dados da ontologia.
 *
 * Funções Privadas:
 * sendQuery                -> envio da query para o Fuseki (esse processo é tratado pelo modelo).
 * getURI                   -> retorna de forma dinamica a uri da ontologia.
 * useXSLT                  -> carrega o xsl indicado e processa à transformação do xml indicado.
 */

//Configurações do PHP
//error_reporting(0);         // -> Comentar isto para ativar as mensagens de erro do PHP.

class PESTI_Controller extends CI_Controller {

    //================= Variaveis Globais ===================//
    protected $url_db_consult = "http://localhost:3030/data/sparql";            // -> endereço do Fuseki para consultas
    protected $url_db_insert = "http://localhost:3030/data/update";             // -> endereço do Fuseki para inserções
    protected $properties_array = array();

    //================= Funções Públicas ====================//	

    public function __construct() {
        parent::__construct();
        $this->load->model('pesti_model');
    }

    public function view($page = 'home') {
        if (!file_exists('application/views/pages/' . $page . '.php')) {
            show_404();
        }

        //O titulo da página é definida no array $data
        $data['title'] = ucfirst($page);

        //carregar os views na ordem que devem ser exibidos
        $this->load->view('templates/header', $data);
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer', $data);
    }

    public function viewInsertClass($page = 'inserir') {
        if (!file_exists('application/views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = ucfirst($page);

        $this->load->view('pages/' . $page, $data);
    }

    public function listClasses() {
        /*
          SPARQL QUERY:

          PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#>
          PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
          PREFIX a: <http://www.w3.org/2002/07/owl#>

          SELECT ?classeMae (strafter(str(?classeMae), "#") AS ?localName)
          WHERE{
          ?classeMae rdfns:type a:Class .
          FILTER (!isBlank(?classeMae))
          FILTER NOT EXISTS{
          {?classeMae rdf:subClassOf ?classe .
          ?classe rdfns:type a:Class .}
          UNION
          {?classeMae a:equivalentClass ?classe2}
          }
          }
         */

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

        //Ficheiro XSL a ser usado para a transformação do XML
        $xslfile = "http://localhost/assets/xsl/lista_classes.xsl";     // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
        //Enviar a query e o ficheiro XSL para o método privado
        $result = $this->sendQuery($query, $xslfile);

        print_r($result);
    }

    public function listSubClasses($classeMae) {
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
        $xslfile = "http://localhost/assets/xsl/lista_subclasses.xsl";  // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
        //Enviar a query e o ficheiro XSL para o método privado
        $result = $this->sendQuery($query, $xslfile);

        print_r($result);
    }

    public function getSubClasses($classeMae) {
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
        $xslfile = "http://localhost/assets/xsl/tabela_subclasses.xsl";     // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
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
        if ($chamada == 1) {
            $xslfile = "http://localhost/assets/xsl/tabela_membros.xsl";    // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
        } else {
            $xslfile = 'http://localhost/assets/xsl/select_membros.xsl';    // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML
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
        $query = $query . 'select distinct ';
        $query = $query . '(strafter(str(?prop), "#") AS ?Propriedades) ';
        $query = $query . 'where{ ?naoInteressa owl:onProperty ?prop. }';
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

          select distinct
          (strafter(str(?value), "#") AS ?AlgunsValoresDe)

          where{
          {
          {
          ?prop rdfns:type owl:ObjectProperty.
          ?prop rdf:range ?value.
          }
          UNION
          {
          ?prop rdfns:type owl:ObjectProperty.
          FILTER NOT EXISTS
          {
          ?prop rdf:range ?value.
          }
          }
          }
          UNION
          {
          {
          ?prop rdfns:type owl:FunctionalProperty.
          ?prop rdfns:type owl:DatatypeProperty.
          BIND
          (owl:DatatypeProperty AS ?value).
          }
          UNION
          {
          ?prop rdfns:type owl:FunctionalProperty.
          ?prop rdf:range ?value.
          }
          UNION
          {
          ?prop rdfns:type owl:FunctionalProperty.
          FILTER NOT EXISTS{?prop rdf:range ?value.}
          FILTER NOT EXISTS{?prop rdfns:type owl:DatatypeProperty.}
          }
          }
          UNION
          {
          ?prop rdfns:type owl:InverseFunctionalProperty.
          }
          }
         */

        //Obter a URI completa e adicionar a variável $classeMae
        $ontologyURI = $this->getURI();
        $fullURI = '<' . $ontologyURI . '#' . $property . '>';

        $query = 'PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX owl: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'select distinct ';
        $query = $query . '(strafter(str(?value), "#") AS ?AlgunsValoresDe) ';
        $query = $query . 'where{{{';
        $query = $query . $fullURI . ' rdfns:type owl:ObjectProperty. ';
        $query = $query . $fullURI . ' rdf:range ?value. ';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:ObjectProperty. ';
        $query = $query . 'FILTER NOT EXISTS{ ';
        $query = $query . $fullURI . ' rdf:range ?value.';
        $query = $query . '}}}UNION{{';
        $query = $query . $fullURI . ' rdfns:type owl:FunctionalProperty. ';
        $query = $query . $fullURI . ' rdfns:type owl:DatatypeProperty. ';
        $query = $query . 'BIND ';
        $query = $query . '(owl:DatatypeProperty AS ?value). ';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:FunctionalProperty.';
        $query = $query . $fullURI . ' rdf:range ?value.';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:FunctionalProperty. ';
        $query = $query . 'FILTER NOT EXISTS{' . $fullURI . ' rdf:range ?value.}';
        $query = $query . 'FILTER NOT EXISTS{' . $fullURI . ' rdfns:type owl:DatatypeProperty.}';
        $query = $query . '}}UNION{ ';
        $query = $query . $fullURI . ' rdfns:type owl:InverseFunctionalProperty.}}';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //variável XML recebe o resultado da query obtido do método presente no modelo
        $xml = $this->pesti_model->enviar_query($this->url_db_consult, $query);

        //Obter o tipo de propriedade
        $literal = explode("<literal>", $xml);
        $aux = $literal[1];
        $getLiteral = explode("</literal>", $aux);

        if ($getLiteral[0] == "DatatypeProperty") {
            print_r("DatatypeProperty");
        } else {
            if ($chamada == 1) {
                $membros = $this->getMembers($getLiteral[0], 2);
                print_r($membros);
            } else if ($chamada == 2) {
                print_r($getLiteral[0]);
            }
        }
    }
    
    public function getClassProperty_M1($classe)
    {
        /*
         * SPARQL QUERY:
         */
        
        //Obter a URI completa e adicionar a variável $classe
        $ontologyURI = $this->getURI();
        $fullURI = '<' . $ontologyURI . '#' . $classe . '>';
        
        //Construção da Query
        $query = '';
        
        //Ficheiro XSL a ser usado para a transformação do XML
        $xslfile = '';   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.

        $result = $this->sendQuery($query, $xslfile);
        
        //Obter todos os <span> do resultado
        $span = explode("<span>", $query);
        
        //Obter o first
        $first_step1 = $span[1];
        $first_step2 = explode("</span>", $first_step1);
        
        //Obter o rest
        $rest_step1 = $span[2];
        $rest_step2 = explode("</span>", $rest_step1);        
        
        if($rest_step2 != 'http://www.w3.org/1999/02/22-rdf-syntax-ns#nil')
        {
            $this->getClassProperty_M2($first_step2, $rest_step2);     
        }
        else
        {
            return $this->properties_array;
        }
    }
    
    public function getClassProperty_M2($first, $rest)
    {
        /*
         * SPARQL QUERY:
         */
        
        //Construção da Query para analisar o first
        $query = '';
        
        //Ficheiro XSL a ser usado para a transformação do XML
        $xslfile = '';   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.

        $result = $this->sendQuery($query, $xslfile);
        
        //Obter todos os <span> do resultado
        $span = explode("<span>", $query);
        
        //Obter o first
        $first_step1 = $span[1];
        $first_step2 = explode("</span>", $first_step1);
        
        //Obter o rest
        $rest_step1 = $span[2];
        $rest_step2 = explode("</span>", $rest_step1);        
        
        if($rest_step2 != 'http://www.w3.org/1999/02/22-rdf-syntax-ns#nil')
        {
            $this->getClassProperty_M2($first_step2, $rest_step2);     
        }
        else
        {
            return $this->properties_array;
        }           
    }

    public function getMemberProperty($membro) {
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
        $xslfile = "http://localhost/assets/xsl/tabela_propriedades.xsl";   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.

        $result = $this->sendQuery($query, $xslfile);

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
        $xml = $this->pesti_model->enviar_query($this->url_db_consult, $query);

        //retirar apenas a URI do resultado XML
        $getfullURI = explode("<uri>", $xml);        // -> primeiro explode (split em JS) que vai procurar no XML todas as ocurrências de <uri>.
        $fullURI = $getfullURI[1];                   // -> em príncipio todas as classe mae pertencem a mesma ontologia, logo só nos interessa a que esta na posição 1 do array retornado pelo explode anterior.
        $getURI = explode("#", $fullURI);           // -> voltamos a fazer um explode para obter apenas o URI da ontologia (ex: http://www.semanticweb.org/ontologies/2012/3/Ontology1334263618896.owl).

        print_r($getURI[0]);
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
        $xml = $this->pesti_model->enviar_query($this->url_db_consult, $query);

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

    public function insertData($type, $subject, $object) {
        // Variáveis utilizadas:
        $full_uri = $this->getURI();                 // -> obter uri da ontologia.
        $subject_uri = "<" . $full_uri . '#' . $subject . ">";  // -> criação da uri com o sujeito.
        $object_uri = "<" . $full_uri . '#' . $object . ">";  // -> criação da uri com o objecto.
        $result = false;

        if ($type == 'teste') {
            print_r("Apenas para testes...<br>");
            print_r("<table border=1>");
            print_r("<tr>");
            print_r("<td>Tipo</td>");
            print_r("<td>" . $type . "</td>");
            print_r("</tr>");
            print_r("<tr>");
            print_r("<td>URI</td>");
            print_r("<td>" . $full_uri . "</td>");
            print_r("</tr>");
            print_r("<tr>");
            print_r("<td>URI do Sujeito</td>");
            print_r("<td>" . htmlspecialchars($subject_uri) . "</td>");
            print_r("</tr>");
            print_r("<tr>");
            print_r("<td>URI do Objecto</td>");
            print_r("<td>" . htmlspecialchars($object_uri) . "</td>");
            print_r("</tr>");
            print_r("</table>");

            exit;
        } else if ($type == 'membro') {    // -> adição na ontologia de membros
            /*
              SPARQL QUERY para inserção de membros (2 Inserts):

              PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#>
              PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
              PREFIX a: <http://www.w3.org/2002/07/owl#>

              INSERT DATA
              {
              <$subject_uri>
              rdfns:type
              a:NamedIndividual
              }

              INSERT DATA
              {
              <$subject_uri>
              rdfns:type
              <$object_uri>
              }
             */

            $predicate_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>";
            $another_uri = "<http://www.w3.org/2002/07/owl#NamedIndividual>";

            //Primeiro Insert
            $result = $this->pesti_model->inserir_Data($this->url_db_insert, $subject_uri, $predicate_uri, $another_uri);

            //Segundo Insert
            $result = $this->pesti_model->inserir_Data($this->url_db_insert, $subject_uri, $predicate_uri, $object_uri);

            print_r($result);

            exit;
        } else if ($type == 'subclasse') {  // -> adição na ontologia de subclasses
            /*
              SPARQL Query para inserção de subclasses

              PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#>
              PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
              PREFIX a: <http://www.w3.org/2002/07/owl#>

              INSERT DATA
              {
              <$subject_uri>
              <http://www.w3.org/2000/01/rdf-schema#subClassOf>
              <$object_uri>
              }

              INSERT DATA
              {
              <$subject_uri>
              <http://www.w3.org/1999/02/22-rdf-syntax-ns#type>
              <http://www.w3.org/2002/07/owl#Class>
              }
             */

            $predicate_uri = "<http://www.w3.org/2000/01/rdf-schema#subClassOf>";

            //Primeiro Insert
            $result = $this->pesti_model->inserir_Data($this->url_db_insert, $subject_uri, $predicate_uri, $object_uri);

            $predicate_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>";
            $another_uri = "<http://www.w3.org/2002/07/owl#Class>";

            //Segundo Insert
            $result = $this->pesti_model->inserir_Data($this->url_db_insert, $subject_uri, $predicate_uri, $another_uri);

            print_r($result);

            exit;
        } else if ($type == 'propriedade') {  // -> adição na ontologia de propriedades
            /* AINDA NAO DESENVOLVIDO */
            print_r("Tipo: Propriedade");
            exit;
        } else if ($type == 'comentario') {
            /*
              SPARQL QUERY:

              INSERT DATA
              {
              <$subject_uri>
              <http://www.w3.org/1999/02/22-rdf-syntax-ns#comment>
              "$object".
              }
             */

            $predicate_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#comment>";

            $result = $this->pesti_model->inserir_Data($this->url_db_insert, $subject_uri, $predicate_uri, $object);

            print_r($result);

            exit;
        } else {
            print_r("Erro: Tipo n&atilde;o reconhecido...");
            exit;
        }
    }

    public function deleteData($type, $subject, $object) {
        // Variáveis utilizadas:
        $full_uri = $this->getURI();                 // -> obter uri da ontologia.
        $subject_uri = "<" . $full_uri . '#' . $subject . ">";  // -> criação da uri com o sujeito.
        $result = false;

        if ($type == 'membro') {    // -> Eliminação de um membro
            $object_uri = "<" . $full_uri . '#' . $object . ">";  // -> criação da uri com o objecto.

            $predicate_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>";
            $another_uri = "<http://www.w3.org/2002/07/owl#NamedIndividual>";

            //Primeiro Eliminar
            $result = $this->pesti_model->eliminar_data($this->url_db_insert, $subject_uri, $predicate_uri, $another_uri);

            //Segundo Eliminar
            $result = $this->pesti_model->eliminar_data($this->url_db_insert, $subject_uri, $predicate_uri, $object_uri);

            print_r($result);

            exit;
        } else if ($type == 'comentario') {   // -> Eliminação de um comentário
            /*
              SPARQL QUERY:

              DELETE DATA
              {
              <$subject_uri>
              <http://www.w3.org/1999/02/22-rdf-syntax-ns#comment>
              "$object".
              }
             */

            $predicate_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#comment>";
            $result = $this->pesti_model->eliminar_data($this->url_db_insert, $subject_uri, $predicate_uri, $object);

            print_r($result);

            exit;
        } else {
            print_r("Erro: Tipo n&atilde;o reconhecido...");
            exit;
        }
    }

    //=================Funções Privadas ====================//

    private function sendQuery($query, $xslfile) {
        //variável XML recebe o resultado da query obtido do método presente no modelo
        $xml = $this->pesti_model->enviar_query($this->url_db_consult, $query);

        if (!$xml) {
            $result = "<br><font color=\"red\"><b>Erro SPARQL: Ocorreu um erro a retornar a informa&ccedil;&atilde;o, verifique se o endere&ccedil;o est&aacute; correcto.</b></font>";
        } else {
            $result = $this->useXSLT($xml, $xslfile);
        }

        return $result;
    }

    private function getURI() {
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
        $xml = $this->pesti_model->enviar_query($this->url_db_consult, $query);

        //retirar a URI do resultado XML
        $getfullURI = explode("<uri>", $xml);        // -> primeiro explode (split em JS) que vai procurar no XML todas as ocurrências de <uri>.
        $fullURI = $getfullURI[1];                   // -> em príncipio todas as classe mae pertencem a mesma ontologia, logo só nos interessa a que esta na posição 1 do array retornado pelo explode anterior.
        $getURI = explode("#", $fullURI);           // -> voltamos a fazer um explode para obter apenas o URI da ontologia (ex: http://www.semanticweb.org/ontologies/2012/3/Ontology1334263618896.owl).

        return $getURI[0];
    }

    private function useXSLT($xmlfile, $xslfile) {
        $xsl = new XSLTProcessor;

        $xsl->importStylesheet(DOMDocument::load($xslfile));

        $result = $xsl->transformToXml(simplexml_load_string($xmlfile));

        return $result;
    }

}

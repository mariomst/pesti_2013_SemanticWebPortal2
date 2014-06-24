<?php

/*
 * PESTI Controller 
 * - Vai ser o centro de todos os pedidos da aplicação Web;
 *
 * Versão 3.0
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
 * 2.6 -> adição da função insertProperty.
 * 2.7 -> alteração da estrutura, criação de algumas funções privadas, descrição indicada em baixo.
 * 2.8 -> alterado a função insertData, para cada caso chama a função privada apropriada; adição das funções privadas. insertClass, insertMember, insertCommentary.
 * 2.9 -> adição da função selectSubClasses, descrição da função indicada em baixo.
 * 3.0 -> adição da função privada readConfigFile, para permitir a leitura do endereço do servidor Fuseki apartir de um ficheiro .ini.
 * 
 * ========================================================   Descrição:   =============================================================================================
 * Funções Públicas:
 * view                     -> criação da view Página Principal
 * view_insertClass         -> criação da view Inserção
 * listClasses              -> recebe um xml com as super classes existentes na ontologia e retorna uma lista.
 * listSubClasses           -> recebe um xml com as subclasses da classe indicada e retorna uma lista.
 * selectSubClasses         -> recebe um xml com as subclasses da classe indicada e retorna opções para inserir num select.
 * getSubClasses            -> recebe um xml com as subclasses da classe indicada e retorna uma tabela.
 * getMembers               -> recebe um xml com todos os membros da classe indicada.
 * getProperties            -> recebe um xml com as propriedades existentes na ontologia.
 * getPropertyRange         -> recebe um xml com o range da propriedade dada.
 * getClassProperty_M1      -> recebe um xml com os nodos dos primeiros first e rest.
 * getClassProperty_M2      -> recebe um xml com os nodos first e rest e analisa-os.
 * getMemberProperty        -> recebe um xml com as propriedades de um determinado membro.
 * printURI                 -> imprime a uri da ontolgia.
 * getCommentary            -> recebe o comentário associado ao elemento indicado.
 * insertData               -> inserção de novos dados na ontologia.
 * insertProperty           -> inserção de propriedades em elementos da ontologia.
 * deteleData               -> eliminação de dados da ontologia.
 *
 * Funções Privadas:
 * readConfigFile           -> carrega o endereço do servidor Fuseki apartir de um ficheiro .ini
 * insertClass              -> inserção de uma classe.
 * insertMember             -> inserção de um membro.
 * insertCommentary         -> inserção de um comentário.
 * insertFixedProperty      -> inserção de propriedades fixas em classes.
 * insertNotFixedProperty   -> inserção de propriedades não fixas em classes.
 * insertMemberProperty     -> inserção de propriedades em membros.
 * insertNewPropertyStep1   -> inserção do tipo de propriedade.
 * insertNewPropertyStep2   -> inserção dos elementos para o respectivo tipo de propriedade.
 * deleteClass              -> eliminação da classe.
 * deleteMember             -> eliminação do membro.
 * deleteCommentary         -> eliminação do comentário.
 * sendQuery                -> envio da query para o Fuseki (esse processo é tratado pelo modelo).
 * sendInsert               -> envio da query de inserção para o Fuseki (esse processo é tratado pelo modelo).
 * sendDelete               -> envio da query de eliminação para o Fuseki (esse processo é tratado pelo modelo).
 * getURI                   -> retorna de forma dinamica a uri da ontologia.
 * useXSLT                  -> carrega o xsl indicado e processa à transformação do xml indicado.
 */

//Configurações do PHP
error_reporting(0);         // -> Comentar isto para ativar as mensagens de erro do PHP.

class PESTI_Controller extends CI_Controller {

    //================= Variaveis Globais ===================//
    protected $url_db_consult = "";             // -> endereço do Fuseki para consultas
    protected $url_db_insert = "";              // -> endereço do Fuseki para inserções
    protected $properties_array = array();

    //================= Funções Públicas ====================//	

    public function __construct() {
        parent::__construct();
        $this->load->model('pesti_model');
        $this->readConfigFile();
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
        $xml = $this->pesti_model->consultar_data($this->url_db_consult, $query);

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
    
    public function getPropertyInfo($property)
    {
        //Obter a URI completa e adicionar a variável $classeMae
        $ontologyURI = $this->getURI();
        $fullURI = '<' . $ontologyURI . '#' . $property . '>';
        
        $query = 'PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX owl: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'select distinct '; 
        $query = $query . '(strafter(str(?type), "#") AS ?Tipo) ';
        $query = $query . '(strafter(str(?char), "#") AS ?Caracteristicas) ';
        $query = $query . '(strafter(str(?prop2), "#") AS ?Propriedade2) ';
        $query = $query . '(strafter(str(?ran), "#") AS ?Range) ';
        $query = $query . 'where{{{';
        $query = $query . $fullURI . ' rdfns:type owl:ObjectProperty. ';
	$query = $query . 'BIND(owl:ObjectProperty AS ?type).';
	$query = $query . $fullURI . ' rdf:range ?ran.';
	$query = $query . '}UNION{';
	$query = $query . $fullURI . ' rdfns:type owl:ObjectProperty.';
	$query = $query . 'BIND(owl:ObjectProperty AS ?type).';
	$query = $query . $fullURI . ' rdfns:type owl:FunctionalProperty.';
	$query = $query . 'BIND(owl:FunctionalProperty AS ?char).';
	$query = $query . '}UNION{';
	$query = $query . $fullURI . ' rdfns:type owl:ObjectProperty.';
	$query = $query . 'BIND(owl:ObjectProperty AS ?type).';
	$query = $query . $fullURI . ' rdfns:type owl:TransitiveProperty.';
	$query = $query . 'BIND(owl:TransitiveProperty AS ?char).';
	$query = $query . '}UNION{';
	$query = $query . $fullURI . ' rdfns:type owl:ObjectProperty.';
	$query = $query . 'BIND(owl:ObjectProperty AS ?type).';
	$query = $query . $fullURI . ' rdfns:type owl:SymmetricProperty.';
	$query = $query . 'BIND(owl:SymmetricProperty AS ?char).';
	$query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:ObjectProperty.';
	$query = $query . 'BIND(owl:ObjectProperty AS ?type).';
	$query = $query . $fullURI . ' rdfns:type owl:AsymmetricProperty.';
	$query = $query . 'BIND(owl:SymmetricProperty AS ?char).';
	$query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:ObjectProperty.';
	$query = $query . 'BIND(owl:ObjectProperty AS ?type).';
        $query = $query . $fullURI . ' rdfns:type owl:InverseFunctionalProperty.';
	$query = $query . 'BIND(owl:InverseFunctionalProperty AS ?char).';
	$query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:ObjectProperty.';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type).';
	$query = $query . $fullURI . ' rdfns:type owl:IrreflexiveProperty.';
        $query = $query . 'BIND(owl:IrreflexiveProperty AS ?char).';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:ObjectProperty.';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type).';
        $query = $query . $fullURI . ' rdfns:type owl:ReflexiveProperty.';
        $query = $query . 'BIND(owl:ReflexiveProperty AS ?char).';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:ObjectProperty.';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type).';
        $query = $query . $fullURI . ' owl:inverseOf ?prop2.';
        $query = $query . 'BIND(owl:inverseOf AS ?char).';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:ObjectProperty.';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type).';
        $query = $query . $fullURI . ' owl:equivalentProperty ?prop2.';
        $query = $query . 'BIND(owl:equivalentProperty AS ?char).';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:ObjectProperty.';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type).';
        $query = $query . $fullURI . ' rdf:subPropertyOf ?prop2.';
        $query = $query . 'BIND(rdf:subPropertyOf AS ?char).';
        $query = $query . '}}UNION{{';
        $query = $query . $fullURI . ' rdfns:type owl:DatatypeProperty.';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type).';
        $query = $query . $fullURI . ' rdfns:type owl:FunctionalProperty.';
        $query = $query . 'BIND(owl:FunctionalProperty AS ?char).';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:DatatypeProperty.';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type).';
        $query = $query . $fullURI . ' rdfns:type owl:TransitiveProperty.';
	$query = $query . 'BIND(owl:TransitiveProperty AS ?char).';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:DatatypeProperty.';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type).';
        $query = $query . $fullURI . ' rdfns:type owl:SymmetricProperty.';
        $query = $query . 'BIND(owl:SymmetricProperty AS ?char).';
	$query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:DatatypeProperty.';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type).';
        $query = $query . $fullURI . ' rdfns:type owl:AsymmetricProperty.';
        $query = $query . 'BIND(owl:SymmetricProperty AS ?char).';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:DatatypeProperty.';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type).';
	$query = $query . $fullURI . ' rdfns:type owl:InverseFunctionalProperty.';
        $query = $query . 'BIND(owl:InverseFunctionalProperty AS ?char).';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:DatatypeProperty.';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type).';
        $query = $query . $fullURI . ' rdfns:type owl:IrreflexiveProperty.';
        $query = $query . 'BIND(owl:IrreflexiveProperty AS ?char).';
	$query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:DatatypeProperty.';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type).';
        $query = $query . $fullURI . ' rdfns:type owl:ReflexiveProperty.';
        $query = $query . 'BIND(owl:ReflexiveProperty AS ?char).';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:DatatypeProperty.';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type).';
	$query = $query . $fullURI . ' owl:inverseOf ?prop2.';
	$query = $query . 'BIND(owl:inverseOf AS ?char).';
	$query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:DatatypeProperty.';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type).';
        $query = $query . $fullURI . ' owl:equivalentProperty ?prop2.';
        $query = $query . 'BIND(owl:equivalentProperty AS ?char).';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . ' rdfns:type owl:DatatypeProperty.';
	$query = $query . 'BIND(owl:DatatypeProperty AS ?type).';
	$query = $query . $fullURI . ' rdf:subPropertyOf ?prop2.';
	$query = $query . 'BIND(rdf:subPropertyOf AS ?char).}}}';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';
        
        $xml = $this->pesti_model->consultar_data($this->url_db_consult, $query);
        
        //Ficheiro XSL a ser usado para a transformação do XML
        //$xslfile = "http://localhost/assets/xsl/informacoes_propriedade.xsl";   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.

        //$result = $this->sendQuery($query, $xslfile);

        print_r($xml);
    }

    public function getClassProperty_M1($classe) {
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
        $span = explode("<span>", $result);

        //Obter o first
        $first_step1 = $span[1];
        $first_step2 = explode("</span>", $first_step1);

        //Obter o rest
        $rest_step1 = $span[2];
        $rest_step2 = explode("</span>", $rest_step1);

        if ($rest_step2 != 'http://www.w3.org/1999/02/22-rdf-syntax-ns#nil') {
            $this->getClassProperty_M2($first_step2, $rest_step2);
        } else {
            return $this->properties_array;
        }
    }

    public function getClassProperty_M2($first, $rest) {
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

        if ($rest_step2 != 'http://www.w3.org/1999/02/22-rdf-syntax-ns#nil') {
            $this->getClassProperty_M2($first_step2, $rest_step2);
        } else {
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
        $xml = $this->pesti_model->consultar_data($this->url_db_consult, $query);

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

    public function insertData($type, $subject, $object) {
        // Variáveis utilizadas:
        $full_uri = $this->getURI();                 // -> obter uri da ontologia.
        $subject_uri = "<" . $full_uri . '#' . $subject . ">";  // -> criação da uri com o sujeito.
        $object_uri = "<" . $full_uri . '#' . $object . ">";  // -> criação da uri com o objecto.
        $result = false;

        if ($type == 'teste') {
            //Teste para verificar se a URI é obtida correctamente.
            $this->testURI($type, $full_uri, $subject_uri, $object_uri);

            exit;
        } else if ($type == 'membro') {
            //Adição de membros.

            /*
             * SPARQL QUERY para inserção de membros (2 Inserts):
             * 
             * PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#>
             * PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
             * PREFIX a: <http://www.w3.org/2002/07/owl#>
             * 
             * INSERT DATA
             * {
             * <$subject_uri>
             * rdfns:type
             * a:NamedIndividual
             * }
             * 
             * INSERT DATA
             * {
             * <$subject_uri>
             * rdfns:type
             * <$object_uri>
             * }
             */

            $result = $this->insertMember($subject_uri, $object_uri);

            print_r($result);

            exit;
        } else if ($type == 'subclasse') {
            //Adição de subclasses

            /*
             * SPARQL Query para inserção de subclasses
             * 
             * PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#>
             * PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
             * PREFIX a: <http://www.w3.org/2002/07/owl#>
             * 
             * INSERT DATA
             * {
             * <$subject_uri>
             * <http://www.w3.org/2000/01/rdf-schema#subClassOf>
             * <$object_uri>
             * }
             * 
             * INSERT DATA
             * {
             * <$subject_uri>
             * <http://www.w3.org/1999/02/22-rdf-syntax-ns#type>
             * <http://www.w3.org/2002/07/owl#Class>
             * }
             */

            $result = $this->insertClass($subject_uri, $object_uri);

            print_r($result);

            exit;
        } else if ($type == 'comentario') {
            //Adição de um comentário.

            /*
             * SPARQL Query:
             * 
             * INSERT DATA
             * {
             * <$subject_uri>
             * <http://www.w3.org/1999/02/22-rdf-syntax-ns#comment>
             * "$object".
             * }
             */

            $result = $this->insertCommentary($subject_uri, $object);

            print_r($result);

            exit;
        } else {
            print_r("Erro: Tipo n&atilde;o reconhecido...");
            exit;
        }
    }

    public function insertProperty($type, $subject, $predicate, $object, $range) {
        //Obter a URI da ontologia.
        $full_uri = $this->getURI();
        //Criação da URI do sujeito.
        $subject_uri = "<" . $full_uri . "#" . $subject . ">";

        if ($type == 'fixo') {
            //Ainda não desenvolvido.
            print_r("Erro: Ainda n&atilde;o desenvolvido...");
            exit;
        } else if ($type == 'naoFixo') {
            /*
             * SPARQL QUERY:
             * 
             * INSERT DATA
             * {
             *      ?classe rdfs:subClassOf _:foo
             *      _:foo rdf:type owl:Restriction
             *      _:foo owl:someValuesFrom ?range
             *      _:foo owl:onProperty ?propriedade
             * }
             */

            //Chamada da função privada.
            $result = $this->insertNotFixedProperty($subject_uri, $object, $range);

            print_r($result);

            exit;
        } else if ($type == 'membro') {
            /*
             * SPARQL QUERY:
             * 
             * INSERT DATA
             * {
             *      <subject_uri>       =>  membro
             *      <predicate_uri>     =>  temX
             *      <object_uri>        =>  valor
             * }
             */

            //Chamada da função privada.
            $result = $this->insertMemberProperty($subject_uri, $predicate, $object);

            //Impressão do resultado.
            print_r($result);
            exit;
        } else if ($type == 'novo1') {
            /*
             * SPARQL QUERY:
             * Inserção de uma propriedade do tipo ObjectProperty ou DatatypeProperty
             * 
             * INSERT DATA
             * {
             *      <subject_uri>
             *      <http://www.w3.org/1999/02/22-rdf-syntax-ns#type>
             *      <object_uri>
             * }
             */

            //Chamada da função privada.
            $result = $this->insertNewPropertyStep1($subject_uri, $object);

            //Impressão do resultado.
            print_r($result);
            exit;
        } else if ($type == 'novo2') {
            /*
             * SPARQL QUERY:
             * Inserção de tipos de FunctionalProperties
             * 
             * INSERT DATA
             * {
             *      <subject_uri>
             *      <predicate_uri>
             *      <object_uri>
             * }
             */

            //Chamada da função privada.
            $result = $this->insertNewPropertyStep2($subject_uri, $predicate, $object);

            //Impressão do resultado.
            print_r($result);
            exit;
        } else {
            print_r("Erro: Tipo de propriedade n&atilde;o reconhecido...");
            exit;
        }
    }

    public function deleteData($type, $subject, $object) {
        //Variáveis utilizadas:
        //Obter a URI da ontologia.
        $full_uri = $this->getURI();
        //Criação da URI do sujeito.
        $subject_uri = "<" . $full_uri . '#' . $subject . ">";

        if ($type == 'classe') {
            //Eliminação de uma classe.
            //Criação da URI do objecto.
            $object_uri = "<" . $full_uri . '#' . $object . ">";

            $result = $this->deleteClass($subject_uri, $object_uri);

            print_r($result);

            exit;
        } else if ($type == 'membro') {
            //Eliminação de um membro.
            //Criação da URI do objecto.
            $object_uri = "<" . $full_uri . '#' . $object . ">";

            $result = $this->deleteMember($subject_uri, $object_uri);

            print_r($result);

            exit;
        } else if ($type == 'comentario') {
            //Eliminação de um comentário
            $result = $this->deleteCommentary($subject_uri, $object);

            print_r($result);

            exit;
        } else {
            print_r("Erro: Tipo n&atilde;o reconhecido...");
            exit;
        }
    }

    //================= Funções Privadas ====================//

    private function readConfigFile() {
        //Definição das variáveis a serem usadas.
        $configFile = 'configs/connections.ini';
        $result = array();
        $url_fuseki = '';

        if (!file_exists($configFile)) {
            print_r("<br><font color=\"red\"><b>Erro: O ficheiro de configura&ccedil;&atilde;o n&atilde;o foi encontrado na pasta configs!");
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

                $url_fuseki = $url_fuseki . $aux[1];

                //Remover possíveis espaços
                $url_fuseki = preg_replace('/\s+/', '', $url_fuseki);
            }

            $this->url_db_consult = $url_fuseki . "/sparql";
            $this->url_db_insert = $url_fuseki . "/update";
        }
    }

    private function insertClass($subject_uri, $object1_uri) {
        //Definição do predicado RDFS:subClassOf para a primeira inserção.
        $predicate1_uri = "<http://www.w3.org/2000/01/rdf-schema#subClassOf>";
        //Definição do predicado RDF:type para a segunda inserção.
        $predicate2_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>";
        //Definição do objecto OWL:Class para a segunda inserção.
        $object2_uri = "<http://www.w3.org/2002/07/owl#Class>";

        //Primeira inserção.
        $result = $this->sendInsert($subject_uri, $predicate1_uri, $object1_uri);

        if ($result == 1) {
            //Segunda inserção.
            $result = $this->sendInsert($subject_uri, $predicate2_uri, $object2_uri);
        }

        return $result;
    }

    private function insertMember($subject_uri, $object2_uri) {
        //Definição do predicado RDF:type.
        $predicate_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>";
        //Definição do objecto OWL:NamedIndividual para a primeira inserção.
        $object1_uri = "<http://www.w3.org/2002/07/owl#NamedIndividual>";

        //Primeira inserção.
        $result = $this->sendInsert($subject_uri, $predicate_uri, $object1_uri);

        if ($result == 1) {
            //Segunda inserção.
            $result = $this->sendInsert($subject_uri, $predicate_uri, $object2_uri);
        }

        return $result;
    }

    private function insertCommentary($subject_uri, $object) {
        //Definição da URI do predicado RDF:comment.
        $predicate_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#comment>";

        //Enviar os argumentos para a função privada sendInsert.
        $result = $this->sendInsert($subject_uri, $predicate_uri, $object);

        return $result;
    }

    private function insertFixedProperty() {
        //Função ainda não desenvolvida.
    }

    private function insertNotFixedProperty($subject_uri, $object, $range) {
        //Definição da URI da ontologia.
        $full_uri = $this->getURI();
        //Definição da URI do objecto.
        $object_uri = "<" . $full_uri . "#" . $object . ">";
        //Definição da URI do range.
        $range_uri = "<" . $full_uri . "#" . $range . ">";

        //Construcção dos argumentos para enviar a função do model.
        $argumentos = $subject_uri . " <http://www.w3.org/2000/01/rdf-schema#subClassOf> _:foo";
        $argumentos = $argumentos . " _:foo <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://www.w3.org/2002/07/owl#Restriction>";
        $argumentos = $argumentos . " _:foo <http://www.w3.org/2002/07/owl#someValuesFrom> " . $range_uri;
        $argumentos = $argumentos . " _:foo <http://www.w3.org/2002/07/owl#onProperty> " . $object_uri;

        //Envio para a função insert_data_2 que recebe vários argumentos.
        $result = $this->pesti_model->inserir_data_2($this->url_db_insert, $argumentos);

        return $result;
    }

    private function insertMemberProperty($subject_uri, $predicate, $object) {
        //Definição da URI da ontologia.
        $full_uri = $this->getURI();
        //Criação da URI do predicado.
        $predicate_uri = "<" . $full_uri . "#" . $predicate . ">";
        //Criação da URI do objecto.
        $object_uri = "<" . $full_uri . "#" . $object . ">";

        //Enviar as variáveis para a função privada sendInsert.
        $result = $this->sendInsert($subject_uri, $predicate_uri, $object_uri);

        return $result;
    }

    private function insertNewPropertyStep1($subject_uri, $object) {
        //Definição da URI do predicado RDF:type.
        $predicate_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>";
        //Definição da URI do objecto.
        $object_uri = "<http://www.w3.org/2002/07/owl#" . $object . ">";

        $result = $this->sendInsert($subject_uri, $predicate_uri, $object_uri);

        return $result;
    }

    private function insertNewPropertyStep2($subject_uri, $predicate, $object) {
        //Verificação se o $predicate é do tipo "type" ou um dos seguintes.
        if ($predicate != "type") {
            //Definição da URI do objecto.
            $full_uri = $this->getURI();
            $object_uri = "<" . $full_uri . '#' . $object . ">";
            //Verificação se é inverseOf ou equivalentPropery.
            if ($predicate == "inverseOf" || $predicate == "equivalentProperty") {
                //Definição da URI para estes dois predicados.
                $predicate_uri = "<http://www.w3.org/2002/07/owl#" . $predicate . ">";
                //Verificação se é range ou subPropertyOf.
            } else if ($predicate == "range" || $predicate == "subPropertyOf") {
                //Definição da URI para estes dois predicados.
                $predicate_uri = "<http://www.w3.org/2000/01/rdf-schema#" . $predicate . ">";
            } else {
                print_r("Erro: Predicado n&atilde;o reconhecido...");
                exit;
            }
        } else {
            //Definição da URI do predicado RDF:type.
            $predicate_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>";
            //Definição da URI do objecto.
            $object_uri = "<http://www.w3.org/2002/07/owl#" . $object . ">";
        }

        $result = $this->sendInsert($subject_uri, $predicate_uri, $object_uri);

        return $result;
    }

    private function deleteClass($subject_uri, $object1_uri) {
        //Definição do predicado RDFS:subClassOf para a primeira eliminação.
        $predicate1_uri = "<http://www.w3.org/2000/01/rdf-schema#subClassOf>";
        //Definição do predicado RDF:type para a segunda eliminação.
        $predicate2_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>";
        //Definição do objecto OWL:Class para a segunda eliminação.
        $object2_uri = "<http://www.w3.org/2002/07/owl#Class>";

        //Primeira eliminação.
        $result = $this->sendDelete($subject_uri, $predicate1_uri, $object1_uri);

        if ($result == 1) {
            //Segunda eliminação.
            $result = $this->sendDelete($subject_uri, $predicate2_uri, $object2_uri);
        }

        return $result;
    }

    private function deleteMember($subject_uri, $object2_uri) {
        //Definição do predicado RDF:type.
        $predicate_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>";
        //Definição do objecto OWL:NamedIndividual para a primeira eliminação.
        $object1_uri = "<http://www.w3.org/2002/07/owl#NamedIndividual>";

        //Primeira eliminação.
        $result = $this->sendDelete($subject_uri, $predicate_uri, $object1_uri);

        if ($result == 1) {
            //Segunda eliminação.
            $result = $this->sendDelete($subject_uri, $predicate_uri, $object2_uri);
        }

        return $result;
    }

    private function deleteCommentary($subject_uri, $object) {
        //Definição do predicado RDF:Comment
        $predicate_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#comment>";

        $result = $this->sendDelete($subject_uri, $predicate_uri, $object);

        return $result;
    }

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

    private function sendInsert($subject, $predicate, $object) {
        //Variável result recebe 1 se a inserção for com sucesso.
        $result = $this->pesti_model->inserir_data($this->url_db_insert, $subject, $predicate, $object);

        return $result;
    }

    private function sendDelete($subject, $predicate, $object) {
        //Variável result recebe 1 se a eliminação for com sucesso.
        $result = $this->pesti_model->eliminar_data($this->url_db_insert, $subject, $predicate, $object);

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

    private function testURI($type, $full_uri, $subject_uri, $object_uri) {
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
    }

}

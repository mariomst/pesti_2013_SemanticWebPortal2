<?php

/*
 * PESTI Controller 
 * - Vai ser o centro de todos os pedidos da aplicação Web;
 *
 * Versão 3.4
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
 * 3.1 -> adição da função getPropertyInfo, que retorna o tipo e características da propriedade indicada.
 * 3.2 -> adição das funções insertVisibilityProperty e deleteVisibilityProperty.
 * 3.3 -> desenvolvimento da função getMemberProperty.
 * 3.4 -> alteração das funções listClasses e listSubClasses para incluir as pesquisas da propriedade temVisibilidade.
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
 * getPropertyInfo          -> recebe um xml com informações de uma dada propriedade.
 * getClassProperty         -> recebe um xml com informações de algumas das propriedades da classe.
 * getMemberProperty        -> recebe um xml com as propriedades de um determinado membro.
 * getCommentary            -> recebe o comentário associado ao elemento indicado.
 * printURI                 -> imprime a uri da ontolgia.
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
 * insertVisibilityProperty -> inserção da propriedade temVisibilidade numa classe.
 * deleteClass              -> eliminação da classe.
 * deleteMember             -> eliminação do membro.
 * deleteCommentary         -> eliminação do comentário.
 * deleteVisibilityProperty -> eliminação da propriedade temVisibilidade numa classe.
 * sendQuery                -> envio da query para o Fuseki (esse processo é tratado pelo modelo).
 * sendInsert               -> envio da query de inserção para o Fuseki (esse processo é tratado pelo modelo).
 * sendDelete               -> envio da query de eliminação para o Fuseki (esse processo é tratado pelo modelo).
 * getURI                   -> retorna de forma dinamica a uri da ontologia.
 * getTipoDatatype          -> retorna o tipo de datatype da propriedade indicada.
 * useXSLT                  -> carrega o xsl indicado e processa à transformação do xml indicado.
 */

//Configurações do PHP
error_reporting(0);         // -> Comentar isto para ativar as mensagens de erro do PHP.

class PESTI_Controller extends CI_Controller {

    //================= Variaveis Globais ===================//

    protected $url_db_consult = "";             // -> endereço do Fuseki para consultas
    protected $url_db_insert = "";              // -> endereço do Fuseki para inserções

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

    /********************************************************
     *                  CONSULTAS FUSEKI                    *
     ********************************************************/

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

        if ($chamada == 1) {
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

    public function listSubClasses($classeMae) {
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

        $query = 'PREFIX rdf: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX owl: <http://www.w3.org/2002/07/owl#> ';

        $query = $query . 'select distinct ';
        $query = $query . '(strafter(str(?prop), "#") AS ?Propriedade) ';
        $query = $query . '(strafter(str(?type), "#") AS ?Tipo) ';
        $query = $query . '(strafter(str(?char), "#") AS ?Caracteristicas) ';
        $query = $query . '(strafter(str(?prop2), "#") AS ?Propriedade2) ';
        $query = $query . '(strafter(str(?ran), "#") AS ?Range) ';

        $query = $query . 'where{{{ ';
        $query = $query . $fullURI . ' rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . '  rdf:range ?ran. ';
        $query = $query . 'FILTER NOT EXISTS{' . $fullURI . ' rdfns:type owl:FunctionalProperty.}. ';
        $query = $query . 'FILTER NOT EXISTS{' . $fullURI . ' rdfns:type owl:TransitiveProperty.}. ';
        $query = $query . 'FILTER NOT EXISTS{' . $fullURI . ' rdfns:type owl:SymmetricProperty.}. ';
        $query = $query . 'FILTER NOT EXISTS{' . $fullURI . ' rdfns:type owl:AsymmetricProperty.}. ';
        $query = $query . 'FILTER NOT EXISTS{' . $fullURI . ' rdfns:type owl:InverseFunctionalProperty.}. ';
        $query = $query . 'FILTER NOT EXISTS{' . $fullURI . ' rdfns:type owl:IrreflexiveProperty.}. ';
        $query = $query . 'FILTER NOT EXISTS{' . $fullURI . ' rdfns:type owl:ReflexiveProperty.}. ';
        $query = $query . 'FILTER NOT EXISTS{' . $fullURI . ' owl:inverseOf ?prop2.}. ';
        $query = $query . 'FILTER NOT EXISTS{' . $fullURI . ' owl:equivalentProperty ?prop2.}. ';
        $query = $query . 'FILTER NOT EXISTS{' . $fullURI . ' rdf:subPropertyOf ?prop2.}. ';
        $query = $query . '}UNION{';
        $query = $query . $fullURI . '  rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:FunctionalProperty. ';
        $query = $query . 'BIND(owl:FunctionalProperty AS ?char). ';
        $query = $query . '{' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION{FILTER NOT EXISTS{' . $fullURI . ' rdf:range ?ran.}} ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:TransitiveProperty. ';
        $query = $query . 'BIND(owl:TransitiveProperty AS ?char). ';
        $query = $query . '{' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION{FILTER NOT EXISTS{' . $fullURI . ' rdf:range ?ran.}} ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:SymmetricProperty. ';
        $query = $query . 'BIND(owl:SymmetricProperty AS ?char). ';
        $query = $query . '{' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION {FILTER NOT EXISTS{' . $fullURI . ' rdf:range ?ran.}} ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:AsymmetricProperty. ';
        $query = $query . 'BIND(owl:SymmetricProperty AS ?char). ';
        $query = $query . '{' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION {FILTER NOT EXISTS{' . $fullURI . ' rdf:range ?ran.}} ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:InverseFunctionalProperty. ';
        $query = $query . 'BIND(owl:InverseFunctionalProperty AS ?char). ';
        $query = $query . '{' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION {FILTER NOT EXISTS{' . $fullURI . ' rdf:range ?ran.}}';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:IrreflexiveProperty. ';
        $query = $query . 'BIND(owl:IrreflexiveProperty AS ?char). ';
        $query = $query . '{' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION {FILTER NOT EXISTS{' . $fullURI . ' rdf:range ?ran.}} ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:ReflexiveProperty. ';
        $query = $query . 'BIND(owl:ReflexiveProperty AS ?char). ';
        $query = $query . '{' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION {FILTER NOT EXISTS{' . $fullURI . ' rdf:range ?ran.}}';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . '  owl:inverseOf ?prop2. ';
        $query = $query . 'BIND(owl:inverseOf AS ?char). ';
        $query = $query . '{' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION {FILTER NOT EXISTS{' . $fullURI . ' rdf:range ?ran.}} ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . '  owl:equivalentProperty ?prop2. ';
        $query = $query . 'BIND(owl:equivalentProperty AS ?char). ';
        $query = $query . '{' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION {FILTER NOT EXISTS{' . $fullURI . ' rdf:range ?ran.}} ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:ObjectProperty. ';
        $query = $query . 'BIND(owl:ObjectProperty AS ?type). ';
        $query = $query . $fullURI . '  rdf:subPropertyOf ?prop2. ';
        $query = $query . 'BIND(rdf:subPropertyOf AS ?char). ';
        $query = $query . '{' . $fullURI . ' rdf:range ?ran.} ';
        $query = $query . 'UNION {FILTER NOT EXISTS{' . $fullURI . ' rdf:range ?ran.}} ';
        $query = $query . '}}UNION{{ ';
        $query = $query . $fullURI . '  rdfns:type owl:DatatypeProperty. ';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:FunctionalProperty. ';
        $query = $query . 'BIND(owl:FunctionalProperty AS ?char). ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:DatatypeProperty. ';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:TransitiveProperty. ';
        $query = $query . 'BIND(owl:TransitiveProperty AS ?char). ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . ' rdfns:type owl:DatatypeProperty. ';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:SymmetricProperty. ';
        $query = $query . 'BIND(owl:SymmetricProperty AS ?char). ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:DatatypeProperty. ';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:AsymmetricProperty. ';
        $query = $query . 'BIND(owl:SymmetricProperty AS ?char). ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:DatatypeProperty. ';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:InverseFunctionalProperty. ';
        $query = $query . 'BIND(owl:InverseFunctionalProperty AS ?char). ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:DatatypeProperty. ';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:IrreflexiveProperty. ';
        $query = $query . 'BIND(owl:IrreflexiveProperty AS ?char). ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:DatatypeProperty. ';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . $fullURI . '  rdfns:type owl:ReflexiveProperty. ';
        $query = $query . 'BIND(owl:ReflexiveProperty AS ?char). ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:DatatypeProperty. ';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . $fullURI . '  owl:inverseOf ?prop2. ';
        $query = $query . 'BIND(owl:inverseOf AS ?char). ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:DatatypeProperty. ';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . $fullURI . '  owl:equivalentProperty ?prop2. ';
        $query = $query . 'BIND(owl:equivalentProperty AS ?char). ';
        $query = $query . '}UNION{ ';
        $query = $query . $fullURI . '  rdfns:type owl:DatatypeProperty. ';
        $query = $query . 'BIND(owl:DatatypeProperty AS ?type). ';
        $query = $query . $fullURI . '  rdf:subPropertyOf ?prop2. ';
        $query = $query . 'BIND(rdf:subPropertyOf AS ?char). }}}';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //Ficheiro XSL a ser usado para a transformação do XML
        $xslfile = "http://localhost/assets/xsl/informacoes_propriedade.xsl";   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.
        $result = $this->sendQuery($query, $xslfile);

        print_r($result);
    }

    public function getClassProperty($classe) {
        //Obter a URI completa e adicionar a variável $classe
        $ontologyURI = $this->getURI();
        $fullURI = '<' . $ontologyURI . '#' . $classe . '>';

        $query = 'prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'prefix owl: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'prefix xml: <http://www.w3.org/2001/XMLSchema#> ';
        $query = $query . 'SELECT (strafter(str(?onProperty), "#") AS ?Propriedade) (strafter(str(?someValuesFrom), "#") AS ?AlgunsValoresDe) ';
        $query = $query . 'WHERE{ ';
        $query = $query . $fullURI . ' rdfs:subClassOf ?blankNode. ';
        $query = $query . '?blankNode owl:onProperty ?onProperty. ';
        $query = $query . '{?blankNode owl:someValuesFrom ?someValuesFrom. FILTER (!isBlank(?someValuesFrom)). } ';
        $query = $query . 'UNION  ';
        $query = $query . '{?blankNode owl:onDataRange ?someValuesFrom. }}  ';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //Ficheiro XSL a ser usado para a transformação do XML
        $xslfile = "http://localhost/assets/xsl/tabela_propriedades_classes.xsl";   // -> endereço do ficheiro XSL a ser utilizado para a transformação do XML para HTML.

        $result = $this->sendQuery($query, $xslfile);

        print_r($result);
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

    /********************************************************
     *                  INSERÇÕES FUSEKI                    *
     ********************************************************/

    public function insertData($type, $subject, $object) {
        //obter uri da ontologia.
        $full_uri = $this->getURI();
        //criação da uri com o sujeito.
        $subject_uri = "<" . $full_uri . '#' . $subject . ">";
        //criação da uri com o objecto.
        $object_uri = "<" . $full_uri . '#' . $object . ">";

        if ($type == 'teste') {
            //Teste para verificar se a URI é obtida correctamente.
            $this->testURI($type, $full_uri, $subject_uri, $object_uri);
            exit;
        } else if ($type == 'membro') {
            //Adição de membros.
            $result = $this->insertMember($subject_uri, $object_uri);
            print_r($result);
            exit;
        } else if ($type == 'subclasse') {
            //Adição de subclasses
            $result = $this->insertClass($subject_uri, $object_uri);
            print_r($result);
            exit;
        } else if ($type == 'comentario') {
            //Adição de um comentário.
            $result = $this->insertCommentary($subject_uri, $object);
            print_r($result);
            exit;
        } else {
            print_r("Erro: Tipo n&atilde;o reconhecido...");
            exit;
        }
    }

    public function insertProperty($type, $subject, $predicate, $object, $range, $tipoRange) {
        //Obter a URI da ontologia.
        $full_uri = $this->getURI();
        //Criação da URI do sujeito.
        $subject_uri = "<" . $full_uri . "#" . $subject . ">";

        if ($type == 'fixo') {
            //Ainda não desenvolvido.
            print_r("Erro: Ainda n&atilde;o desenvolvido...");
            exit;
        } else if ($type == 'naoFixo') {
            //Chamada da função privada para inserção de uma propriedade não fixa.
            $result = $this->insertNotFixedProperty($subject_uri, $object, $range, $tipoRange);
            //Impressão do resultado.
            print_r($result);
            exit;
        } else if ($type == 'membro') {
            //Chamada da função privada para inserção de uma propriedade num membro.
            $result = $this->insertMemberProperty($subject_uri, $predicate, $object, $tipoRange);
            //Impressão do resultado.
            print_r($result);
            exit;
        } else if ($type == 'novo1') {
            //Chamada da função privada para inserção de uma nova propriedade (Parte 1).
            $result = $this->insertNewPropertyStep1($subject_uri, $object);
            //Impressão do resultado.
            print_r($result);
            exit;
        } else if ($type == 'novo2') {
            //Chamada da função privada para inserção de uma nova propriedade (Parte 2).
            $result = $this->insertNewPropertyStep2($subject_uri, $predicate, $object);
            //Impressão do resultado.
            print_r($result);
            exit;
        } else if ($type == 'visibilidade') {
            //Chamada da função privada para inserção da propriedade temVisibilidade.
            $result = $this->insertVisibilityProperty();
            //Impressão do resultado.
            print_r($result);
            exit;
        } else if ($type == 'visibilidadeValor') {
            //Chamada da função privada.
            $result = $this->insertVisibilityPropertyValue($subject_uri, $object);
            print_r($result);
            exit;
        } else {
            print_r("Erro: Tipo de propriedade n&atilde;o reconhecido...");
            exit;
        }
    }

    /********************************************************
     *                  ELIMINAÇÕES FUSEKI                  *
     ********************************************************/

    public function deleteData(nk$type, $subject, $object) {
        //Obter a URI da ontologia.
        $full_uri = $this->getURI();
        //Criação da URI do sujeito.
        $subject_uri = "<" . $full_uri . '#' . $subject . ">";

        if ($type == 'classe') {
            //Eliminação de uma classe.
            //Criação da URI do objecto.
            $object_uri = "<" . $full_uri . '#' . $object . ">";
            //Chamada da função privada deleteClass.
            $result = $this->deleteClass($subject_uri, $object_uri);
            //Impressão do resultado.
            print_r($result);
            exit;
        } else if ($type == 'membro') {
            //Eliminação de um membro.
            //Criação da URI do objecto.
            $object_uri = "<" . $full_uri . '#' . $object . ">";
            //Chamada da função privada deleteMember.
            $result = $this->deleteMember($subject_uri, $object_uri);
            //Impressão do resultado.
            print_r($result);
            exit;
        } else if ($type == 'comentario') {
            //Eliminação de um comentário
            $result = $this->deleteCommentary($subject_uri, $object);
            //Impressão do resultado.
            print_r($result);
            exit;
        } else {
            print_r("Erro: Tipo n&atilde;o reconhecido...");
            exit;
        }
    }

    public function deleteProperty($type, $subject, $predicate, $object, $range, $tipoRange) {
        //Obter a URI da ontologia.
        $full_uri = $this->getURI();
        //Criação da URI do sujeito.
        $subject_uri = "<" . $full_uri . "#" . $subject . ">";

        if ($type == 'fixo') {
            //Ainda não desenvolvido.
            print_r("Erro: Ainda n&atilde;o desenvolvido...");
            exit;
        } else if ($type == 'naoFixo') {
            //Ainda não desenvolvido.
            print_r("Erro: Ainda n&atilde;o desenvolvido...");
            exit;
        } else if ($type == 'membro') {
            //Chamada da função privada para eliminação de uma propriedade num membro.
            $result = $this->deletePropertyMember($subject, $predicate, $object);
            //Impressão do resultado.
            print_r($result);
            exit;
        } else if ($type == 'visibilidade') {
            //Chamada da função privada para eliminação da propriedade temVisibilidade.
            $result = $this->deleteVisibilityPropertyValue($subject_uri, $object);
            //Impressão do resultado.
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
                $url_fuseki = $url_fuseki . $aux[1];
                //Remover possíveis espaços
                $url_fuseki = preg_replace('/\s+/', '', $url_fuseki);
            }

            $this->url_db_consult = $url_fuseki . "/sparql";
            $this->url_db_insert = $url_fuseki . "/update";
        }
    }

    /********************************************************
     *                  INSERÇÕES FUSEKI                    *
     ********************************************************/

    private function insertClass($subject_uri, $object1_uri) {
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

        //Definição da URI do predicado RDF:comment.
        $predicate_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#comment>";

        //Enviar os argumentos para a função privada sendInsert.
        $result = $this->sendInsert($subject_uri, $predicate_uri, $object);

        return $result;
    }

    private function insertFixedProperty() {
        //Função ainda não desenvolvida.
    }

    private function insertNotFixedProperty($subject_uri, $object, $range, $tipoRange) {
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

        //Definição da URI da ontologia.
        $full_uri = $this->getURI();
        //Definição da URI do objecto.
        $object_uri = "<" . $full_uri . "#" . $object . ">";

        if ($tipoRange == "null") {
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
        } else {
            $range_uri = "<http://www.w3.org/2001/XMLSchema#" . $tipoRange . ">";
            $range_uri2 = "\"" . $range . "\" ^^<http://www.w3.org/2001/XMLSchema#" . $tipoRange . ">";

            //Construcção dos argumentos para enviar a função do model.
            $argumentos = $subject_uri . " <http://www.w3.org/2000/01/rdf-schema#subClassOf> _:foo";
            $argumentos = $argumentos . " _:foo <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://www.w3.org/2002/07/owl#Restriction>";
            $argumentos = $argumentos . " _:foo <http://www.w3.org/2002/07/owl#onDataRange> " . $range_uri;
            $argumentos = $argumentos . " _:foo <http://www.w3.org/2002/07/owl#onProperty> " . $object_uri;
            $argumentos = $argumentos . " _:foo <http://www.w3.org/2002/07/owl#qualifiedCardinality> " . $range_uri2;
            //Envio para a função insert_data_2 que recebe vários argumentos.
            $result = $this->pesti_model->inserir_data_2($this->url_db_insert, $argumentos);
            return $result;
        }
    }

    private function insertMemberProperty($subject_uri, $predicate, $object, $tipoRange) {
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

        //Definição da URI da ontologia.
        $full_uri = $this->getURI();
        //Criação da URI do predicado.
        $predicate_uri = "<" . $full_uri . "#" . $predicate . ">";
        if ($tipoRange == "null") {
            //Criação da URI do objecto.
            $object_uri = "<" . $full_uri . "#" . $object . ">";
            //Enviar as variáveis para a função privada sendInsert.
            $result = $this->sendInsert($subject_uri, $predicate_uri, $object_uri);

            return $result;
        } else {
            $object_uri = "\"" . $object . "\" ^^<http://www.w3.org/2001/XMLSchema#" . $tipoRange . ">";
            //Enviar as variáveis para a função privada sendInsert.
            $result = $this->sendInsert($subject_uri, $predicate_uri, $object_uri);

            return $result;
        }
    }

    private function insertNewPropertyStep1($subject_uri, $object) {
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

        //Definição da URI do predicado RDF:type.
        $predicate_uri = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>";
        //Definição da URI do objecto.
        $object_uri = "<http://www.w3.org/2002/07/owl#" . $object . ">";

        $result = $this->sendInsert($subject_uri, $predicate_uri, $object_uri);

        return $result;
    }

    private function insertNewPropertyStep2($subject_uri, $predicate, $object) {
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

    private function insertVisibilityProperty() {
        /*
         * PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
         * PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
         * PREFIX owl: <http://www.w3.org/2002/07/owl#>
         * PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
         * PREFIX myOWL: <http://www.semanticweb.org/ontologies/2012/3/Ontology1334263618896.owl#>
         * 
         * INSERT DATA {
         * myOWL:temVisibilidade rdf:type owl:DatatypeProperty.
         * myOWL:temVisibilidade rdfs:range xsd:boolean.
         * myOWL:temVisibilidade rdf:type owl:functionalProperty.
         * myOWL:temVisibilidade rdfs:domain owl:class.
         *
         * }
         */

        //Definição da URI da ontologia.
        $uri = $this->getURI();

        //Definição da URI da propriedade temVisibilidade.
        $temVisibilidade = "<" . $uri . "#temVisibilidade>";

        //Construção dos argumentos para inserção do valor e propriedade na classe
        $argumentos = $temVisibilidade . " <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://www.w3.org/2002/07/owl#DatatypeProperty>. ";
        $argumentos = $argumentos . $temVisibilidade . " <http://www.w3.org/2000/01/rdf-schema#range> <http://www.w3.org/2001/XMLSchema#boolean>. ";
        $argumentos = $argumentos . $temVisibilidade . " <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://www.w3.org/2002/07/owl#functionalProperty>. ";
        $argumentos = $argumentos . $temVisibilidade . " <http://www.w3.org/2000/01/rdf-schema#domain> <http://www.w3.org/2002/07/owl#class>. ";

        //Envio para a função insert_data_2 que recebe vários argumentos.
        $result = $this->pesti_model->inserir_data_2($this->url_db_insert, $argumentos);

        return $result;
    }

    private function insertVisibilityPropertyValue($subject_uri, $value) {
        /*
         * SPARQL Query
         * 
         * PREFIX myOWL: <http://www.semanticweb.org/ontologies/2012/3/Ontology1334263618896.owl#>
         * 
         * INSERT DATA{
         * myOWL:Specs myOWL:temVisibiliade "TRUE" ^^<http://www.w3.org/2001/XMLSchema#boolean>.
         * }
         */

        //Definição da URI da ontologia.
        $uri = $this->getURI();

        //Definição da URI da propriedade temVisibilidade.        
        $temVisibilidade = "<" . $uri . "#temVisibilidade>";

        //Definição da URI do valor de temVisibilidade.
        $value_uri = ' "' . $value . '" ^^<http://www.w3.org/2001/XMLSchema#boolean>. ';

        //Envio para a função privada sendInsert
        $result = $this->sendInsert($subject_uri, $temVisibilidade, $value_uri);

        return $result;
    }

    /********************************************************
     *                  ELIMINAÇÕES FUSEKI                  *
     ********************************************************/

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

    private function deleteVisibilityPropertyValue($subject_uri, $value) {
        //Definição da URI da ontologia.
        $uri = $this->getURI();

        //Definição da URI da propriedade temVisibilidade.        
        $temVisibilidade = "<" . $uri . "#temVisibilidade>";

        //Definição da URI do valor de temVisibilidade.
        $value_uri = ' "' . $value . '"^^<http://www.w3.org/2001/XMLSchema#boolean>. ';



        //Envio para a função privada sendDelte
        $result = $this->sendDelete($subject_uri, $temVisibilidade, $value_uri);

        return $result;
    }

    private function deletePropertyMember($subject, $predicate, $object) {
        //Obter o tipo de datatype da váriavel presente no object.
        $type = $this->getTipoDatatype($subject, $predicate, $object);
        //Definição da URI da ontologia.
        $full_uri = $this->getURI();
        //Criação da URI do sujeito.
        $subject_uri = "<" . $full_uri . "#" . $subject . ">";
        //Criação da URI do predicado.
        $predicate_uri = "<" . $full_uri . "#" . $predicate . ">";

        if ($type == "uri") {
            //Se não for datatype.
            //Criação da URI do objecto.
            $object_uri = "<" . $full_uri . "#" . $object . ">";
            //Enviar as variáveis para a função privada sendInsert.
            $result = $this->sendDelete($subject_uri, $predicate_uri, $object_uri);
            return $result;
        } else {
            //Se for datatype.
            //Criação da URI do objecto. (ex: "116.99" ^^<...>)
            $object_uri = '"' . $object . '" ^^<' . $type . '>';
            //Enviar as variáveis para a função privada sendInsert.
            $result = $this->sendDelete($subject_uri, $predicate_uri, $object_uri);
            return $result;
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

    private function getTipoDatatype($membro, $prop, $valor) {
        //Obter a URI completa e adicionar a variável $classeMae
        $ontologyURI = $this->getURI();
        $membroURI = '<' . $ontologyURI . '#' . $membro . '>';
        $propURI = '<' . $ontologyURI . '#' . $prop . '>';

        $query = 'prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> ';
        $query = $query . 'prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'prefix owl: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'SELECT ?tipo ';
        $query = $query . 'WHERE{ ' . $membroURI . ' ' . $propURI . ' ?tipo. ';
        $query = $query . 'FILTER (contains(str(?tipo), "' . $valor . '")).} ';

        //variável XML recebe o resultado da query obtido do método presente no modelo
        $xml = $this->pesti_model->consultar_data($this->url_db_consult, $query);

        //Obter o tipo de propriedade
        $getLiteral = explode("datatype=\"", $xml);
        $getLiteral2 = explode("\"", $getLiteral[0]);

        return $getLiteral2[15];
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

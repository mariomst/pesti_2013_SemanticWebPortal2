<?php

/*
 * Read Controller
 * - Controller responsável pelas inserções feitas na ontologia.
 * 
 * Versão 1.0
 * 
 * @author Mário Teixeira   1090626     1090626@isep.ipp.pt
 * @author Marta Graça      1100640     1100640@isep.ipp.pt
 * 
 * =============================   Descrição: ==================================
 * Funções Públicas:
 * + view_insertClass         -> criação da view Inserção.
 * + insertData               -> inserção de novos dados na ontologia.
 * + insertProperty           -> inserção de propriedades em elementos da ontologia.
 * 
 * Funções Privadas:
 * - readConfigFile           -> carrega o endereço do servidor Fuseki apartir de um ficheiro .ini.
 * - insertClass              -> inserção de uma classe.
 * - insertMember             -> inserção de um membro.
 * - insertCommentary         -> inserção de um comentário.
 * - insertFixedProperty      -> inserção de propriedades fixas em classes.
 * - insertNotFixedProperty   -> inserção de propriedades não fixas em classes.
 * - insertMemberProperty     -> inserção de propriedades em membros.
 * - insertNewPropertyStep1   -> inserção do tipo de propriedade.
 * - insertNewPropertyStep2   -> inserção dos elementos para o respectivo tipo de propriedade.
 * - insertVisibilityProperty -> inserção da propriedade temVisibilidade numa classe.
 * - sendInsert               -> envio da query de inserção para o Fuseki (esse processo é tratado pelo modelo).
 * - getURI                   -> retorna de forma dinamica a uri da ontologia.
 * - getTipoDatatype          -> retorna o tipo de datatype da propriedade indicada.
 * - useXSLT                  -> carrega o xsl indicado e processa à transformação do xml indicado.
 */

//Configurações do PHP
error_reporting(1);         // -> 0 - desactivo; 1 - activo.

class Insert_Controller extends CI_Controller {
    //================= Variaveis Globais ===================//
    protected $url_db_consult = "";     // -> endereço do Fuseki para consultas
    protected $url_db_insert = "";      // -> endereço do Fuseki para inserções
    
    //================= Funções Públicas ====================//
    public function __construct() {
        parent::__construct();
        $this->load->model('pesti_model');
        $this->readConfigFile();
    }
    
    public function viewInsertClass($page = 'inserir') {
        if (!file_exists('application/views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = ucfirst($page);

        $this->load->view('pages/' . $page, $data);
    }
    
    /**********************************************************
     *                   INSERÇÕES FUSEKI                     *
     **********************************************************/
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
                if($aux[0] == 'url_fuseki ' || $aux[0] == 'dataset '){
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
     *                  COMUNICAÇÃO MODEL                   *
     ********************************************************/
    private function sendInsert($subject, $predicate, $object) {
        //Variável result recebe 1 se a inserção for com sucesso.
        $result = $this->pesti_model->inserir_data($this->url_db_insert, $subject, $predicate, $object);
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
 


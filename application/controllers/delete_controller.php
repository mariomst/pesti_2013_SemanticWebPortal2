<?php

/*
 * Delete Controller
 * - Controller responsável pelas eliminações feitas na ontologia.
 * 
 * Versão 1.0
 * 
 * @author Mário Teixeira   1090626     1090626@isep.ipp.pt
 * @author Marta Graça      1100640     1100640@isep.ipp.pt
 * 
 * =============================   Descrição: ==================================
 * Funções Públicas:
 * + deteleData               -> eliminação de dados da ontologia.
 * 
 * Funções Privadas:
 * - deleteClass              -> eliminação da classe.
 * - deleteMember             -> eliminação do membro.
 * - deleteCommentary         -> eliminação do comentário.
 * - deleteVisibilityProperty -> eliminação da propriedade temVisibilidade numa classe.
 * - sendDelete               -> envio da query de eliminação para o Fuseki (esse processo é tratado pelo modelo).
 */

//Configurações do PHP
error_reporting(1);         // -> 0 - desactivo; 1 - activo.

class Delete_Controller extends CI_Controller {
    //================= Variaveis Globais ===================//
    protected $url_db_consult = "";     // -> endereço do Fuseki para consultas
    protected $url_db_insert = "";      // -> endereço do Fuseki para inserções
    
    //================= Funções Públicas ====================// 
    public function __construct() {
        parent::__construct();
        $this->load->model('pesti_model');
        $this->readConfigFile();
    }
    
    /********************************************************
     *                  ELIMINAÇÕES FUSEKI                  *
     ********************************************************/
    public function deleteData($type, $subject, $object) {
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
}

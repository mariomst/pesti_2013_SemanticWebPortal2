<?php

/*
 * Insert Model
 * - Modelo responsável pelas inserções feitas à ontologia.
 * 
 * Versão 1.0
 * 
 * @author Mário Teixeira   1090626     1090626@isep.ipp.pt
 * @author Marta Graça      1100640     1100640@isep.ipp.pt
 * 
 * Funções Públicas:
 * + insertClass                    -> inserção de uma classe.
 * + insertMember                   -> inserção de um membro.
 * + insertCommentary               -> inserção de um comentário.
 * + insertFixedProperty            -> inserção de propriedades fixas em classes.
 * + insertNonFixedProperty         -> inserção de propriedades não fixas em classes.
 * + insertMemberProperty           -> inserção de propriedades em membros.
 * + insertNewPropertyStep1         -> inserção do tipo de propriedade.
 * + insertNewPropertyStep2         -> inserção dos elementos para o respectivo tipo de propriedade.
 * + insertVisibilityProperty       -> inserção da propriedade temVisibilidade numa classe.
 * + insertVisibilityPropertyValue  -> inserção do valor da propriedade temVisibilidade numa classe.
 * 
 * Funções Privadas:
 * - SetAddress                     -> define o endereço do servidor Fuseki e o repositório a ser utilizado.
 * - getOntologyURI                 -> define a URI da ontologia existente no repositório
 * - consultData                    -> instruções para efetuar os pedidos ao servidor FUSEKI 
 * - updateData                     -> instruções para efetuar os pedidos ao servidor FUSEKI 
 */

class Insert_Model extends CI_Model {

    //Variáveis Globais
    protected $url_db_consult = ""; //endereço fuseki para consultas.
    protected $url_db_update = ""; //endereço fuseki para updates.
    protected $ontologyURI = ""; //URI da ontologia.

    //Funções Públicas
    public function __construct() {
        parent::__construct();
        $this->load->helper('portal_helper');
        $this->setAddress();
        $this->getOntologyURI();
    }

    public function insertClass($subject, $object) {
        //Criação da URI do sujeito.
        $subjectURI = "<" . $this->ontologyURI . '#' . $subject . ">";
        //Criação da URI do objecto.
        $objectURI = "<" . $this->ontologyURI . '#' . $object . ">";

        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('inserts/query_insertClass');

        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1 e $argumento2
        $tq1 = str_replace('$argumento1', $subjectURI, $query);
        $tq2 = str_replace('$argumento2', $objectURI, $tq1);

        //Execução da query.
        $result = $this->updateData($this->url_db_update, $tq2);
        
        if ($result == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function insertMember($subject, $object) {
        //Criação da URI do sujeito.
        $subjectURI = "<" . $this->ontologyURI . '#' . $subject . ">";
        //Criação da URI do objecto.
        $objectURI = "<" . $this->ontologyURI . '#' . $object . ">";

        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('inserts/query_insertMember');

        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1 e $argumento2
        $tq1 = str_replace('$argumento1', $subjectURI, $query);
        $tq2 = str_replace('$argumento2', $objectURI, $tq1);

        //Execução da query.
        $result = $this->updateData($this->url_db_update, $tq2);

        if ($result == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function insertCommentary($subject, $object) {
        //Criação da URI do sujeito.
        $subjectURI = "<" . $this->ontologyURI . '#' . $subject . ">";
        
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('inserts/query_insertCommentary');
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1 e $argumento2
        $tq1 = str_replace('$argumento1', $subjectURI, $query);
        $tq2 = str_replace('$argumento2', $object, $tq1);
        
        //Execução da query.
        $result = $this->updateData($this->url_db_update, $tq2);

        if ($result == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function insertFixedProperty(){
        //Função não implementada.
        return false;
    }
    
    public function insertNonFixedProperty($subject, $object, $range, $rangeType){
        //Criação da URI do sujeito.
        $subjectURI = "<" . $this->ontologyURI . '#' . $subject . ">";
        //Criação da URI do sujeito.
        $objectURI = "<" . $this->ontologyURI . '#' . $object . ">";
        
        if($rangeType == "null") {
            //Criação da URI do range.
            $rangeURI = "<" . $this->ontologyURI . '#' . $range . ">";
            //Carregar query através do ficheiro externo indicado.
            $query = readQueryFile('inserts/query_insertNonFixedProperty_1');
            //Substituir na query obtida pelo ficheiro todas as instancias $argumento1, $argumento2 e $argumento3
            $tq1 = str_replace('$argumento1', $subjectURI, $query);
            $tq2 = str_replace('$argumento2', $rangeURI, $tq1);
            $tq3 = str_replace('$argumento3', $objectURI, $tq2);
        } else {
            //Criação da URI do rangeType.
            $rangeTypeURI = "<http://www.w3.org/2001/XMLSchema#" . $rangeType . ">";
            //Criação da URI do range.
            $rangeURI = "\"" . $range . "\" ^^<http://www.w3.org/2001/XMLSchema#" . $rangeType . ">";            
            //Carregar query através do ficheiro externo indicado.
            $query = readQueryFile('inserts/query_insertNonFixedProperty_2');
            //Substituir na query obtida pelo ficheiro todas as instancias $argumento1, $argumento2 e $argumento3
            $tq0 = str_replace('$argumento1', $subjectURI, $query);
            $tq1 = str_replace('$argumento2', $rangeURI, $tq0);
            $tq2 = str_replace('$argumento3', $objectURI, $tq1);
            $tq3 = str_replace('$argumento4', $rangeTypeURI, $tq2);
        }      
        
        //Execução da query.
        $result = $this->updateData($this->url_db_update, $tq3);

        if ($result == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function insertMemberProperty($subject, $predicate, $object, $rangeType){
        //Criação da URI do sujeito.
        $subjectURI = "<" . $this->ontologyURI . '#' . $subject . ">";
        //Criação da URI do predicado.
        $predicateURI = "<" . $this->ontologyURI . '#' . $predicate . ">";
        
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('inserts/query_insertMemberProperty');      
        
        if($rangeType == "null") {
            //Criação da URI do objecto.
            $objectURI = "<" . $this->ontologyURI . '#' . $object . ">";                        
        } else {
            //Criação da URI do objecto.
            $objectURI = "\"" . $object . "\" ^^<http://www.w3.org/2001/XMLSchema#" . $rangeType . ">";
        }        
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1, $argumento2 e $argumento3
        $tq1 = str_replace('$argumento1', $subjectURI, $query);
        $tq2 = str_replace('$argumento2', $predicateURI, $tq1);
        $tq3 = str_replace('$argumento3', $objectURI, $tq2);
        
        //Execução da query.
        $result = $this->updateData($this->url_db_update, $tq3);

        if ($result == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function insertNewPropertyStep1($subject, $object) {
        //Criação da URI do sujeito.
        $subjectURI = "<" . $this->ontologyURI . '#' . $subject . ">";
        //Criação da URI do objecto.
        $objectURI = "<http://www.w3.org/2002/07/owl#" . $object . ">";
        
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('inserts/query_insertNewPropertyStep1');
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1 e $argumento2
        $tq1 = str_replace('$argumento1', $subjectURI, $query);
        $tq2 = str_replace('$argumento2', $objectURI, $tq1);
        
        //Execução da query.
        $result = $this->updateData($this->url_db_update, $tq2);

        if ($result == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function insertNewPropertyStep2($subject, $predicate, $object) {
        //Criação da URI do sujeito.
        $subjectURI = "<" . $this->ontologyURI . '#' . $subject . ">";
                
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('inserts/query_insertNewPropertyStep2');
        
        //Verificação se o $predicate é do tipo "type" ou outro.
        if($predicate != "type") {
            //Criação da URI do objecto.
            $objectURI = "<" . $this->ontologyURI . '#' . $object . ">";
            //Verificação se é inverseOf ou equivalentPropery.
            if($predicate == "inverseOf" || $predicate == "equivalentProperty") {
                //Criação da URI do predicado.
                $predicateURI = "<http://www.w3.org/2002/07/owl#" . $predicate . ">";
                //Verificação se é range ou subPropertyOf.
            } else if ($predicate == "range" || $predicate == "subPropertyOf") {
                //Criação da URI do predicado.
                $predicateURI = "<http://www.w3.org/2000/01/rdf-schema#" . $predicate . ">";
            } else {
                print_r("Erro: Predicado n&atilde;o reconhecido...");
                exit;
            }
        } else {
            //Criação da URI do predicado RDF:type.
            $predicateURI = "<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>";
            //Criação da URI do objecto.
            $objectURI = "<http://www.w3.org/2002/07/owl#" . $object . ">";
        }
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1 e $argumento2
        $tq1 = str_replace('$argumento1', $subjectURI, $query);
        $tq2 = str_replace('$argumento2', $predicateURI, $tq1);
        $tq3 = str_replace('$argumento3', $objectURI, $tq2);
        
        //Execução da query.
        $result = $this->updateData($this->url_db_update, $tq3);

        if ($result == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function insertVisibilityProperty(){
        //Criação da URI da propriedade temVisibilidade.
        $temVisibilidade = "<" . $this->ontologyURI . "#temVisibilidade>";
        
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('inserts/query_insertVisibilityProperty');
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1
        $tq1 = str_replace('$argumento1', $temVisibilidade, $query);
        
        //Execução da query.
        $result = $this->updateData($this->url_db_update, $tq1);

        if ($result == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function insertVisibilityPropertyValue($subject, $value) {
        //Criação da URI do sujeito.
        $subjectURI = "<" . $this->ontologyURI . '#' . $subject . ">";
        //Criação da URI da propriedade temVisibilidade.
        $temVisibilidade = "<" . $this->ontologyURI . "#temVisibilidade>";
        //Criação da URI do valor de temVisibilidade.
        $valueURI = ' "' . $value . '" ^^<http://www.w3.org/2001/XMLSchema#boolean>';
        
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('inserts/query_insertVisibilityPropertyValue');
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1
        $tq1 = str_replace('$argumento1', $subjectURI, $query);
        $tq2 = str_replace('$argumento2', $temVisibilidade, $tq1);
        $tq3 = str_replace('$argumento3', $valueURI, $tq2);
        
        //Execução da query.
        $result = $this->updateData($this->url_db_update, $tq3);

        if ($result == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    //Funções Privadas
    private function setAddress() {
        $url = readConfigFile();
        $this->url_db_consult = $url . "/sparql";
        $this->url_db_update = $url . "/update";
    }

    private function getOntologyURI() {
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

    private function updateData($url, $query) {
        $post = curl_init();
        $fields = 'update=' . $query;

        curl_setopt($post, CURLOPT_URL, $url);
        curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($post);

        curl_close($post);

        return $response;
    }
}

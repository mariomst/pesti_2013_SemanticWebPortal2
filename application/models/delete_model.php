<?php

/*
 * Delete Model
 * - Modelo responsável pelas eliminações feitas à ontologia.
 * 
 * Versão 1.0
 * 
 * @author Mário Teixeira   1090626     1090626@isep.ipp.pt
 * @author Marta Graça      1100640     1100640@isep.ipp.pt
 * 
 * Funções Públicas:
 * + deleteClass                    -> eliminação da classe.
 * + deleteMember                   -> eliminação do membro.
 * + deleteCommentary               -> eliminação do comentário.
 * + deleteVisibilityPropertyValue  -> eliminação do valor da propriedade temVisibilidade da classe.
 * + insertMemberProperty           -> eliminação de propriedades em membros.
 * 
 * Funções Privadas:
 * - SetAddress                     -> define o endereço do servidor Fuseki e o repositório a ser utilizado.
 * - getOntologyURI                 -> define a URI da ontologia existente no repositório
 * - consultData                    -> instruções para efetuar os pedidos ao servidor FUSEKI 
 * - updateData                     -> instruções para efetuar os pedidos ao servidor FUSEKI 
 */

class Delete_Model extends CI_Model {
    
    //Váriaveis Globais
    protected $url_db_consult = ""; //endereço fuseki para consultas.
    protected $url_db_update = ""; //endereço fuseki para updates.
    protected $ontologyURI = ""; //URI da ontologia.
    
    //Funções públicas
    public function __construct() {
        parent::__construct();
        $this->load->helper('portal_helper');
        $this->setAddress();
        $this->getOntologyURI();
    }
    
    public function deleteClass($subject, $object) {
        //Criação da URI do sujeito.
        $subjectURI = "<" . $this->ontologyURI . '#' . $subject . ">";
        //Criação da URI do objecto.
        $objectURI = "<" . $this->ontologyURI . '#' . $object . ">";

        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('deletes/query_deleteClass');

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
    
    public function deleteMember($subject, $object) {
        //Criação da URI do sujeito.
        $subjectURI = "<" . $this->ontologyURI . '#' . $subject . ">";
        //Criação da URI do objecto.
        $objectURI = "<" . $this->ontologyURI . '#' . $object . ">";

        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('deletes/query_deleteMember');

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
    
    public function deleteCommentary($subject, $object) {
        //Criação da URI do sujeito.
        $subjectURI = "<" . $this->ontologyURI . '#' . $subject . ">";
        
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('deletes/query_deleteCommentary');
        
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
    
    public function deleteVisibilityPropertyValue($subject, $value) {
        //Criação da URI do sujeito.
        $subjectURI = "<" . $this->ontologyURI . '#' . $subject . ">";
        //Criação da URI da propriedade temVisibilidade.
        $temVisibilidade = "<" . $this->ontologyURI . "#temVisibilidade>";
        //Criação da URI do valor de temVisibilidade.
        $valueURI = ' "' . $value . '" ^^<http://www.w3.org/2001/XMLSchema#boolean>';
        
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('deletes/query_deleteVisibilityPropertyValue');
        
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

    public function deletePropertyMember($subject, $predicate, $object, $rangeType) {
        //Criação da URI do sujeito.
        $subjectURI = "<" . $this->ontologyURI . '#' . $subject . ">";
        //Criação da URI do predicado.
        $predicateURI = "<" . $this->ontologyURI . '#' . $predicate . ">";
        
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('deletes/query_deleteMemberProperty');      
        
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

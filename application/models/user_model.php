<?php

/* 
 * User Model
 * - Modelo responsável pelos pedidos feitos à ontologia relacionados com utilizadores.
 * 
 * Versão 1.0
 * 
 * @author Mário Teixeira   1090626     1090626@isep.ipp.pt
 * @author Marta Graça      1100640     1100640@isep.ipp.pt
 * 
 * Funções Públicas:
 * + checkUserExists        ->  Verifica se o utilizador existe na base de dados.
 * + checkUserPassword      ->  Verifica se a password indicada pelo utilizador esta correta.
 * + getUserAccessLevel     ->  Retorna o nível de utilizador.
 * + listUsers              ->  Lista todos os utlizadores existentes na base de dados.
 * + insertNewUser          ->  Insere um novo utilizador na base de dados.
 * + deleteUser             ->  Elimina um utilizador da base de dados.
 * + getFusekiAddress       ->  Retorna o url do Servidor Fuseki.
 * + checkFusekiStatus      ->  Verifica se o servidor Fuseki esta vivo fazendo um pedido GET.
 * 
 * Funções Privadas:
 * - SetAddress             ->  Define o endereço do servidor Fuseki e o repositório a ser utilizado.
 * - getOntologyURI         ->  Define a URI da ontologia existente no repositório.
 * - consultData            ->  Instruções para efetuar os pedidos ao servidor FUSEKI. 
 * - deleteData             ->  Instruções para efetuar os pedidos ao servidor FUSEKI.
 */

class User_Model extends CI_Model {
    
    //Variáveis Globais
    protected $url_user_db_sparql = "";
    protected $url_user_db_update = "";
    protected $ontologyURI = "";

    //Funções Públicas
    public function __construct() {
        parent::__construct();
        $this->load->helper('portal_helper');
        $this->setAddress();
        $this->getOntologyURI();
    }
    
    public function checkUserExists($user) {
        //Criação da URI do utilizador.
        $userURI = "<" . $this->ontologyURI . '#' . $user . ">";
        //Criação da URI do tipo de utilizador.
        $userType = "<" . $this->ontologyURI . "#Utilizador>";       
     
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('users/query_checkUserExists');
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1 e $argumento2
        $tq1 = str_replace('$argumento1', $userURI, $query);
        $tq2 = str_replace('$argumento2', $userType, $tq1);
        
        //Execução da query.
        $xml = $this->consultData($this->url_user_db_sparql, $tq2);

        //Processamento directo do XML para retirar a resposta.
        $aux1 = explode("<boolean>", $xml);
        $aux2 = $aux1[1];
        $result = explode("</boolean>", $aux2);
        
        if($result[0] == 'true') {
            $exists = 1;
        } else {
            $exists = 0;
        }
        
        return $exists;
    }
    
    public function checkUserPassword($user, $password) {
        $validator = "0";        
        
        $userExists = $this->checkUserExists($user);        
        
        if ($userExists == 1) {
            $userPassword = $this->getUserPassword($user);
            if ($password == $userPassword) {
                $validator = "1";
            }
        }

        return $validator;
    }
    
    public function getUserAccessLevel($user){
        //Criação da URI do utilizador.
        $userURI = "<" . $this->ontologyURI . '#' . $user . ">";
        //Criação da URI da propriedade temNivel
        $levelURI = "<" . $this->ontologyURI . "#temNivel>";
        
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('users/query_getUserAccessLevel');
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1 e $argumento2
        $tq1 = str_replace('$argumento1', $userURI, $query);
        $tq2 = str_replace('$argumento2', $levelURI, $tq1);
        
        //Execução da query.
        $xml = $this->consultData($this->url_user_db_sparql, $tq2);

        //Processamento directo do XML para retirar a URI.
        $aux_1 = explode("<literal>", $xml);
        $aux_2 = $aux_1[1];
        $level = explode("</literal>", $aux_2);
        
        return $level[0];
    }
    
    public function listUsers(){
        //Criação da URI do utilizador.
        $userType = "<" . $this->ontologyURI . "#Utilizador>";
        
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('users/query_listUsers');
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1 pelo $temVisibilidade
        $tq = str_replace('$argumento1', $userType, $query);
        
        //Ficheiro XSL a ser usado para a transformação do XML
        $xslfile = "assets/xsl/tabela_users.xsl";
        
        $xml = $this->consultData($this->url_user_db_sparql, $tq);
        
        if (!$xml) {
            $result = "<br><font color=\"red\"><b>Erro SPARQL: Ocorreu um erro a retornar a informa&ccedil;&atilde;o, verifique se o endere&ccedil;o est&aacute; correcto.</b></font>";
        } else {
            $result = useXSLT($xml, $xslfile);
        }

        return $result;
    }
    
    public function insertNewUser($user, $password) {
        //Definição do nível do utilizador (1 para utilizadores normais).
        $accessLevel = 1;
        //Definição das URIs necessárias.
        $userURI = "<" . $this->ontologyURI . '#' . $user . ">";
        $userType = "<" . $this->ontologyURI . "#Utilizador>";
        $passwordURI = "<" . $this->ontologyURI . "#temPassword>";
        $levelURI = "<" . $this->ontologyURI . "#temNivel>";
        
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('users/query_insertNewUser');

        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1 e $argumento2
        $tq1 = str_replace('$argumento1', $userURI, $query);
        $tq2 = str_replace('$argumento2', $userType, $tq1);
        $tq3 = str_replace('$argumento3', $passwordURI, $tq2);
        $tq4 = str_replace('$argumento4', $password, $tq3);
        $tq5 = str_replace('$argumento5', $levelURI, $tq4);
        $tq6 = str_replace('$argumento6', $accessLevel, $tq5);

        //Execução da query.
        $result = $this->updateData($this->url_user_db_update, $tq6);

        if ($result == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteUser($user) { 
        //Obter nivel e password do user.
        $accessLevel = $this->getUserAccessLevel($user);
        $password = $this->getUserPassword($user);
        
        //Definição das URIs necessárias.
        $userURI = "<" . $this->ontologyURI . '#' . $user . ">";
        $userType = "<" . $this->ontologyURI . "#Utilizador>";
        $passwordURI = "<" . $this->ontologyURI . "#temPassword>";
        $levelURI = "<" . $this->ontologyURI . "#temNivel>";
        
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('users/query_deleteUser');

        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1 e $argumento2
        $tq1 = str_replace('$argumento1', $userURI, $query);
        $tq2 = str_replace('$argumento2', $userType, $tq1);
        $tq3 = str_replace('$argumento3', $passwordURI, $tq2);
        $tq4 = str_replace('$argumento4', $password, $tq3);
        $tq5 = str_replace('$argumento5', $levelURI, $tq4);
        $tq6 = str_replace('$argumento6', $accessLevel, $tq5);

        //Execução da query.
        $result = $this->updateData($this->url_user_db_update, $tq6);

        if ($result == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getFusekiAddress() {
        return $this->url_user_db_sparql;
    }
    
    public function checkFusekiStatus() {
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('users/query_checkFusekiStatus');
        
        $xml = $this->consultData($this->url_user_db_sparql, $query);
        
        if (!$xml) {
            return 0;
        } else {
            return 1;
        }
    }
    
    //Funções Privadas
    private function getUserPassword($user) {
        //Criação da URI do utilizador.
        $userURI = "<" . $this->ontologyURI . '#' . $user . ">";
        //Criação da URI da propriedade temPassword.
        $passwordURI = "<" . $this->ontologyURI . "#temPassword>";
        
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('users/query_getUserPassword');
        
        //Substituir na query obtida pelo ficheiro todas as instancias $argumento1 e $argumento2
        $tq1 = str_replace('$argumento1', $userURI, $query);
        $tq2 = str_replace('$argumento2', $passwordURI, $tq1);
        
        //Execução da query.
        $xml = $this->consultData($this->url_user_db_sparql, $tq2);
        
        //Processamento directo do XML para retirar a URI.
        $aux1 = explode("<literal>", $xml);
        $aux2 = $aux1[1];
        $password = explode("</literal>", $aux2);

        return $password[0];
    }
    
    private function setAddress() {
        $url = readConfigFileUsers();
        $this->url_user_db_sparql = $url . "/sparql";
        $this->url_user_db_update = $url . "/update";
    }

    private function getOntologyURI() {
        $query = readQueryFile('users/query_obtainURI');

        //Variável XML recebe o resultado da query
        $xml = $this->consultData($this->url_user_db_sparql, $query);

        //Processamento directo do XML para retirar a URI.
        $aux_1 = explode("<literal>", $xml);
        $aux_2 = $aux_1[1];
        $uri = explode("</literal>", $aux_2);

        $this->ontologyURI = $uri[0];
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



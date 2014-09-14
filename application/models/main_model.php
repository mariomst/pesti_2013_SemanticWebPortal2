<?php

/*
 * Main Model
 * - Verifica o estado do servidor Fuseki principal.
 *
 * Versão 2.5
 *
 * @author Mário Teixeira
 * @author Marta Graça
 * 
 */

class Main_Model extends CI_Model {

    //Variáveis Globais
    protected $url_db_consult = "";

    //Funções Públicas
    public function __construct() {
        parent::__construct();
        $this->load->helper('portal_helper');
        $this->setAddress();
    }

    public function getFusekiAddress() {
        return $this->url_db_consult;
    }
    
    public function checkFusekiStatus() {
        //Carregar query através do ficheiro externo indicado.
        $query = readQueryFile('users/query_checkFusekiStatus');
        
        $xml = $this->consultData($this->url_db_consult, $query);
        
        if (!$xml) {
            return 0;
        } else {
            return 1;
        }
    }
    
    private function setAddress() {
        $url = readConfigFile();
        $this->url_db_consult = $url . "/sparql";
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

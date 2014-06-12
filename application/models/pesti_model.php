<?php

/*
 * PESTI Model
 * - Envia os pedidos do controller para o Servidor Fuseki e retorna as respostas.
 *
 * Versão 2.2
 *
 * @author Mário Teixeira
 * @author Marta Graça
 *
 * ========================================================   Changelog:   =============================================================================================
 * 1.0   ->  Criação e desenvolvimento das funções enviar_query e obter_xml;
 * 2.0   ->  Criação e desenvolvimento da função inserir_data;
 * 2.1   ->  Criação da função eliminar_data;
 * 2.2   ->  Desenvolvimento da função eliminar_data;
 *
 * ========================================================   Descrição:   =============================================================================================
 * Funções Públicas:
 * enviar_query  ->  Junta o URL da TDB e a query e envia para a função privada obter_xml.
 * inserir_data  ->  Insere data na TDB usando uma query de inserção simples.
 * inserir_data_mais_argumentos -> Insere data na TDB usando uma query com vários argumentos.
 * eliminar_data ->  Elimina data da TDB usando uma query de eliminação simples.
 *
 * Funções Privadas:
 * obter_xml     ->  Faz um pedido ao servidor Fuseki para retornar a resposta em XML da query enviada.
 */

class PESTI_Model extends CI_Model {

    public function __construct() {
        //pode ficar vazio
    }

    //=================Funções Públicas ====================//

    public function enviar_query($url_db, $query) {
        $aux = 'query=' . $query;
        
        $post = curl_init();
        
        curl_setopt($post, CURLOPT_URL, $url_db);
        curl_setopt($post, CURLOPT_POSTFIELDS, $aux);
        curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
        
        $response = curl_exec($post);

        curl_close($post);
        
        return $response;
    }

    public function inserir_data($url_db, $sujeito, $predicado, $objecto) {
        $query = "update=INSERT DATA {" . $sujeito . $predicado . $objecto . "}";

        $result = $this->executar_query($url_db, $query);

        return $result;
    }

    public function inserir_data_mais_argumentos($url_db, $argumentos) {
        $query = "update=INSERT DATA {";

        foreach ($argumentos as &$argumento) {
            $query = $query . $argumento;
        }

        $query = $query . "}";

        $result = $this->executar_query($url_db, $query);

        return $result;
    }

    public function eliminar_data($url_db, $sujeito, $predicado, $objecto) {
        $query = "update=DELETE DATA {" . $sujeito . $predicado . $objecto . "}";

        $result = $this->executar_query($url_db, $query);

        return $result;
    }

    //=================Funções Privadas ====================//

    private function obter_xml($url) {
        $resposta_xml_data = file_get_contents($url);

        return $resposta_xml_data;
    }

    private function executar_query($url_db, $query) {
        $post = curl_init();

        curl_setopt($post, CURLOPT_URL, $url_db);
        curl_setopt($post, CURLOPT_POSTFIELDS, $query);
        curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($post);

        curl_close($post);

        $result = true;

        return $result;
    }

}

<?php

/*
 * PESTI Model
 * - Envia os pedidos do controller para o Servidor Fuseki e retorna as respostas.
 *
 * Versão 2.4
 *
 * @author Mário Teixeira
 * @author Marta Graça
 *
 * ========================================================   Changelog:   =============================================================================================
 * 1.0   ->  Criação e desenvolvimento das funções enviar_query e obter_xml;
 * 2.0   ->  Criação e desenvolvimento da função inserir_data;
 * 2.1   ->  Criação da função eliminar_data;
 * 2.2   ->  Desenvolvimento da função eliminar_data;
 * 2.3   ->  Desenvolvimento da função inserir_data_2;
 * 2.4   ->  Alteração da função consultar_data; Adicionado as funções privadas executar_consulta e executar_update;
 *
 * ========================================================   Descrição:   =============================================================================================
 * Funções Públicas:
 * consultar_data   ->  Consulta data da TDB usando a query recebida.
 * inserir_data     ->  Insere data na TDB usando uma query de inserção simples.
 * inserir_data_2   ->  Insere data na TDB usando uma query com vários argumentos.
 * eliminar_data    ->  Elimina data da TDB usando uma query de eliminação simples.
 * eliminar_data_2  ->  Elimina data na TDB usando uma query com vários argumentos.
 *
 * Funções Privadas:
 * executar_consulta  ->  Faz um pedido SPARQL ao servidor Fuseki para retornar a resposta da query enviada.
 * executar_update    ->  Faz um envio Update ao servidor Fuseki com a query recebida.
 */

class PESTI_Model extends CI_Model {

    public function __construct() {
        // Chamar o construtor do Model.
        parent::__construct();
    }

    //================= Funções Públicas ====================//

    public function consultar_data($url_db, $argumentos) {
        $query = 'query=' . $argumentos;

        $result = $this->executar_consulta($url_db, $query);

        return $result;
    }

    public function inserir_data($url_db, $sujeito, $predicado, $objecto) {
        $query = "update=INSERT DATA {" . $sujeito . $predicado . $objecto . "}";

        $result = $this->executar_update($url_db, $query);

        return $result;
    }

    public function inserir_data_2($url_db, $argumentos) {
        $query = "update=INSERT DATA {" . $argumentos . "}";

        $result = $this->executar_update($url_db, $query);

        return $result;
    }

    public function eliminar_data($url_db, $sujeito, $predicado, $objecto) {
        $query = "update=DELETE DATA {" . $sujeito . $predicado . $objecto . "}";

        $result = $this->executar_update($url_db, $query);

        return $result;
    }
     
    public function eliminar_data_2($url_db, $argumentos) {
        $query = "update=DELETE DATA {" . $argumentos . "}";

        $result = $this->executar_update($url_db, $query);

        return $result;
    }

    //================= Funções Privadas ====================//

    private function executar_consulta($url_db, $query) {
        $post = curl_init();

        curl_setopt($post, CURLOPT_URL, $url_db);
        curl_setopt($post, CURLOPT_POSTFIELDS, $query);
        curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($post);

        curl_close($post);

        return $response;
    }

    private function executar_update($url_db, $query) {
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

<?php

/*
 * PESTI Controller 
 * - Vai ser o centro de todos os pedidos da aplicação Web;
 *
 * Versão 3.8
 *
 * @author Mário Teixeira    1090626     1090626@isep.ipp.pt     
 * @author Marta Graça       1100640     1100640@isep.ipp.pt
 * 
 * ========================================================   Descrição:   =============================================================================================
 * Funções Públicas:
 * + view                     -> criação da view Página Principal.
 * 
 * Funções Privadas:
 * - readConfigFile           -> carrega o endereço do servidor Fuseki apartir de um ficheiro .ini.
 */

//Configurações do PHP
error_reporting(1);         // -> 0 - desactivo; 1 - activo.

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
        $title = 'Semantic Web Portal - ' . ucfirst($page);
        $data['title'] = $title;

        //carregar os views na ordem que devem ser exibidos
        $this->load->view('templates/header', $data);
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer', $data);
    }

    public function getFusekiAddress(){
        print_r($this->url_db_consult);
    }
    
    public function checkFusekiStatus() {
        /*
         * Query usada para o pedido GET
         * 
         * ASK WHERE { ?s ?p ?o }
         */

        $query = 'ASK WHERE { ?s ?p ?o }';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //xml recebe o resultado da query enviada para o Fuseki.
        $xml = $this->pesti_model->consultar_data($this->url_db_consult, $query);

        if (!$xml) {
            print_r(0);
        } else {
            print_r(1);
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
}
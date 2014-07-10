<?php

/*
 * User Controller
 * - Vai tratar dos pedidos da aplicação Web relacionados com utilizadores.
 * 
 * Versão 1.2
 * 
 * @author Mário Teixeira   1090626     1090626@isep.ipp.pt
 * @author Marta Graça      1100640     1100640@isep.ipp.pt
 * 
 * =================================== Descrição ========================================
 * Variáveis Globais:
 * url_user_db_sparql   ->  URL do servidor Fuseki para consultas.
 * url_user_db_update   ->  URL do servidor Fuseki para inserções/eliminações.
 * 
 * Funções Públicas:
 * __construct          ->  Construtor.
 * viewLogin            ->  Criação da view Login.
 * viewRegister         ->  Criação da view Registro.
 * checkUserExists      ->  Verifica se o utilizador existe na base de dados.
 * checkUserPassword    ->  Verifica se a password indicada pelo utilizador esta correta.
 * getUserAccessLevel   ->  Retorna o nível de utilizador.
 * listUsers            ->  Lista todos os utlizadores existentes na base de dados.
 * insertNewUser        ->  Insere um novo utilizador na base de dados.
 * deleteUser           ->  Elimina um utilizador da base de dados.
 * 
 * Funções Privadas:
 * readConfigFile       ->  Carrega o endereço do servidor Fuseki apartir de um ficheiro .ini.
 * sendQuery            ->  Envio da query para o Fuseki (esse processo é tratado pelo modelo).
 * sendInsert           ->  Envio da query de inserção para o Fuseki (esse processo é tratado pelo modelo).
 * sendDelete           ->  Envio da query de eliminação para o Fuseki (esse processo é tratado pelo modelo).
 * useXSLT              ->  Carrega o xsl indicado e processa à transformação do xml indicado.
 * getURI               ->  Retorna a URI da ontologia.
 */

class User_Controller extends CI_Controller {

    //================= Variáveis Globais ===================
    protected $url_user_db_sparql = 'null';
    protected $url_user_db_update = 'null';

    //================= Funções Públicas ====================
    public function __construct() {
        parent::__construct();
        $this->load->model('pesti_model');
        $this->readConfigFile();
    }

    public function viewLogin($page = 'login') {
        if (!file_exists('application/views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = ucfirst($page);

        $this->load->view('pages/' . $page, $data);
    }

    public function viewRegister($page = 'registro') {
        if (!file_exists('application/views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = ucfirst($page);

        $this->load->view('pages/' . $page, $data);
    }

    public function checkUserExists($user) {
        //Apenas para testes.
        if ($user == 'Admin' || $user == 'testUser') {
            print_r("1");
        } else {
            print_r("0");
        }
    }

    public function checkUserPassword($user, $password) {
        //Apenas para testes.
        if ($user == 'Admin' && $password == 'Admin') {
            print_r("1");
        } else if ($user == 'testUser' && $password == 'testUser') {
            print_r("1");
        } else {
            print_r("0");
        }
    }

    public function getUserAccessLevel($user) {
        //Ainda não desenvolvido.
    }

    public function listUsers() {
        //Ainda não desenvolvido.
    }

    public function insertNewUser($user, $password) {
        //Ainda não desenvolvido.
    }

    public function deleteUser($user) {
        //Ainda não desenvolvido.
    }

    //================= Funções Privadas ====================
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
                if ($aux[0] == 'url_fuseki_users ' || $aux[0] == 'dataset_users ') {
                    $url_fuseki = $url_fuseki . $aux[1];
                    //Remover possíveis espaços
                    $url_fuseki = preg_replace('/\s+/', '', $url_fuseki);
                }
            }

            $this->url_user_db_sparql = $url_fuseki . "/sparql";
            $this->url_user_db_update = $url_fuseki . "/update";
        }
    }

    private function sendQuery($query) {
        //XML recebe o resultado da query enviada para o Fuseki.
        $xml = $this->pesti_model->consultar_data($this->url_user_db_sparql, $query);
        return $xml;
    }

    private function sendInsert($subject, $predicate, $object) {
        //Variável result recebe 1 se a inserção for com sucesso.
        $result = $this->pesti_model->inserir_data($this->url_user_db_update, $subject, $predicate, $object);
        return $result;
    }

    private function sendDelete($subject, $predicate, $object) {
        //Variável result recebe 1 se a eliminação for com sucesso.
        $result = $this->pesti_model->eliminar_data($this->url_user_db_update, $subject, $predicate, $object);
        return $result;
    }

    private function useXSLT($xmlfile, $xslfile) {
        $xsl = new XSLTProcessor;

        $xsl->importStylesheet(DOMDocument::load($xslfile));

        $result = $xsl->transformToXml(simplexml_load_string($xmlfile));

        return $result;
    }

    private function getURI($element) {
        /*
         * Query para obter a URI da ontologia de utilizadores.
         * 
         * PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
         * PREFIX owl: <http://www.w3.org/2002/07/owl#>
         * 
         * SELECT DISTINCT (strbefore(str(?class), "#") AS ?URI)
         * WHERE { ?class rdfns:type owl:Class. }
         */

        $query = 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX owl: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'SELECT DISTINCT (strbefore(str(?class), "#") AS ?URI) ';
        $query = $query . 'WHERE { ?class rdfns:type owl:Class. }';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //result recebe o resultado da query enviada para o Fuseki.
        $xml = $this->sendQuery($query);

        //Processamento directo do XML para retirar a URI.
        $aux_1 = explode("<literal>", $xml);
        $aux_2 = $aux_1[1];
        $uri = explode("</literal>", $aux_2);
        $fullURI = '<' . $uri[0] . '#' . $element . '>';

        return $fullURI;
    }

}

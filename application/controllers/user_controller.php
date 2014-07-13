<?php

/*
 * User Controller
 * - Vai tratar dos pedidos da aplicação Web relacionados com utilizadores.
 * 
 * Versão 1.4
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
 * getFusekiAddress     ->  Retorna o url do Servidor Fuseki.
 * checkFusekiStatus    ->  Verifica se o servidor Fuseki esta vivo fazendo um pedido GET.
 * 
 * Funções Privadas:
 * readConfigFile       ->  Carrega o endereço do servidor Fuseki apartir de um ficheiro .ini.
 * sendQuery            ->  Envio da query para o Fuseki (esse processo é tratado pelo modelo).
 * sendInsert           ->  Envio da query de inserção para o Fuseki (esse processo é tratado pelo modelo).
 * sendDelete           ->  Envio da query de eliminação para o Fuseki (esse processo é tratado pelo modelo).
 * useXSLT              ->  Carrega o xsl indicado e processa à transformação do xml indicado.
 * getUserPassword      ->  Retorna a password do utilizador indicado.
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

    public function viewAdmin($page = 'admin') {
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

    public function checkUserExists($user, $chamada) {
        /*
         * Query para verificar se o utilizador existe na ontologia.
         * 
         * PREFIX : <http://www.semanticweb.org/ontologies/2014/6/OntologiaDeUtilizadores.owl#>
         * 
         * ASK WHERE { :Admin a :Utilizador }
         */

        $userURI = $this->getURI($user);
        $userType = $this->getUri('Utilizador');

        $query = 'ASK WHERE { ' . $userURI . ' a ' . $userType . ' }';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //xml recebe o resultado da query enviada para o Fuseki.
        $xml = $this->sendQuery($query);

        //Processamento directo do XML para retirar a resposta.
        $aux_1 = explode("<boolean>", $xml);
        $aux_2 = $aux_1[1];
        $exists = explode("</boolean>", $aux_2);

        if ($exists[0] == 'true') {
            $result = 1;
        } else {
            $result = 0;
        }

        if ($chamada == 1) {
            return $result;
        } else {
            print_r($result);
        }
    }

    public function checkUserPassword($user, $password) {
        $validator = "0";

        $userExists = $this->checkUserExists($user, 1);
        $userPassword = $this->getUserPassword($user);

        if ($userExists == 1) {
            if ($password == $userPassword) {
                $validator = "1";
            }
        }

        print_r($validator);
    }

    public function getUserAccessLevel($user) {
        /*
         * Query para obter o nível de um utilizador.
         * 
         * PREFIX : <http://www.semanticweb.org/ontologies/2014/6/OntologiaDeUtilizadores.owl#>
         * PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
         * PREFIX owl: <http://www.w3.org/2002/07/owl#>
         * 
         * select (str(?nivel) AS ?Nivel)
         * where{ ?membro rdfns:type owl:NamedIndividual. ?membro :temNivel ?nivel. }
         */

        $userURI = $this->getURI($user);
        $nivelURI = $this->getURI('temNivel');

        $query = 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX owl: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'select (str(?nivel) AS ?Nivel) ';
        $query = $query . 'where{ ' . $userURI . ' rdfns:type owl:NamedIndividual. ' . $userURI . ' ' . $nivelURI . ' ?nivel. }';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //xml recebe o resultado da query enviada para o Fuseki.
        $xml = $this->sendQuery($query);

        //Processamento directo do XML para retirar a URI.
        $aux_1 = explode("<literal>", $xml);
        $aux_2 = $aux_1[1];
        $level = explode("</literal>", $aux_2);

        print_r($level[0]);
    }

    public function listUsers() {
        /*
         * Query para obter todos os utilizadores registados.
         *
         * PREFIX : <http://www.semanticweb.org/ontologies/2014/6/OntologiaDeUtilizadores.owl#>
         * PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
         * PREFIX owl: <http://www.w3.org/2002/07/owl#>
         * 
         * select (strafter(str(?membro), "#") AS ?Utilizador)
         * where{ ?membro rdfns:type owl:NamedIndividual. ?membro rdfns:type :Utilizador. }
         */

        $userURI = $this->getURI('Utilizador');

        $query = 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX owl: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'select (strafter(str(?membro), "#") AS ?Utilizador) ';
        $query = $query . 'where{ ?membro rdfns:type owl:NamedIndividual. ?membro rdfns:type ' . $userURI . '. }';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //xml recebe o resultado da query enviada para o Fuseki.
        $xml = $this->sendQuery($query);

        //Ficheiro XSL a ser usado para a transformação do XML
        $xslfile = "http://localhost/assets/xsl/tabela_users.xsl";

        //Enviar o resultado XML e o ficheiro XSL para o processador XSLT
        $result = $this->useXSLT($xml, $xslfile);

        print_r($result);
    }

    public function insertNewUser($user, $password) {
        /*
         * Query para inserção de um novo utilizador
         * 
         * INSERT DATA{
         * <URI da ontologia#username> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://www.w3.org/2002/07/owl#NamedIndividual>.
         * <URI da ontologia#username> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://www.semanticweb.org/ontologies/2014/6/OntologiaDeUtilizadores.owl#Utilizador>.
         * <URI da ontologia#username> <http://www.semanticweb.org/ontologies/2014/6/OntologiaDeUtilizadores.owl#temPassword> "INSERIR VALOR AQUI".
         * <URI da ontologia#username> <http://www.semanticweb.org/ontologies/2014/6/OntologiaDeUtilizadores.owl#temNivel> "INSERIR VALOR AQUI".}
         */

        //Definição do nível do utilizador (1 para utilizadores normais).
        $accessLevel = 1;
        //Definição das URIs necessárias.
        $userURI = $this->getURI($user);
        $userType = $this->getUri('Utilizador');
        $passwordURI = $this->getURI('temPassword');
        $levelURI = $this->getURI('temNivel');

        $arguments = $userURI . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://www.w3.org/2002/07/owl#NamedIndividual>. ';
        $arguments = $arguments . $userURI . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ' . $userType . '. ';
        $arguments = $arguments . $userURI . $passwordURI . '"' . $password . '".';
        $arguments = $arguments . $userURI . $levelURI . '"' . $accessLevel . '".';

        $result = $this->sendInsert($arguments);

        return $result;
    }

    public function deleteUser($user) {
        /*
         * Query para inserção de um novo utilizador
         * 
         * DELETE DATA{
         * <URI da ontologia#username> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://www.w3.org/2002/07/owl#NamedIndividual>.
         * <URI da ontologia#username> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://www.semanticweb.org/ontologies/2014/6/OntologiaDeUtilizadores.owl#Utilizador>.
         * <URI da ontologia#username> <http://www.semanticweb.org/ontologies/2014/6/OntologiaDeUtilizadores.owl#temPassword> "INSERIR VALOR AQUI".
         * <URI da ontologia#username> <http://www.semanticweb.org/ontologies/2014/6/OntologiaDeUtilizadores.owl#temNivel> "INSERIR VALOR AQUI".}
         */

        $accessLevel = $this->getUserAccessLevel($user);
        $password = $this->getUserPassword($user);

        //Definição das URIs necessárias.
        $userURI = $this->getURI($user);
        $userType = $this->getUri('Utilizador');
        $passwordURI = $this->getURI('temPassword');
        $levelURI = $this->getURI('temNivel');

        $arguments = $userURI . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://www.w3.org/2002/07/owl#NamedIndividual>. ';
        $arguments = $arguments . $userURI . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ' . $userType . '. ';
        $arguments = $arguments . $userURI . $passwordURI . '"' . $password . '".';
        $arguments = $arguments . $userURI . $levelURI . '"' . $accessLevel . '".';

        $result = $this->sendDelete($arguments);

        return $result;
    }

    public function getFusekiAddress() {
        print_r($this->url_user_db_sparql);
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
        $xml = $this->sendQuery($query);

        if (!$xml) {
            print_r(0);
        } else {
            print_r(1);
        }
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

    private function sendInsert($arguments) {
        //Variável result recebe 1 se a inserção for com sucesso.
        $result = $this->pesti_model->inserir_data_2($this->url_user_db_update, $arguments);
        return $result;
    }

    private function sendDelete($arguments) {
        //Variável result recebe 1 se a eliminação for com sucesso.
        $result = $this->pesti_model->eliminar_data_2($this->url_user_db_update, $arguments);
        return $result;
    }

    private function useXSLT($xmlfile, $xslfile) {
        $xsl = new XSLTProcessor;

        $xsl->importStylesheet(DOMDocument::load($xslfile));

        $result = $xsl->transformToXml(simplexml_load_string($xmlfile));

        return $result;
    }

    private function getUserPassword($user) {
        /*
         * Query para obter a password do utilizador indicado.
         * 
         * PREFIX : <http://www.semanticweb.org/ontologies/2014/6/OntologiaDeUtilizadores.owl#>
         * PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
         * PREFIX owl: <http://www.w3.org/2002/07/owl#>
         * 
         * select (str(?pass) AS ?Password)
         * where{ ?membro rdfns:type owl:NamedIndividual. ?membro :temPassword ?pass. }
         */

        $userURI = $this->getURI($user);
        $passwordURI = $this->getURI('temPassword');

        $query = 'PREFIX rdfns: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ';
        $query = $query . 'PREFIX owl: <http://www.w3.org/2002/07/owl#> ';
        $query = $query . 'select (str(?pass) AS ?Password) ';
        $query = $query . 'where{ ' . $userURI . ' rdfns:type owl:NamedIndividual. ' . $userURI . $passwordURI . ' ?pass. }';
        $query = $query . '&output=xml&stylesheet=xml-to-html.xsl';

        //xml recebe o resultado da query enviada para o Fuseki.
        $xml = $this->sendQuery($query);

        //Processamento directo do XML para retirar a URI.
        $aux_1 = explode("<literal>", $xml);
        $aux_2 = $aux_1[1];
        $password = explode("</literal>", $aux_2);

        return $password[0];
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

        //xml recebe o resultado da query enviada para o Fuseki.
        $xml = $this->sendQuery($query);

        //Processamento directo do XML para retirar a URI.
        $aux_1 = explode("<literal>", $xml);
        $aux_2 = $aux_1[1];
        $uri = explode("</literal>", $aux_2);
        $fullURI = '<' . $uri[0] . '#' . $element . '>';

        return $fullURI;
    }

}

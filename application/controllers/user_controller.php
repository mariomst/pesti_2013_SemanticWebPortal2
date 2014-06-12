<?php

/* 
 * User Controller
 * - Vai tratar dos pedidos da aplicação Web relacionados com utilizadores.
 * 
 * Versão 1.1
 * 
 * @author Mário Teixeira   1090626     1090626@isep.ipp.pt
 * @author Marta Graça      1100640     1100640@isep.ipp.pt
 * 
 * =================================== Changelog ========================================
 * 1.0 -> Declaração de alguns dos métodos para o tratamento de utilizadores.
 * 1.1 -> Adição das Views "Login" e "Registro"
 * 
 * =================================== Descrição ========================================
 * Variáveis Globais:
 * url_user_db_sparql   ->  URL do servidor Fuseki para consultas.
 * url_user_db_update   ->  URL do servidor Fuseki para inserções/eliminações.
 * 
 * Funções Públicas:
 * viewLogin           ->  criação da view Login.
 * viewRegister        ->  criação da view Registro.
 * checkUserExists      ->  Verifica se o utilizador existe na base de dados.
 * checkUserPassword    ->  Verifica se a password indicada pelo utilizador esta correta.
 * listUsers            ->  Lista todos os utlizadores existentes na base de dados.
 * insertNewUser        ->  Insere um novo utilizador na base de dados.
 * deleteUser           ->  Elimina um utilizador da base de dados.
 * 
 * Funções Privadas:
 * getURI               ->  Retorna a URI da ontologia.
 */

class User_Controller extends CI_Controller
{
    //================= Variáveis Globais ===================
    protected $url_user_db_sparql = 'null';
    protected $url_user_db_update = 'null';
    
    //================= Funções Públicas ====================
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pesti_model');
    }
    
    public function viewLogin($page = 'login')
    {
        if(!file_exists('application/views/pages/'.$page.'.php'))
        {
            show_404();
        }

        $data['title'] = ucfirst($page);

        $this->load->view('pages/'.$page,$data);
    }

    public function viewRegister($page = 'registro')
    {
        if(!file_exists('application/views/pages/'.$page.'.php'))
        {
            show_404();
        }

        $data['title'] = ucfirst($page);

        $this->load->view('pages/'.$page,$data);
    }
    
    public function checkUserExists($user)
    {
        //Ainda não desenvolvido.        
    }
    
    public function checkUserPassword($user, $password)
    {
        //Ainda não desenvolvido.
    }
    
    public function listUsers()
    {
        //Ainda não desenvolvido.
    }
    
    public function insertNewUser($user, $password)
    {
        //Ainda não desenvolvido.
    }
    
    public function deleteUser($user)
    {
        //Ainda não desenvolvido.
    }
    
    //================= Funções Privadas ====================
    private function getURI()
    {
        //Ainda não desenvolvido.
    }
}




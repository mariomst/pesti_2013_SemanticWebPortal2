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
 */

class User_Controller extends CI_Controller {

    //Funções públicas
    public function __construct() {
        parent::__construct();
        $this->load->model('user_model');
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
        $result = $this->user_model->checkUserExists($user);
        print_r($result);
    }

    public function checkUserPassword($user, $password) {
        $result = $this->user_model->checkUserPassword($user, $password);
        print_r($result);
    }

    public function getUserAccessLevel($user) {
        $result = $this->user_model->getUserAccessLevel($user);
        print_r($result);
    }

    public function listUsers() {
        $result = $this->user_model->listUsers();
        print_r($result);
    }

    public function insertNewUser($user, $password) {
        $result = $this->user_model->insertNewUser($user, $password);
        return $result;
    }

    public function deleteUser($user) {
        $result = $this->user_model->deleteUser($user);
        return $result;
    }

    public function getFusekiAddress() {
        $result = $this->user_model->getFusekiAddress();
        print_r($result);
    }

    public function checkFusekiStatus() {
        $result = $this->user_model->checkFusekiStatus();
        print_r($result);
    }
}

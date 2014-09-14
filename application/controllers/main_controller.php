<?php

/*
 * Main Controller 
 *
 * Versão 3.9
 *
 * @author Mário Teixeira    1090626     1090626@isep.ipp.pt     
 * @author Marta Graça       1100640     1100640@isep.ipp.pt
 *
 */

//Configurações do PHP
error_reporting(1);         // -> 0 - desactivo; 1 - activo.

class Main_Controller extends CI_Controller {

    //Funções Públicas
    public function __construct() {
        parent::__construct();
        $this->load->model('main_model');
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

    public function getFusekiAddress() {
        $result = $this->main_model->getFusekiAddress();
        print_r($result);
    }

    public function checkFusekiStatus() {
        $result = $this->main_model->checkFusekiStatus();
        print_r($result);
    }

}

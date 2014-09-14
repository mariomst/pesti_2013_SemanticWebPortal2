<?php

/*
 * Read Controller
 * - Controller responsável em fazer os pedidos ao Modelo de Consulta
 * 
 * Versão 1.1
 * 
 * @author Mário Teixeira   1090626     1090626@isep.ipp.pt
 * @author Marta Graça      1100640     1100640@isep.ipp.pt
 */

//Configurações do PHP
error_reporting(1);         // -> 0 - desactivo; 1 - activo.

class Consult_Controller extends CI_Controller {
    
    //Funções Públicas
    public function __construct() {
        parent::__construct();
        $this->load->model('consult_model');
    }   

    public function listClasses($chamada) {
        $result = $this->consult_model->listClasses($chamada);
        print_r($result);
    }

    public function listSubClasses($classeMae, $chamada) {
        $result = $this->consult_model->listSubClasses($classeMae, $chamada);
        print_r($result);
    }

    public function selectSubClasses($classeMae) {
        $result = $this->consult_model->selectSubClasses($classeMae);
        print_r($result);
    }

    public function getSubClasses($classeMae, $chamada) {
        $result = $this->consult_model->getSubClasses($classeMae, $chamada);
        print_r($result);
    }

    public function getMembers($classeMae, $chamada) {
        $result = $this->consult_model->getMembers($classeMae, $chamada);
        print_r($result);
    }

    public function getProperties() {
        $result = $this->consult_model->getProperties();
        print_r($result);
    }

    public function getPropertyRange($property, $chamada) {
        $result = $this->consult_model->getPropertyRange($property, $chamada);
        print_r($result);        
    }

    public function getPropertyInfo($property) {
        $result = $this->consult_model->getPropertyInfo($property);
        print_r($result);
    }

    public function getClassProperty($classe, $chamada) {
        $result = $this->consult_model->getClassProperty($classe, $chamada);
        print_r($result);
    }

    public function getMemberProperty($membro, $chamada) {
        $result = $this->consult_model->getMemberProperty($membro, $chamada);
        print_r($result);
    }

    public function getComment($subject) {
        $result = $this->consult_model->getComment($subject);
        print_r($result);
    }

    public function printURI() {
        $result = $this->consult_model->getURI();
        print_r($result);
    }
}

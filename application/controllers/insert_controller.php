<?php

/*
 * Read Controller
 * - Controller responsável pelas inserções feitas na ontologia.
 * 
 * Versão 1.1
 * 
 * @author Mário Teixeira   1090626     1090626@isep.ipp.pt
 * @author Marta Graça      1100640     1100640@isep.ipp.pt
 * 
 */

//Configurações do PHP
error_reporting(1);         // -> 0 - desactivo; 1 - activo.

class Insert_Controller extends CI_Controller {
    
    //Funções Públicas    
    public function __construct() {
        parent::__construct();
        $this->load->model('insert_model');
    }    
    
    public function viewInsertClass($page = 'inserir') {
        if (!file_exists('application/views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = ucfirst($page);

        $this->load->view('pages/' . $page, $data);
    }
    
    public function insertData($type, $subject, $object) {
        //Conforme o tipo de inserção faz um pedido diferente ao modelo.       
        if ($type == 'membro') {
            //Adição de membros.
            $result = $this->insert_model->insertMember($subject, $object);
            print_r($result);
            exit;
        } else if ($type == 'subclasse') {
            //Adição de subclasses
            $result = $this->insert_model->insertClass($subject, $object);
            print_r($result);
            exit;
        } else if ($type == 'comentario') {
            //Adição de um comentário.
            $result = $this->insert_model->insertCommentary($subject, $object);
            print_r($result);
            exit;
        } else {
            print_r("Erro: Tipo n&atilde;o reconhecido...");
            exit;
        }
    }
    
    public function insertProperty($type, $subject, $predicate, $object, $range, $rangeType) {
        //Conforme o tipo de propriedade faz um pedido diferente ao modelo.  
        if ($type == 'fixo') {
            //Ainda não desenvolvido.
            print_r("Erro: Ainda n&atilde;o desenvolvido...");
            exit;
        } else if ($type == 'naoFixo') {
            //Inserção de uma propriedade não fixa.
            $result = $this->insert_model->insertNonFixedProperty($subject, $object, $range, $rangeType);
            print_r($result);
            exit;
        } else if ($type == 'membro') {
            //Inserção de uma propriedade a um membro.
            $result = $this->insert_model->insertMemberProperty($subject, $predicate, $object, $rangeType);
            print_r($result);
            exit;
        } else if ($type == 'novo1') {
            //Inserção de uma nova propriedade (Parte 1).
            $result = $this->insert_model->insertNewPropertyStep1($subject, $object);
            print_r($result);
            exit;
        } else if ($type == 'novo2') {
            //Inserção de uma nova propriedade (Parte 2).
            $result = $this->insert_model->insertNewPropertyStep2($subject, $predicate, $object);
            print_r($result);
            exit;
        } else if ($type == 'visibilidade') {
            //Inserção da propriedade temVisibilidade.
            $result = $this->insert_model->insertVisibilityProperty();
            print_r($result);
            exit;
        } else if ($type == 'visibilidadeValor') {
            //Inserção do valor da propriedade temVisibilidade.
            $result = $this->insert_model->insertVisibilityPropertyValue($subject, $object);
            print_r($result);
            exit;
        } else {
            print_r("Erro: Tipo de propriedade n&atilde;o reconhecido...");
            exit;
        }
    }
}
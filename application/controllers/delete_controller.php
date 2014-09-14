<?php

/*
 * Delete Controller
 * - Controller responsável pelas eliminações feitas na ontologia.
 * 
 * Versão 1.1
 * 
 * @author Mário Teixeira   1090626     1090626@isep.ipp.pt
 * @author Marta Graça      1100640     1100640@isep.ipp.pt
 * 
 */

//Configurações do PHP
error_reporting(1);         // -> 0 - desactivo; 1 - activo.

class Delete_Controller extends CI_Controller {
    
    //Funções Públicas
    public function __construct() {
        parent::__construct();
        $this->load->model('delete_model');
    }
    
    public function deleteData($type, $subject, $object) {
        //Conforme o tipo de inserção faz um pedido diferente ao modelo.  
        if ($type == 'classe') {
            //Eliminação de uma classe.
            $result = $this->delete_model->deleteClass($subject, $object);
            print_r($result);
            exit;
        } else if ($type == 'membro') {
            //Eliminação de um membro.
            $result = $this->delete_model->deleteMember($subject, $object);
            print_r($result);
            exit;
        } else if ($type == 'comentario') {
            //Eliminação de um comentário
            $result = $this->delete_model->deleteCommentary($subject, $object);
            print_r($result);
            exit;
        } else {
            print_r("Erro: Tipo n&atilde;o reconhecido...");
            exit;
        }
    }
    
    public function deleteProperty($type, $subject, $predicate, $object, $range, $rangeType) {
        //Conforme o tipo de propriedade faz um pedido diferente ao modelo. 
        if ($type == 'fixo') {
            //Ainda não desenvolvido.
            print_r("Erro: Ainda n&atilde;o desenvolvido...");
            exit;
        } else if ($type == 'naoFixo') {
            //Ainda não desenvolvido.
            print_r("Erro: Ainda n&atilde;o desenvolvido...");
            exit;
        } else if ($type == 'membro') {
            //Eliminação de uma propriedade num membro.
            $result = $this->delete_model->deletePropertyMember($subject, $predicate, $object, $rangeType);
            print_r($result);
            exit;
        } else if ($type == 'visibilidade') {
            //Eliminação do valor da propriedade temVisibilidade.
            $result = $this->delete_model->deleteVisibilityPropertyValue($subject, $object);
            print_r($result);
            exit;
        } else {
            print_r("Erro: Tipo n&atilde;o reconhecido...");
            exit;
        }
    }
}

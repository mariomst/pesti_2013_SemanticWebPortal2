<?php

class Test_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('unit_test');
        $this->load->helper('portal_helper');
        $this->load->model('consult_model');
        $this->load->model('insert_model');
        $this->load->model('delete_model');
        $this->load->model('user_model');
    }

    public function tests() {
        $str = '
            <table border="1" cellpadding="4" cellspacing="1">                    
                {rows}
                    <tr>
                        <td>{item}</td>
                        <td>{result}</td>
                    </tr>
                {/rows}
            </table><br>';

        $this->unit->set_template($str);

        $this->unit->run(test_helper('teste1'), 'O helper recebeu o argumento teste1', 'Chamda de uma função do helper.', 'Apenas testa o acesso as funções do helper.');

        $this->unit->run(readConfigFile(), 'localhost:3030/data', 'Leitura de ficheiro de configuração.', 'Sucesso:<br>- Ficheiro é encontrado e lido. <br> '
                . 'Insucesso:<br>- Ficheiro não é encontrado '
                . '<br>- Erro no processamento.');

        $this->unit->run(readQueryFile('queryTeste1'), 'is_string', 'Leitura de ficheiro query.', 'Ficheiro usado queryTeste1.query.');

        $this->unit->run($this->consult_model->listClasses(1), 'is_string', 'Comunicação entre modelo e servidor Fuseki. (Consulta)', 'Listagem de Classes presentes na ontologia.');

        $this->unit->run($this->insert_model->insertMember('unitTest', 'Specs'), 'is_bool', 'Comunicação entre modelo e servidor Fuseki. (Inserção)', 'Inserção do indivíduo unitTest');

        $this->unit->run($this->delete_model->deleteMember('unitTest', 'Specs'), 'is_bool', 'Comunicação entre modelo e servidor Fuseki. (Eliminação)', 'Eliminação do indivíduo unitTest');

        $this->unit->run($this->user_model->insertNewUser('userTest', 'test1234'), 'is_bool', 'Comunicação entre modelo e servidor Fuseki. (Inserção)', 'Inserção do utilizador teste.');

        $this->unit->run($this->user_model->deleteUser('userTest'), 'is_bool', 'Comunicação entre modelo e servidor Fuseki. (Eliminação)', 'Eliminação do utilizador teste.');

        $title = 'Semantic Web Portal - Test Page';
        $data['title'] = $title;

        $this->load->view('pages/tests', $data);
    }

}

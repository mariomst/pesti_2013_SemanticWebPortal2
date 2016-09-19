# PESTI 2013/2014 - Semantic Web Portal 2 #

### Introdução ###

A principal ideia subjacente a esta aplicação é melhorar a interpretação do utilizador referente a qualquer informação abarcada numa ontologia através da representação de conhecimento estruturado semanticamente. Isto é útil pois estas tecnologias são aplicadas na web de formas tão subtis ao utilizador comum que se torna difícil percecionar como tudo se interliga e funciona. Este mecanismo permite estender a rede de páginas web inserindo metadados nas suas estruturas, permitindo automatizar agentes informáticos a aceder à internet de forma mais inteligente e realizar tarefas autonomamente. Permite também expor através de páginas web a informação contida em qualquer repositório cuja informação seja descrita por ontologias. Solicita ao repositório os dados pretendidos para cada operação (Create, Read, Update, Delete (CRUD)), filtrando e transformando posteriormente os resultados. A plataforma organiza os conteúdos e expõe-nos de forma estruturada, facilitando assim a execução de operações de leitura, inserção, atualização e remoção destes. É utilizado um sistema de autenticação e autorização que permite apenas a utilizadores registados manipularem a informação contida no repositório (operações CRUD), enquanto que utilizadores não registados apenas têm permissão para visualizar informação. Recorre-se à existência de um administrador para realizar a manutenção do sistema e dos utilizadores registados neste.

Das várias funcionalidades destacam-se:

* Melhorias no processo de geração da interface de operações CRUD (Create, Read, Update,
Delete);
* Disponibilização de autenticação e autorização de acesso a páginas de dados;
* Disponibilização do conteúdo do portal através de REST API (Representational State Transfer
Application Programming Interface)

Projeto desenvolvido em contexto de PESTI (Projeto/Estágio).

### Versão ###

* 6.0.2

### Requisitos ###

* Editor para PHP (ex: [NetBeans IDE](https://netbeans.org/); [PhpStorm](https://www.jetbrains.com/phpstorm/); etc.)
* [Servidor Fuseki](https://jena.apache.org/documentation/serving_data/)
* PHP 5.3
* [XAMPP](https://www.apachefriends.org/index.html)
* [WAMP](http://www.wampserver.com/en/)

### Configuração ###
*  O projecto pode ser posto directamente na pasta htdocs se estiver a utilizar um servidor XAMPP  ou na pasta www se estiver a utilizar um servidor WAMP.
*  Como é utilizado o [Framework CodeIgniter](http://ellislab.com/codeigniter), caso seja necessário a alteração dos caminhos para as páginas PHP, pode ser facilmente ajustado no ficheiro "routes.php" localizado em "/application/config/". Exemplo:

```
#!php

//Páginas De Visualização
$route['default_controller'] = 'pesti_controller/view';
$route['login'] = 'user_controller/viewLogin';
$route['register'] = 'user_controller/viewRegister';

```
    
*  Para executar o Website, é necessáriro e indicar o endereço do [Servidor Fuseki](https://jena.apache.org/documentation/serving_data/) no ficheiro "connections.ini" localizado em "/configs/".

### Autores ###
* Mário Teixeira [1090626@isep.ipp.pt](mailto:1090626@isep.ipp.pt)
* Marta Graça [1100640@isep.ipp.pt](mailto:1100640@isep.ipp.pt)

### Orientadores ###
* Nuno Silva [nps@isep.ipp.pt](mailto:nps@isep.ipp.pt)
* Paulo Maio [pam@isep.ipp.pt](mailto:nps@isep.ipp.pt)
# PESTI 2013/2014 - Semantic Web Portal 2 #

### Introdução ###

* Código-fonte do projecto "Desenvolvimento de infraestrutura para portal baseado em tecnologias de Web Semântica"
+ Versão atual 6.0.2

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
* Nuno Silva
* Paulo Maio
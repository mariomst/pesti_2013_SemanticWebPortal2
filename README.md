# README #

### Para que serve este repositório? ###

* Código-fonte do projecto "Desenvolvimento de infraestrutura para portal baseado em tecnologias de Web Semântica"
+ Versão atual 5.9.0

### Como faço para configurar? ###

+ **Configuração**:
    *  O projecto pode ser aberto utilizando a aplicação [NetBeans IDE](https://netbeans.org/).
    *  O projecto pode ser posto directamente na pasta htdocs se estiver a utilizar um [Servidor XAMPP](https://www.apachefriends.org/index.html) ou na pasta www se estiver a utilizar um [Servidor WAMP](http://www.wampserver.com/en/).
    *  Como é utilizado o [Framework CodeIgniter](http://ellislab.com/codeigniter), caso seja necessário a alteração dos caminhos para as páginas PHP, pode ser facilmente ajustado no ficheiro "routes.php" localizado em "/application/config/". Exemplo:

```
#!php

//Páginas De Visualização
$route['default_controller'] = 'pesti_controller/view';
$route['login'] = 'user_controller/viewLogin';
$route['register'] = 'user_controller/viewRegister';

```


+ **Dependências**:
    *  Para executar o Website, é necessário ter um [Servidor Fuseki](https://jena.apache.org/documentation/serving_data/) e indicar o endereço (ex: http://localhost:3030/data) no ficheiro "connections.ini" localizado em "/configs/".
    *  É necessário também a utilização de um [Servidor XAMPP](https://www.apachefriends.org/index.html) ou um [Servidor WAMP](http://www.wampserver.com/en/).

### Quem devo falar em caso de dúvidas? ###

* Mário Teixeira [1090626@isep.ipp.pt](mailto:1090626@isep.ipp.pt)
* Marta Graça [1100640@isep.ipp.pt](mailto:1100640@isep.ipp.pt)
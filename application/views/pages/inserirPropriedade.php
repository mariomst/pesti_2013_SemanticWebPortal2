<!-- Página de inserção de uma propriedade já existente 
     Versão 1.0     
-->

<?php
header('Content-Type: text/html; charset=utf-8');
?>

<html>
    <head>
        <title><?php echo $title ?></title>
        <!-- Carregamento dos ficheiros JavaScript -->
        <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
        <script type="text/javascript" src="/assets/js/JSfunctions.js"></script>
        <!-- Carregamento dos ficheiros CSS -->
        <link rel="stylesheet" href="/assets/css/stylesheet.css" type="text/css" media="screen"/>
    </head>
    <body>
        <h2 align="center">
            &#8226; Inser&ccedil;&atilde;o de uma propriedade no(a)
            <?php echo htmlspecialchars($_GET["type"]); ?>:
            <?php echo htmlspecialchars($_GET["element"]); ?> 
        </h2>

        <div align="center">
            <form align="center" class="forms">                
                <b>Escolha a(s) propriedade(s) que deseja adicionar: </b>
                <br>
                <br>
                <!-- Construção da tabela com as propriedades existentes na ontologia. -->
                <?php
                //Chamada da função getProperties existente no controller, para obter todas as propriedades.
                $properties_link = "http://$_SERVER[HTTP_HOST]/index.php/getProperties";
                $properties = file_get_contents($properties_link);

                //Construção da tabela.
                echo "<table id=\"propriedades\" align=\"center\" border=0>";
                echo $properties;
                echo "</table>";
                ?>
                <!-- Fim da tabela. -->
                <br>
                <button id="goBack">&#8592; Voltar</button>
                <button id="insertProps">Inserir</button>                
            </form>            
        </div>

        <!-- Código JavaScript para esta página -->
        <script type="text/javascript">
            //Permitir ao JavaScript utilizar os argumentos PHP recebidos.
            $_GET = <?php echo json_encode($_GET); ?>;

            //Atribuir as variáveis PHP às variáveis JS.
            elementType = $_GET["type"];
            elementName = $_GET["element"];

            //Atribuir as variáveis, os objectos da página.
            backButton = document.getElementById("goBack");
            insertButton = document.getElementById("insertProps");

            $(document).ready(function()
            {
                var all_tr_in_a_table = $("#propriedades tr");

                $(all_tr_in_a_table).each(function()
                {
                    createPropertySelects(this, elementType);
                })
            });

            //Acções para os botões.
            backButton.onclick = function() {
                //Criar a URL de acordo com o tipo de elemento que recebemos do PHP.
                if (elementType == "membro")
                {
                    url = "/index.php/insertClass/?type=propriedade&class=" + elementName + "&chamada=2";
                }
                else
                {
                    url = "/index.php/insertClass/?type=propriedade&class=" + elementName + "&chamada=1";
                }

                $.nmManual(url);
                return false;
            };

            insertButton.onclick = function()
            {
                //Para todos os tr da tabela de propriedades
                var all_tr_in_a_table = $("#propriedades tr");

                $(all_tr_in_a_table).each(function()
                {
                    insertProperty(this, elementName, elementType);
                });

                $.nmTop().close();
                return false;
            };
        </script>        
    </body>    
</html>
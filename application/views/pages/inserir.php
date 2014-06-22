<!-- Página de Inserção                                                      -->
<!-- Versão 3.3                                                              -->
<!-- Alterações:                                                             -->
<!-- - 2.0 Restruturação da página para apresentar os 3 tipos de inserção.   -->
<!-- - 3.0 Restruturação da página para apresentar os 4 tipos de inserção.   -->
<!-- - 3.1 Atualização das funções JS responsáveis pela inserção.            -->
<!-- - 3.2 Correcção de alguns bugs no formulário completo.                  -->
<!-- - 3.3 Limpeza de código, funções JS são agora chamadas externamente.    -->

<?php
header('Content-Type: text/html; charset=utf-8');
?>

<html>
    <head>
        <title><?php echo $title ?></title>
        <!-- Carregamento dos ficheiros JavaScript. -->
        <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
        <script type="text/javascript" src="/assets/js/JSfunctions.js"></script>
        <!-- Carregamento dos ficheiros CSS. -->
        <link rel="stylesheet" href="/assets/css/stylesheet.css" type="text/css" media="screen"/>
    </head>
    <body>
        <h2 align="left">&#8226; Classe: 
            <!-- Obtêm o nome da classe / membro onde vai ser feita a inserção. -->
            <?php echo htmlspecialchars($_GET["class"]); ?>
        </h2>                                 

        <!-- Construção do formulário para inserção -->
        <div align = "center">
            <form align = "center" class="forms">
                <!-- Verificação do tipo de inserção que vai ser feita e construir o formulário apropriado. -->
                <?php
                $tipoInsercao = $_GET["type"];
                //Se for Membro ou SubClasse usa o formulário completo.
                if ($tipoInsercao == "membro" || $tipoInsercao == "subclasse") {
                    echo "<p align=\"right\"><b>Novo " . htmlspecialchars($_GET["type"]) . "</b></p>";
                    //Caixa de texto para inserção do nome do novo elemento.
                    echo "<p align=\"left\">Label: <input type=\"text\" id=\"itemName\" style=\"width:427px\">";
                    //Caixa de texto para inserção do comentário do elemento.
                    echo "<br>";
                    echo "<p align=\"left\" class=\"formfield\">";
                    echo "<label for=\"itemDesc\">Descri&ccedil;&atilde;o:</label>";
                    echo "<textarea rows=7 cols=48 id=\"itemDesc\"></textarea>";
                    echo "</p>";
                    echo "<br>";
                    //Chamada da função getProperties existente no controller, para obter todas as propriedades.
                    $properties_link = "http://$_SERVER[HTTP_HOST]/index.php/getProperties";
                    $properties = file_get_contents($properties_link);
                    //Construção da tabela.
                    echo "<table id=\"propriedades\" border=0 align=center>";
                    echo $properties;
                    echo "</table>";
                    //Fim da tabela.
                    echo "<br>";
                    echo "<button id=\"insertNewItem\">Inserir</button>";
                    echo "<button id=\"cancelNewItem\">Cancelar</button>";
                } else if ($tipoInsercao == "comentario") {
                    echo "<p align=\"right\"><b>Editar coment&aacute;rio</b></p>";
                    //Caixa de texto para inserção do comentário do elemento.
                    echo "<p align=\"left\" class=\"formfield\">";
                    echo "<label for=\"itemDesc\">Descri&ccedil;&atilde;o:</label>";
                    echo "<textarea rows=7 cols=48 id=\"itemDesc\"></textarea>";
                    echo "</p>";
                    echo "<br>";
                    echo "<button id=\"insertNewItem\">Inserir</button>";
                    echo "<button id=\"cancelNewItem\">Cancelar</button>";
                } else if ($tipoInsercao == "propriedade") {
                    echo "<b>Escolha a op&ccedil;&atilde;o desejada:</b>";
                    echo "<br>";
                    echo "<br>";
                    echo "<input type=\"radio\" name=\"propriedade\" value=\"novaPropriedade\" checked>Nova Propriedade</input>";
                    echo "<br>";
                    echo "<input type=\"radio\" name=\"propriedade\" value=\"propriedadeExistente\">Propriedade j&aacute; existente</input>";
                    echo "<br>";
                    echo "<br>";
                    echo "<button id=\"cancelNewItem\">Cancelar</button>";
                    echo "<button id=\"nextStep\">Seguinte &#8594;</button>";
                } else {
                    echo "Tipo de inser&ccedil;&atilde;o n&atilde;o reconhecida...";
                    echo "<br>";
                    echo "<br>";
                    echo "<button id=\"cancelNewItem\">Fechar</button>";
                }
                ?>
            </form>
        </div>

        <!-- Código JavaScript para esta página -->
        <script type="text/javascript">
            //Permitir ao JavaScript utilizar os argumentos PHP recebidos.
            var $_GET = <?php echo json_encode($_GET); ?>;

            //Atribuir as variáveis PHP às variáveis JS.
            //O tipo de inserção que vai ser feita (novo membro, nova subclasse, comentário, propriedade). 
            var type = $_GET["type"];
            //O elemento aonde vai ser aplicado a inserção.
            var parentClass = $_GET["class"];
            //Se for feita inserção da propriedade, indicar no endereço se é para uma classe ou membro.
            var chamada = $_GET["chamada"];

            //Atribuir às variáveis, os objectos da página.
            var cancelButton = document.getElementById("cancelNewItem");

            //Outras variáveis.
            var insertResult = null;

            //Para a página de inserção de uma nova propriedade, se a propriedade é para uma classe ou para um membro.
            if (chamada == 1) {
                var fortype = "classe";
            } else {
                var fortype = "membro";
            }

            if (type == "membro" || type == "subclasse") {
                //No caso de ser uma inserção de um novo membro ou nova subclasse.
                //Atribuir às variáveis, os objectos da página.
                var insertButton = document.getElementById("insertNewItem");

                //Carregar as opções de escolha para cada propriedade.
                $(document).ready(function()
                {
                    var all_tr_in_a_table = $("#propriedades tr");

                    $(all_tr_in_a_table).each(function()
                    {
                        createPropertySelects(this, type);
                    })
                });

                //Acção do botão de inserção.
                insertButton.onclick = function()
                {
                    //Atribuir às variáveis, o conteúdo do formulário.
                    var itemName = document.getElementById("itemName").value;
                    var itemDescription = $('textarea#itemDesc').val();
                    //Remover caso exista, espaços no nome do elemento.
                    var itemName = itemName.replace(/\s/g, "");

                    //Outras variáveis
                    var id = null;
                    var propriedade = null;
                    var opcaoSelecionada = null;
                    var tipoPropriedade = null;

                    //Se o nome do elemento for vazio
                    if (itemName == '')
                    {
                        window.alert("Erro: O campo Label está vazio...");
                    }
                    else
                    {
                        //Chamada do metódo para a inserção do elemento.
                        if (type == "membro")
                        {
                            insertResult = insertMember(itemName, parentClass);
                        }
                        else
                        {
                            insertResult = insertClass(itemName, parentClass);
                        }

                        //Chamada do metódo de inserção do comentário se a inserção anterior tiver sucesso.
                        if (insertResult == 1 && itemDescription != '')
                        {
                            insertResult = insertComment(itemName, itemDescription);
                        }

                        //Chamada do metódo de inserção de propriedades se a inserção anterior tiver sucesso.
                        if (insertResult == 1)
                        {
                            //Para todos os tr da tabela de propriedades
                            var all_tr_in_a_table = $("#propriedades tr");

                            $(all_tr_in_a_table).each(function()
                            {
                                insertResult = insertProperty(this, itemName, type);
                            });

                            //Caso nenhuma propriedade tivesse sido seleccionada.
                            if (insertResult == null)
                            {
                                insertResult = 1;
                            }
                        }

                        if (insertResult == 1)
                        {
                            alert("Info: A inserção foi bem sucedida!");
                            $.nmTop().close();
                        }
                        else
                        {
                            alert("Erro: Ocorreu um erro durante o processo de inserção...");
                        }
                    }
                    //Impedir que a página seja atualizada.
                    return false;
                };
            }
            else if (type == "comentario") {
                //No caso de ser uma inserção de um comentário num elemento.
                //Atribuir às variáveis, os objectos das páginas.
                var insertButton = document.getElementById("insertNewItem");

                //Acção do botão de inserção.
                insertButton.onclick = function()
                {
                    //Atribuir à variável o conteúdo do formulário.
                    var itemDescription = $('textarea#itemDesc').val();

                    //Chamada do método que trata da inserção de comentários.
                    insertResult = insertComment(parentClass, itemDescription);

                    if (insertResult == 1)
                    {
                        alert("Info: A inserção do comentário foi bem sucedida!");
                        $.nmTop().close();
                    }
                    else
                    {
                        alert("Erro: Ocorreu um erro durante o processo de inserção do comentário...");
                    }

                    return false;                                                                     //Retorna falso para impedir da página atualizar.
                };
            }
            else {
                //No caso de ser uma inserção de uma propriedade.
                //Obtêm pelo ID o botão de continuação (apenas no caso de ser propriedade).
                var nextButton = document.getElementById("nextStep");

                //Acção do botão.
                nextButton.onclick = function()
                {
                    //Obtêm o valor do radiobutton seleccionado no momento em que se clica no botão.
                    var propSel = document.querySelector('input[name="propriedade"]:checked').value;

                    //Outras variáveis.
                    var url = null;

                    if (propSel == "novaPropriedade")
                    {
                        //Endereço para a página que trata da nova propriedade.
                        url = "/index.php/insertClass/novaPropriedade/?type=" + fortype + "&element=" + parentClass;
                    }
                    else
                    {
                        url = "/index.php/insertClass/inserirPropriedade/?type=" + fortype + "&element=" + parentClass;
                    }

                    //A janela modal é atualizada para o endereço presente na variável url.
                    $.nmManual(url);

                    //Retorna falso para impedir da página atualizar.
                    return false;
                };
            }

            //Acção do botão de cancelar (comum nos 3 casos).
            cancelButton.onclick = function()
            {
                $.nmTop().close();
                return false;
            };
        </script>
    </body>    
</html>
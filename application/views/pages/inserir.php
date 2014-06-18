<!-- Página de Inserção                                                      -->
<!-- Versão 3.2									                             -->
<!-- Alterações:                                                             -->
<!-- - 2.0 Restruturação da página para apresentar os 3 tipos de inserção.   -->
<!-- - 3.0 Restruturação da página para apresentar os 4 tipos de inserção.   -->
<!-- - 3.1 Atualização das funções JS responsáveis pela inserção.			 -->
<!-- - 3.2 Correcção de alguns bugs no formulário completo.                  -->

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
        <h2 align="left">&#8226; Classe: 
            <?php echo htmlspecialchars($_GET["class"]); ?></h2>                                 <!-- Obtêm o nome da classe / membro onde vai ser feita a inserção -->

        <div align = "center">
            <form align = "center" class="forms">
                <?php
                if ($_GET["type"] == "membro" || $_GET["type"] == "subclasse") {                       //Se for Membro ou SubClasse usa formulário normal
                    echo "<p align=\"right\"><b>Novo " . htmlspecialchars($_GET["type"]) . "</b></p>";
                    echo "<p align=\"left\">Label: <input type=\"text\" id=\"itemName\" style=\"width:427px\">";
                    echo "<br><br>";
                    echo "Descri&ccedil;&atilde;o: <input type=\"text\" id=\"itemDesc\" style=\"width:400px; height:150px;\">";
                    echo "<br><br>";
                    $Properties_link = "http://$_SERVER[HTTP_HOST]/index.php/getProperties";
                    $properties = file_get_contents($Properties_link);
                    echo "<table id=\"propriedades\" border=0 align=center>";
                    echo $properties;
                    echo "</table><br>";
                    //echo "<div class=\"props\" id=\"props\">";
                    // A div é tratada pelo JavaScript para chamar as propriedades.
                    //echo "</div></p>";		                
                    echo "<button id=\"insertNewItem\">Inserir</button>";
                    echo "<button id=\"cancelNewItem\">Cancelar</button>";
                } else if ($_GET["type"] == "propriedade") {
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
                } else if ($_GET["type"] == "comentario") {
                    echo "Descri&ccedil;&atilde;o: <input type=\"text\" id=\"itemDesc\" style=\"width:400px; height:150px;\">";
                    echo "<br></p>";
                    echo "<button id=\"insertNewItem\">Inserir</button>";
                    echo "<button id=\"cancelNewItem\">Cancelar</button>";
                } else {
                    echo "Tipo de inser&ccedil;&atilde;o n&atilde;o reconhecida...";
                    echo "<br>";
                    echo "<br>";
                    echo "<button id=\"cancelNewItem\">Fechar</button>";
                }
                ?>

            </form>
        </div>

        <!---------- Scripts JavaScript ---------->

        <script type="text/javascript">
            //Variáveis utilizadas para os 3 casos (Membros, SubClasses, Propriedades):
            var $_GET = <?php echo json_encode($_GET); ?>;							        //Permitir ao JavaScript buscar variaveis do PHP.
            var type = $_GET["type"];												        //Atribui o type que foi recebido pelo PHP.
            var classG = $_GET["class"];											        //Atribui a class que foi recebida pelo PHP.	
            var chamada = $_GET["chamada"];											        //Atribui a chamda que foi recebida pelo PHP (membro ou subclasse).
            var cancelButton = document.getElementById("cancelNewItem");                    //Obtêm pelo ID o botão de cancelar.

            if (chamada == 1)                                                                //Para a página de inserção de uma nova propriedade, se a propriedade é para uma classe ou para um membro.
            {
                var fortype = "classe";
            }
            else
            {
                var fortype = "membro";
            }

            if (type == "membro" || type == "subclasse")                                     //Se for adição de um membro ou SubClasse.
            {
                var insertButton = document.getElementById("insertNewItem");                //Obtêm pelo ID o botão de inserção.

                insertButton.onclick = function()                                          //Acção do botão de inserção.
                {
                    var itemText = document.getElementById("itemName").value;               //Obtêm o texto presente na caixa de texto.
                    var itemDescription = document.getElementById("itemDesc").value;
                    var text = itemText.replace(/\s/g, "");                                 //Remove espaços.

                    if (itemText == '')                                                      //Se o texto obtido é vazio apresenta mensagem de erro.
                    {
                        window.alert("Erro: O campo Label esta vazio...");
                    }
                    else
                    {
                        var url = "/index.php/insertData/" + type + "/" + text + "/" + classG;  //URL do metódo no controller que trata de inserções simples.

                        //Inserção do individuo.
                        $.post(url, function(result)
                        {
                            if (result == 1)                                                     //Se a inserção do individuo tiver sucesso
                            {
                                if (itemDescription != "")                                       //Se existir algo no campo Descrição.
                                {
                                    insertComment("comentario", text, "null", itemDescription); //Chamada do método que trata da inserção de comentários (ver no fim da página).
                                }
                                else
                                {
                                    window.alert("Mensagem: Insercao com sucesso!");
                                    $.nmTop().close();                                          //Fechar janela modal.
                                }
                            }
                            else
                            {
                                window.alert("Erro: Insercao sem sucesso!");
                            }
                        });
                    }

                    return false;                                                                   //Retorna falso para impedir da página atualizar.
                };

                $(document).ready(function() {														//Quando a página está pronta vai percorrer todos os tr's da tabela de propriedades
                    var all_tr_in_a_table = $("#propriedades tr");
                    $(all_tr_in_a_table).each(function() {
                        var parentID = $(this).attr('id');											//Vai buscar id do tr
                        var valor = $('#' + parentID + '> #valor').attr('value');					//Vai buscar valor do td dentro do tr selecionado
                        var data = null;
                        var data2 = null;
                        var url2 = "/index.php/getPropertyRange/" + valor + "/2";
                        var url = "/index.php/getPropertyRange/" + valor + "/1";    				//URL para obter o Range da Propriedade seleccionada.                  

                        var obj = XMLHttpObject();

                        if (obj) {
                            data2 = requestInformation(obj, url2);
                            data = requestInformation(obj, url);
                        }

                        if (data == "DatatypeProperty")
                        {
                            $('#' + parentID).append("<td id=\"range\"><input type=\"text\" id=\"datatypeProperty\" style=\"width: 150px\"></td>");
                        }
                        else
                        {
                            var dataSubClasses = null;
                            var htmlRange = "<td id=\"range\"><select id=\"drop\">";
                            htmlRange = htmlRange + "<option id=\"Nenhum\" value=\"Nenhum\">-</option>";
                            if (type == "subclasse") {
                                htmlRange = htmlRange + "<option id=\"Classe\" value=\"" + data2 + "\">" + data2 + "</option>";
                            }
                            htmlRange = htmlRange + getRangeRecursivo(data2, type);
                            htmlRange = htmlRange + "</select></td>";
                            $('#' + parentID).append(htmlRange);
                        }
                    });
                });
            }
            else if (type == "comentario")
            {
                var insertButton = document.getElementById("insertNewItem");

                insertButton.onclick = function()                                          //Acção do botão de inserção.
                {
                    var itemText = document.getElementById("itemDesc").value;               //Obtêm o texto presente na caixa de texto.	
                    var url_desc = '/index.php/getComment/' + classG;                       //Obtêm o comentário (se já existir) para o substituir.
                    var data = null;

                    data = requestInformation(obj, url_desc);

                    var decoded = $('<div>').html(data).text();			                    //O javascript com acentos e outros caracteres atrofia, isto é uma forma de contornar esse problema.      

                    insertComment(type, classG, decoded, itemText);				            //Chamada do método que trata da inserção de comentários (ver no fim da página).

                    return false;                                                                     //Retorna falso para impedir da página atualizar.
                };
            }
            else                                                                                      //No caso de for adição de uma propridade.
            {
                var nextButton = document.getElementById("nextStep");                                 //Obtêm pelo ID o botão de continuação (apenas no caso de ser propriedade).

                nextButton.onclick = function()
                {
                    var propSel = document.querySelector('input[name="propriedade"]:checked').value;  //Obtêm o valor do radiobutton seleccionado no momento em que se clica no botão.

                    if (propSel == "novaPropriedade")
                    {
                        var url = "/index.php/insertClass/novaPropriedade/?type=" + fortype;        //Endereço para a página que trata da nova propriedade.
                    }
                    else
                    {
                        alert("INFO: Ainda nao implementado...");
                    }

                    $.nmManual(url);

                    return false;                                                                    //Retorna falso para impedir da página atualizar.
                };
            }

            cancelButton.onclick = function()                                                       //Acção do botão de cancelar (comum nos 3 casos).
            {
                $.nmTop().close();
                return false;
            };

            //================================================================================================

            function XMLHttpObject()
            {
                var obj = null;

                if (window.XMLHttpRequest)
                {
                    obj = new XMLHttpRequest();                         //para browsers modernos. 
                }
                else
                {
                    obj = new ActiveXObject("Microsoft.XMLHTTP");       //para versões antigas do Internet Explorer (IE5 e IE6).
                }

                return obj;
            }

            function requestInformation(obj, url)
            {
                var data = null;

                if (obj)
                {
                    $.ajax({
                        url: url,
                        type: 'get',
                        dataType: 'html',
                        async: false,
                        success: function(result)
                        {
                            data = result;
                        }
                    });
                }
                else
                {
                    data = "Erro: Em adquirir a informação...";
                }

                return data;
            }

            function insertComment(type, classG, decoded, itemText)
            {
                var url_2 = "/index.php/insertData/" + type + "/" + classG + "/\"" + itemText + "\"";       //URL do metódo no controller que trata de inserções simples.			

                if (decoded != "null")
                {
                    var url_1 = "/index.php/deleteData/" + type + "/" + classG + "/\"" + decoded + "\"";       //URL do metódo do controller que trata de eliminações simples.

                    $.post(url_1, function(result)
                    {
                        if (result == 1)
                        {
                            $.post(url_2, function(result)
                            {
                                if (result == 1)
                                {
                                    window.alert("Mensagem: Insercao com sucesso!");
                                    $.nmTop().close();
                                }
                                else
                                {
                                    window.alert("Erro: Insercao sem sucesso!");
                                }
                            });
                        }
                    });
                }
                else
                {
                    $.post(url_2, function(result)
                    {
                        if (result == 1)
                        {
                            window.alert("Mensagem: Insercao com sucesso!");
                            $.nmTop().close();
                        }
                        else
                        {
                            window.alert("Erro: Insercao sem sucesso!");
                        }
                    });
                }
            }

            function botaoAdd(id) {
                //$('#propriedades').append("<tr><td>xxxxxx</td></tr>");
                var all_tr_in_a_table = $("#propriedades tr");
                $(all_tr_in_a_table).each(function() {
                    var parentID = $(this).attr('id');						//Vai buscar id do tr
                    if (parentID == id) {
                        $("#range").append("<tr><td><select>" + $("#range select").html() + "</select></td></tr>");
                    }
                });
            }

            function getRangeRecursivo(subclasse, type) {
                var obj = XMLHttpObject();
                var dataSubClasses = null;

                if (obj) {
                    var opcoes = requestInformation(obj, "/index.php/getMembers/" + subclasse + "/2");

                    dataSubClasses = requestInformation(obj, "/index.php/selectSubClasses/" + subclasse);
                    if (type == "subclasse") {
                        opcoes = opcoes + dataSubClasses;
                    }

                    if (dataSubClasses != null) {
                        var classes = dataSubClasses.split("\"");
                        var count = 7;
                        $.each(classes, function(index, chunk) {
                            if (index == count) {
                                count = count + 4;
                                opcoes = opcoes + getRangeRecursivo(chunk, type);
                            }
                        });
                    }
                    return opcoes;
                }
            }
        </script>



<!-- Página de inserção de uma nova propriedade

     Versão 1.0

     @author Mário Teixeira    1090626     1090626@isep.ipp.pt     
     @author Marta Graça       1100640     1100640@isep.ipp.pt

     Argumentos que recebe:
     type       ->  aonde é que vai ser inserido a nova propriedade, se é num membro ou classe;
     element    ->  nome do membro / classe onde vai ser inserido a nova propriedade.

     Exemplo:
     /index.php/novaPropriedade?type=membro&element=XPTO

     A nova propriedade vai ser inserida no elemento XPTO que é um membro.

     Nota:
     - A inserção esta a ser feita mas parece faltar algo na parte do controller.
-->

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
            &#8226; Inser&ccedil;&atilde;o de uma nova propriedade no(a)
            <?php echo htmlspecialchars($_GET["type"]); ?>:
            <?php echo htmlspecialchars($_GET["element"]); ?>
        </h2>

        <!-- Criação do formulário -->
        <div align = "center">
            <form align = "center" class="forms">
                <b>Insira o nome da propriedade: </b>
                <input type="text" id="propName" style="width: 280px">
                <br>
                <br>
                <b>Escolha o tipo de propriedade: </b>
                <br>
                <br>
                <input type="radio" name="propType" value="DatatypeProperty">DatatypeProperty</input>
                <div align="right" class="propTypeDiv" id="DTP_div" style="display: none;">
                    <input type="text" id="DTP_value" style="width: 400px"/>					
                </div>
                <br>
                <input type="radio" name="propType" value="ObjectProperty" checked>ObjectProperty</input>
                <div align="left" class="propTypeDiv" id="OP_div">
                    <input type="checkbox" class="property" value="FunctionalProperty">FunctionalProperty</input></br>
                    <input type="checkbox" class="property" value="InverseFunctionalProperty">InverseFunctionalProperty</input></br>
                    <input type="checkbox" class="property" value="TransitiveProperty">TransitiveProperty</input></br>
                    <input type="checkbox" class="property" value="SymmetricProperty">SymmetricProperty</input></br>
                    <input type="checkbox" class="property" value="AsymmetricProperty">AsymmetricProperty</input></br>
                    <input type="checkbox" class="property" value="ReflexiveProperty">ReflexiveProperty</input></br>
                    <input type="checkbox" class="property" value="IrreflexiveProperty">IrreflexiveProperty</input></br>
                    <!--<input type="checkbox" class="property" value="inverseOf">InverseOf</input></br>
                    <input type="checkbox" class="property" value="equivalentProperty">EquivalentProperty</input></br>
                    <input type="checkbox" class="property" value="range">Range</input></br>
                    <input type="checkbox" class="property" value="subPropertyOf">SubPropertyOf</input>-->
                </div>
                <br>
                <br>
                <button id="goBack">&#8592; Voltar</button>
                <button id="insertNewProp">Inserir</button>
            </form>
        </div>

        <!-- Código JavaScript para esta página -->
        <script type="text/javascript">
            //Atribuir as variáveis, os objectos da página.
            backButton = document.getElementById("goBack");
            insertButton = document.getElementById("insertNewProp");

            //Permitir ao JavaScript utilizar os argumentos PHP recebidos.
            $_GET = <?php echo json_encode($_GET); ?>;

            //Atribuir as variáveis PHP às variáveis JS.
            elementType = $_GET["type"];
            elementName = $_GET["element"];

            //Acções para os radiobuttons.
            $('input[type="radio"]').click(function() {
                //Verificar qual das radiobuttons foi escolhida e mostrar a respectiva DIV escondida.
                if ($(this).attr("value") == "DatatypeProperty")
                {
                    $("#DTP_div").show();
                    $("#OP_div").hide();
                }
                else
                {
                    $("#OP_div").show();
                    $("#DTP_div").hide();
                }
            });

            //Acções para os botões;
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
        
            insertButton.onclick = function(){
                //Atribuir o valor da caixa de texto propName à uma variável.
                propertyName = document.getElementById("propName").value;
                //Atribuir o valor do radiobutton à uma variável.
                propertyType = $('input[type="radio"]:checked').val();
                
                if(propertyName != "")
                {
                    //Remove (caso tenha) o background de erro.
                    $('.error_textbox').removeClass('error_textbox');  
                    
                    //Verifica qual é o tipo do elemento para chamar a função Javascript própria.
                    if(propertyType == "DatatypeProperty")
                    {
                        //insertNewProperty("novo1", propertyName, "ignore", propertyType);
                        alert("Ainda nao disponivel...");
                    }
                    else
                    {
                        result = insertNewProperty("novo1", propertyName, "ignore", propertyType);
                        
                        //Obter os valores de cada checkbox seleccionada.
                        $('.property:checkbox:checked').each(function()
                        {
                            insertNewProperty("novo2", propertyName, "type", ($(this).val()));                            
                        });
                        
                        $.nmTop().close();                        
                     
                    }
                }
                else
                {
                    //Adiciona o background de erro.
                    $('#propName').addClass('error_textbox');
                }
                //Fechar a janela nyroModal
                //$.nmTop().close();
                return false;
            };
        </script>
    </body>
</html>
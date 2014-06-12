/*
 * Funções JavaScript
 * - Este ficheiro vai conter funções para as páginas HTML.
 *
 * Versão 2.0
 *
 * Autores
 * - Mário Teixeira  1090626     1090626@isep.ipp.pt
 * - Marta Sofia     1100640     1100640@isep.ipp.pt
 *
 * Funções
 * - XMLHttpObject           (Linha 35)      Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
 * - requestInformation      (linha 51)      Envia o pedido http ao controller e retorna o resultado do XSLT.
 * - selectedElement         (linha 76)      Recebe o elemento, aplica a classe de iluminação e executa as funções cleanDIV e consultClass.
 * - cleanDIV                (linha 96)      Apenas faz limpeza da informação contida numa DIV.
 * - constructClassTree      (linha 104)     Faz o pedido ao controller e ao receber a lista de classes constroi a respectiva árvore.
 * - getSubClasses           (linha 115)     Recebe como parâmetro o nome da classe, faz um pedido de pesquisa de todas as subclasses pertencentes a ela e retorna o resultado em forma de lista.
 * - removeSpaces            (linha 131)     Remove todos os espaços que possam existir na variável. 
 * - highlightElement        (linha 139)     Ilumina o elemento da árvore de classes escolhido.
 * - consultMember           (linha 145)     Ao clicar num dos membros na div de contéudo, faz um pedido para obter informações sobre esse membro.
 * - consultClass            (linha 171)     Ao clicar num dos elementos da árvore, faz um pedido para obter informações sobre essa classe.
 * - appendMembers           (linha 205)     Adiciona a div de conteúdo os membros pertencentes à classe.
 * - appendSubClasses        (linha 217)     Adiciona a div de conteúdo as subclasses pertencentes à classe.
 * - appendProperties        (linha 229)     Adiciona a div de conteúdo as propriedades associadas à classe.
 * - deleteMember            (linha 251)     Elimina membros usando query simples.
 * - callFunctionsFromLink   (linha 264)     Chama funcionalidades JS apartir de certos links existentes na página.
 * - createModalWindow       (linha 293)     Chama as funcionalidades do nyroModal para criação de uma janela modal.
 * - createModelessWindow    (linha 328)     Chama as funcionalidades do nyroModal para criação de uma janela modeless.
 */

//Variaveis Globais:
var selectedClass = "null";

//Funções
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

function selectedElement(target)
{
    var nrElements = $(target).parent().find('li').length;     //obtem o nr de filhos (subclasses) que a classe tem na lista;
    var divContent = document.getElementById("content");
    var classLabel = removeSpaces(target);                      //Remove a URI e retorna apenas o nome da classe / indivíduo.

    highlightElement(target);                                   //Ilumina o elemento da árvore escolhido.
    cleanDIV(divContent);                                       //Apenas faz limpeza da informação contida na DIV de conteúdo.    
    consultClass(classLabel);                                   //Faz um pedido para obter informações sobre a classe escolhida.

    if (nrElements == 0)
    {
        getSubClasses(target);                                  //Faz um pedido para obter (se existirem) as subclasses da class escolhida.
    }
    else
    {
        $(target).parent().find('ul').remove();
    }
}

function cleanDIV(target)
{
    while (target.firstChild)
    {
        target.removeChild(target.firstChild);
    }
}

function constructClassTree(target)
{
    obj = XMLHttpObject();                                  //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.  

    url_Classes = "/index.php/listClasses";                 //url da função existente no Controller. 

    result = requestInformation(obj, url_Classes);          //chama a função que faz um pedido "get" ao servidor e recebe o resultado.

    $(target).append(result);                               //faz append do resultado html na DIV indicada.
}

function getSubClasses(parentClass)
{
    obj = XMLHttpObject();                                  //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    url_subClasses = "/index.php/listSubClasses/";          //url do método existente no Controller. 

    classLabel = removeSpaces(parentClass);                 //Remove os espaços.

    url_subClasses = url_subClasses + classLabel;           //adiciona ao url, a classe a ser feita a pesquisa de subClasses.

    result = requestInformation(obj, url_subClasses);       //chama o método que faz um pedido "get" ao servidor e recebe o resultado.

    parentID = $(parentClass).parent().attr('id');			//busca o ID do pai da classe para adicionar a informação na página.

    $('#' + parentID).append(result);						//faz append no elemento que tenha o ID obtido na linha anterior.
}

function removeSpaces(parentClass)
{
    classURI = $(parentClass).text();                        //recebe o texto dentro do elemento da lista.
    classLabel = classURI.replace(/\s/g, "");                 //remove todos os espaços que possam estar presentes.

    return classLabel;
}

function highlightElement(target)
{
    $('.highlight').removeClass('highlight');               //remove o highlight da classe que previamente estava selecionada.
    $(target).addClass('highlight');                        //adiciona ao target a classe highlight, ao ser adicionado o CSS adapta o efeito.
}

function elementVisibility(button)
{
    var listElement = $(button).closest('li').find('span');
    
    if($(listElement).is(":visible"))
    {
        $(listElement).hide();
        $(button).find('img').attr('src', "/assets/images/eye_closed.png");
    }
    else
    {
        $(listElement).show();
        $(button).find('img').attr('src', "/assets/images/eye_open.png");
    }
}

function consultMember(memberLabel)
{
    //Variáveis utilizadas
    url_properties = "/index.php/getMemberProperty/" + memberLabel;
    url_insert_comment = "/index.php/insertClass/?type=comentario&class=" + memberLabel + "&chamada=2";
    url_insert_prop = "/index.php/insertClass/?type=propriedade&class=" + memberLabel + "&chamada=2";
    url_uri = "/index.php/printURI";
    url_comment = "/index.php/getComment/" + memberLabel;

    obj = XMLHttpObject();
    result_uri = requestInformation(obj, url_uri);			//obter a URI do membro seleccionado.
    result_comment = requestInformation(obj, url_comment);  //obter o comentário do membro seleccionado.

    //Construção da DIV de conteúdo
    $(".content").append("<h3>Informa&ccedil;&otilde;es relativa ao membro: " + memberLabel + "<h3>");

    $(".content").append("<b>URI</b>: <a href=\"" + result_uri + "#" + memberLabel + "\" onclick=\"callFunctionsFromLink('" + memberLabel + "',2);return false;\">" + result_uri + "#" + memberLabel + "</a>");

    $(".content").append("<br><br><b>Coment&aacute;rio:</b> " + result_comment);

    $(".content").append("<br>&#8594; Para adicionar ou actualizar o coment&aacute;rio, clique no bot&atildeo ");
    $(".content").append("<button type=\"button\" onclick=\"createModalWindow(url_insert_comment,'" + memberLabel + "', 2)\"><img src=\"/assets/images/add.png\" width=\"24px\" height=\"24px\"/></button>");

    appendProperties(obj, memberLabel, url_insert_prop, url_properties, 2);
}

function consultClass(classLabel)
{
    //Variáveis utilizadas
    url_members = "/index.php/getMembers/" + classLabel + "/1";
    url_subclasses = "/index.php/getSubClasses/" + classLabel;
    url_insert_comment = "/index.php/insertClass/?type=comentario&class=" + classLabel + "&chamda=1";
    url_insert_member = "/index.php/insertClass/?type=membro&class=" + classLabel + "&chamada=1";
    url_insert_subclass = "/index.php/insertClass/?type=subclasse&class=" + classLabel + "&chamada=1";
    url_insert_prop = "/index.php/insertClass/?type=propriedade&class=" + classLabel + "&chamada=1";
    url_uri = "/index.php/printURI";
    url_comment = "/index.php/getComment/" + classLabel;

    obj = XMLHttpObject();									//Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.	
    result_uri = requestInformation(obj, url_uri);			//obter a URI da Classe seleccionada.
    result_comment = requestInformation(obj, url_comment);  //obter o comentário da Classe seleccionada.

    selectedClass = classLabel;

    //Construção da DIV de conteúdo
    $(".content").append("<h3>Informa&ccedil;&otilde;es relativa &agrave; classe: " + classLabel + "<h3>");

    $(".content").append("<b>URI</b>: <a href=\"" + result_uri + "#" + classLabel + "\" onclick=\"callFunctionsFromLink('" + classLabel + "',1);return false;\">" + result_uri + "#" + classLabel + "</a><br><br>");

    $(".content").append("<b>Coment&aacute;rio:</b> " + result_comment + "<br>");

    $(".content").append("&#8594; Para adicionar ou actualizar o coment&aacute;rio, clique no bot&atildeo ");
    $(".content").append("<button type=\"button\" onclick=\"createModalWindow(url_insert_comment,'" + classLabel + "', 1)\"><img src=\"/assets/images/add.png\" width=\"24px\" height=\"24px\"/></button><br><br>");

    //Chamada de funções para cada secção do DIV
    appendMembers(obj, classLabel, url_insert_member, url_members);
    appendSubClasses(obj, classLabel, url_insert_subclass, url_subclasses);
    appendProperties(obj, classLabel, url_insert_prop, "", 1);
}

function appendMembers(obj, classLabel, url_insert_member, url_members)
{
    $(".content").append("<b>Membros pertencentes &agrave; classe:</b>");

    result_members = requestInformation(obj, url_members);

    $(".content").append(result_members);

    $(".content").append("&#8594; Para adicionar um novo membro, clique no bot&atildeo ");
    $(".content").append("<button type=\"button\" onclick=\"createModalWindow(url_insert_member,'" + classLabel + "', 1)\"><img src=\"/assets/images/add.png\" width=\"24px\" height=\"24px\"/></button>");
}

function appendSubClasses(obj, classLabel, url_insert_subclass, url_subclasses)
{
    $(".content").append("<br><br><b>SubClasses pertencentes &agrave; classe:</b>");

    result_subclasses = requestInformation(obj, url_subclasses);

    $(".content").append(result_subclasses);

    $(".content").append("&#8594; Para adicionar uma nova subclasse da classe " + classLabel + ", clique no bot&atildeo ");
    $(".content").append("<button type=\"button\" onclick=\"createModalWindow(url_insert_subclass, '" + classLabel + "', 1)\"><img src=\"/assets/images/add.png\" width=\"24px\" height=\"24px\"/></button>");
}

function appendProperties(obj, label, url_insert_prop, url_properties, tipo)
{
    $(".content").append("<br><br><b>Propriedades associadas &agrave; classe:</b>");

    if (url_properties != "")
    {
        result_properties = requestInformation(obj, url_properties);
        $(".content").append(result_properties);
    }

    if (tipo == "1")
    {
        $(".content").append("<br>&#8594; Para adicionar uma nova propriedade &agrave; classe " + label + ", clique no bot&atildeo ");
        $(".content").append("<button type=\"button\" onclick=\"createModalWindow(url_insert_prop, '" + label + "', 1)\"><img src=\"/assets/images/add.png\" width=\"24px\" height=\"24px\"/></button>");
    }
    else if (tipo == "2")
    {
        $(".content").append("<br>&#8594; Para adicionar uma nova propriedade ao membro " + label + ", clique no bot&atildeo ");
        $(".content").append("<button type=\"button\" onclick=\"createModalWindow(url_insert_prop, '" + label + "', 2)\"><img src=\"/assets/images/add.png\" width=\"24px\" height=\"24px\"/></button>");
    }
}

function deleteMember(memberLabel, classLabel)
{
    //Variáveis utilizadas
    var url_deleteMember = "/index.php/deleteData/membro/" + memberLabel + "/" + classLabel;
    var divCont = document.getElementById("content");

    $.post(url_deleteMember, function(result)
    {
        cleanDIV(divCont);
        consultClass(classLabel);
    });
}

function callFunctionsFromLink(label, chamada)
{
    var divContent = document.getElementById("content");

    cleanDIV(divContent);

    if (chamada == "1")
    {
        consultClass(label);
    }
    else if (chamada == "2")
    {
        consultMember(label);
    }
    else if (chamada == "3")
    {
        /*Not done yet*/
    }
    else if (chamada == "4")
    {
        deleteMember(label, selectedClass);
    }
    else
    {
        alert("Erro: Erro em chamar a funcao de consulta.");
    }

}

function createModalWindow(url, classParent, chamada)
{
    $.nmObj({
        forcetype: 'iframe',
        modal: true,
        sizes:
                {
                    minW: 500,
                    minH: 600
                },
        callbacks:
                {
                    afterClose: function()
                    {
                        var divCont = document.getElementById("content");
                        var divMenu = document.getElementById("menu");
                        var element = $(".highlight").get();
                        //actualizar a árvore de classes:
                        $(element).parent().find('ul').remove();
                        getSubClasses(element);
                        //actualizar a página de consulta:                           
                        cleanDIV(divCont);
                        if (chamada == 1)
                        {
                            consultClass(classParent);
                        }
                        else
                        {
                            consultMember(classParent);
                        }

                    }
                }
    });
    $.nmManual(url);

    return false;
}

function createModelessWindow(url)
{
    $.nmObj({
        forcetype: 'iframe',
        modal: false,
        sizes:
                {
                    minW: 500,
                    minH: 600
                },
    });
    $.nmManual(url);

    return false;
}

function testes()
{
    alert("Info: Ainda nao desenvolvido...");
}
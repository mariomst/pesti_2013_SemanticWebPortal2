/*
 * Funções JavaScript
 * - Este ficheiro vai conter funções para as páginas HTML.
 *
 * Versão 2.3
 *
 * Autores
 * - Mário Teixeira  1090626     1090626@isep.ipp.pt
 * - Marta Graça     1100640     1100640@isep.ipp.pt
 *
 * Funções
 * - XMLHttpObject                Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
 * - requestInformation           Envia o pedido http ao controller e retorna o resultado do XSLT.
 * - selectedElement              Recebe o elemento, aplica a classe de iluminação e executa as funções cleanDIV e consultClass.
 * - cleanDIV                     Apenas faz limpeza da informação contida numa DIV.
 * - constructClassTree           Faz o pedido ao controller e ao receber a lista de classes constroi a respectiva árvore.
 * - getSubClasses                Recebe como parâmetro o nome da classe, faz um pedido de pesquisa de todas as subclasses pertencentes a ela e retorna o resultado em forma de lista.
 * - removeSpaces                 Remove todos os espaços que possam existir na variável. 
 * - highlightElement             Ilumina o elemento da árvore de classes escolhido.
 * - elementVisibility            Esconde ou mostra um elemento da árvore de classes.
 * - consultMember                Ao clicar num dos membros na div de contéudo, faz um pedido para obter informações sobre esse membro.
 * - consultClass                 Ao clicar num dos elementos da árvore, faz um pedido para obter informações sobre essa classe.
 * - appendMembers                Adiciona a div de conteúdo os membros pertencentes à classe.
 * - appendSubClasses             Adiciona a div de conteúdo as subclasses pertencentes à classe.
 * - appendProperties             Adiciona a div de conteúdo as propriedades associadas à classe.
 * - deleteClass                  Elimina classes e respectivas associações.
 * - deleteMember                 Elimina membros usando query simples.
 * - deleteComment                Elimina o comentário do elemento indicado.
 * - callFunctionsFromLink        Chama funcionalidades JS apartir de certos links existentes na página.
 * - createModalWindow            Chama as funcionalidades do nyroModal para criação de uma janela modal.
 * - createModelessWindow         Chama as funcionalidades do nyroModal para criação de uma janela modeless.
 */

//Variaveis Globais:
var selectedClass = "null";

//Funções
function XMLHttpObject()
{
    var obj = null;

    if (window.XMLHttpRequest)
    {
        //Para browsers modernos. 
        obj = new XMLHttpRequest();
    }
    else
    {
        //Para versões antigas do Internet Explorer (IE5 e IE6).
        obj = new ActiveXObject("Microsoft.XMLHTTP");
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
    //Obtêm o nr de filhos (subclasses) que a classe tem na lista;
    var nrElements = $(target).parent().find('li').length;
    //Fica apontar para a DIV de conteúdo
    var divContent = document.getElementById("content");
    //Remove espaços que possam existir na variavel recebida
    var classLabel = removeSpaces(target);

    //Ilumina o elemento da árvore escolhido.
    highlightElement(target);
    //Apenas faz limpeza da informação contida na DIV de conteúdo.
    cleanDIV(divContent);
    //Faz um pedido para obter informações sobre a classe escolhida.
    consultClass(classLabel);

    if (nrElements == 0)
    {
        //Faz um pedido para obter (se existirem) as subclasses da class escolhida.
        getSubClasses(target);
    }
    else
    {
        //Remove todos os filhos (subclasses) da classe escolhida da árvore.
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
    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser. 
    obj = XMLHttpObject();

    //URL da função existente no Controller. 
    url_Classes = "/index.php/listClasses";

    //Chama a função que faz um pedido "get" ao servidor e recebe o resultado.
    result = requestInformation(obj, url_Classes);

    //Faz append do resultado html na DIV indicada.
    $(target).append(result);
}

function getSubClasses(parentClass)
{
    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    obj = XMLHttpObject();

    //URL do método existente no Controller. 
    url_subClasses = "/index.php/listSubClasses/";

    //Remove os espaços.
    classLabel = removeSpaces(parentClass);

    //Adiciona ao URL, a classe a ser feita a pesquisa de subClasses.
    url_subClasses = url_subClasses + classLabel;

    //Chama o método que faz um pedido "get" ao servidor e recebe o resultado.
    result = requestInformation(obj, url_subClasses);

    //Busca o ID do pai da classe para adicionar a informação na página.
    parentID = $(parentClass).parent().attr('id');

    //Faz append no elemento que tenha o ID obtido na linha anterior.
    $('#' + parentID).append(result);
}

function removeSpaces(parentClass)
{
    //Recebe o texto dentro do elemento da lista.
    classURI = $(parentClass).text();
    //Remove todos os espaços que possam estar presentes.
    classLabel = classURI.replace(/\s/g, "");

    return classLabel;
}

function highlightElement(target)
{
    //Remove o highlight da classe que previamente estava selecionada.
    $('.highlight').removeClass('highlight');
    //Adiciona ao target a classe highlight, ao ser adicionado o CSS adapta o efeito.
    $(target).addClass('highlight');
}

function elementVisibility(button)
{
    //Obtêm o elemento 'span' mais perto do botão.
    var listElement = $(button).closest('li').find('span');

    //Verifica se o elemento esta ou não visível.
    if ($(listElement).is(":visible"))
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

    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    obj = XMLHttpObject();
    //Obter a URI do membro seleccionado.
    result_uri = requestInformation(obj, url_uri);
    //Obter o comentário do membro seleccionado.
    result_comment = requestInformation(obj, url_comment);

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

    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    obj = XMLHttpObject();
    //Obter a URI da Classe seleccionada.
    result_uri = requestInformation(obj, url_uri);
    //Obter o comentário da Classe seleccionada.
    result_comment = requestInformation(obj, url_comment);

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

    //Obter todos os membros da classe seleccionada.
    result_members = requestInformation(obj, url_members);

    //Adição dos resultado na DIV
    $(".content").append(result_members);

    $(".content").append("&#8594; Para adicionar um novo membro, clique no bot&atildeo ");
    $(".content").append("<button type=\"button\" onclick=\"createModalWindow(url_insert_member,'" + classLabel + "', 1)\"><img src=\"/assets/images/add.png\" width=\"24px\" height=\"24px\"/></button>");
}

function appendSubClasses(obj, classLabel, url_insert_subclass, url_subclasses)
{
    $(".content").append("<br><br><b>SubClasses pertencentes &agrave; classe:</b>");

    //Obter todos as subclasses da classe seleccionada.
    result_subclasses = requestInformation(obj, url_subclasses);

    //Adição dos resultado na DIV
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

function deleteClass(classLabel, superClassLabel)
{
    //Variáveis utilizadas
    url_membersList = "/index.php/getMembers/" + classLabel + "/3";
    url_subClassesList = "/index.php/listSubClasses/" + classLabel;
    url_deleteClass = "/index.php/deleteData/classe/" + classLabel + "/" + superClassLabel;
    divCont = document.getElementById("content");

    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    obj = XMLHttpObject();
    //Obter a lista de subclasses da Classe seleccionada.
    result_subClassesList = requestInformation(obj, url_subClassesList);
    //Obter a lista de membros da Classe seleccionada.
    result_membersList = requestInformation(obj, url_membersList);
    //Obter o comentário da Classe seleccionada.
    result_comment = requestInformation(obj, url_comment);

    //Obter o tamanho da lista de subClasses.
    subClassesList_length = $(result_subClassesList).find('li').length;
    //Obter o tamanho da lista de membros.
    membersList_length = $(result_membersList).find('li').length;

    //Verifica se existem subclasses da Classe a ser eliminada.
    if (subClassesList_length != 0)
    {
        $(result_subClassesList).find('li').each(function()
        {
            deleteClass($(this).text(), classLabel);
        });
    }
    //Verifica se existem membros da Classe a ser eliminada.
    if (membersList_length != 0)
    {
        $(result_membersList).find('li').each(function()
        {
            deleteMember($(this).text(), classLabel);
        });
    }

    //Apagar comentário associado a Classe
    deleteComment(classLabel);

    $.post(url_deleteClass, function(result)
    {
        if (result != 1)
        {
            alert("Erro: Eliminacao da classe sem sucesso...");
        }
    });
}

function deleteMember(memberLabel, classLabel)
{
    //Variáveis utilizadas
    url_deleteMember = "/index.php/deleteData/membro/" + memberLabel + "/" + classLabel;
    divCont = document.getElementById("content");

    $.post(url_deleteMember, function(result)
    {
        //Se a eliminação do membro for bem sucedida.
        if (result == 1)
        {
            //Eliminação do comentário associado ao membro.
            deleteComment(memberLabel);
        }
        else
        {
            alert("Erro: Eliminacao do membro sem sucesso...");
        }
    });
}

function deleteComment(element)
{
    //Variáveis utilizadas
    url_deleteComment = "/index.php/deleteData/comentario/" + element + "/";
    url_comment = "/index.php/getComment/" + element;

    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    obj = XMLHttpObject();

    //Obter o comentário (se existir) do elemento indicado.
    comment = requestInformation(obj, url_comment);
    convertString = $('<div>').html(comment).text();

    url_deleteComment = url_deleteComment + "\"" + convertString + "\"";

    //Eliminação do comentário.
    $.post(url_deleteComment, function(result)
    {
        return(result);
    });
}

function deleteProperties(element)
{
    //Ainda não desenvolvido...
}

function callFunctionsFromLink(label, chamada)
{
    var divContent = document.getElementById("content");

    /*
     * Descrição das chamadas:
     * 1 - consulta de uma Classe.
     * 2 - consulta de um Membro.
     * 3 - eliminação de uma Classe.
     * 4 - eliminação de um Membro.
     */

    if (chamada == "1")
    {
        //Atualização da div de conteúdo.
        cleanDIV(divContent);
        consultClass(label);
    }
    else if (chamada == "2")
    {
        //Atualização da div de conteúdo.
        cleanDIV(divContent);
        consultMember(label);
    }
    else if (chamada == "3")
    {
        deleteClass(label, selectedClass);
        //Atualização da div de conteúdo.
        cleanDIV(divCont);
        consultClass(selectedClass);
    }
    else if (chamada == "4")
    {
        deleteMember(label, selectedClass);
        //Atualização da div de conteúdo.
        cleanDIV(divCont);
        consultClass(selectedClass);
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
                        //Actualizar a árvore de classes:
                        $(element).parent().find('ul').remove();
                        getSubClasses(element);
                        //Actualizar a página de consulta:                           
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
    alert("Info: Ainda em fase de testes...");
}
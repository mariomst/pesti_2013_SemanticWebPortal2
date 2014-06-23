/*
 * Funções JavaScript
 * - Este ficheiro vai conter funções para as páginas HTML.
 *
 * Versão 2.6
 *
 * Autores
 * - Mário Teixeira  1090626     1090626@isep.ipp.pt
 * - Marta Graça     1100640     1100640@isep.ipp.pt
 *
 * Funções
 * - XMLHttpObject                Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
 * - requestInformation           Envia o pedido http ao controller e retorna o resultado do XSLT.
 * - requestUpdate                Envia o pedido post ao controller para a inserção de informação.
 * - selectedElement              Recebe o elemento, aplica a classe de iluminação e executa as funções cleanDIV e consultClass.
 * - cleanDIV                     Apenas faz limpeza da informação contida numa DIV.
 * - constructClassTree           Faz o pedido ao controller e ao receber a lista de classes constroi a respectiva árvore.
 * - getSubClasses                Recebe como parâmetro o nome da classe, faz um pedido de pesquisa de todas as subclasses pertencentes a ela e retorna o resultado em forma de lista.
 * - removeSpaces                 Remove todos os espaços que possam existir na variável. 
 * - highlightElement             Ilumina o elemento da árvore de classes escolhido.
 * - elementVisibility            Esconde ou mostra um elemento da árvore de classes.
 * - createPropertySelects        Cria as opções de escolha para cada propriedade.
 * - getRecursiveRange            Obtêm recursivamente todos os elementos do range de uma propriedade.
 * - botaoAdd                     Adiciona mais um select (página de inserção de propriedades).
 * - consultMember                Ao clicar num dos membros na div de contéudo, faz um pedido para obter informações sobre esse membro.
 * - consultClass                 Ao clicar num dos elementos da árvore, faz um pedido para obter informações sobre essa classe.
 * - appendMembers                Adiciona a div de conteúdo os membros pertencentes à classe.
 * - appendSubClasses             Adiciona a div de conteúdo as subclasses pertencentes à classe.
 * - appendProperties             Adiciona a div de conteúdo as propriedades associadas à classe.
 * - insertClass                  Inserção de uma nova subclasse na classe indicada.
 * - insertMember                 Inserção de um novo membro na classe indicada.
 * - insertComment                Inserção do comentário no elemento indicado.
 * - insertProperty               Inserção de propriedade no elemento indicado.
 * - insertNewProperty            Inserção de uma nova propriedade.
 * - deleteClass                  Elimina classes e respectivas associações.
 * - deleteMember                 Elimina membros usando query simples.
 * - deleteComment                Elimina o comentário do elemento indicado.
 * - deleteProperties             Elimina as propriedades associadas ao elemento.
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

function requestUpdate(obj, url)
{
    var result = null;

    if (obj)
    {
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'html',
            async: false,
            success: function()
            {
                result = 1;
            },
            error: function()
            {
                result = 0;
            }
        });
    }
    else
    {
        result = 0;
    }

    return result;
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

function createPropertySelects(element, type)
{
    //Variáveis utilizadas.
    var parentID = $(element).attr('id');
    var value = $('#' + parentID + '> #valor').attr('value');
    var data_1 = null;
    var data_2 = null;
    var url_1 = "/index.php/getPropertyRange/" + value + "/1";
    var url_2 = "/index.php/getPropertyRange/" + value + "/2";

    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    var obj = XMLHttpObject();

    //Obter a informação da função getPropertyRange.
    if (obj)
    {
        data_1 = requestInformation(obj, url_1);
        data_2 = requestInformation(obj, url_2);
    }

    if (data_1 == "DatatypeProperty")
    {
        $('#' + parentID).append("<td id=\"range\"><input type=\"text\" id=\"datatypeProperty\" style=\"width: 150px\"></td>");
    }
    else
    {
        var dataSubClasses = null;
        var htmlRange = "<td id=\"range\"><select id=\"drop\">";
        htmlRange = htmlRange + "<option id=\"Nenhum\" value=\"Nenhum\">-</option>";
        if (type == "subclasse")
        {
            htmlRange = htmlRange + "<option id=\"Classe\" value=\"" + data_2 + "\">" + data_2 + "</option>";
        }
        htmlRange = htmlRange + getRecursiveRange(data_2, type, obj);
        htmlRange = htmlRange + "</select></td>";
        $('#' + parentID).append(htmlRange);
    }

    //alert(value);
}

function getRecursiveRange(subclasse, type, obj)
{
    //Variáveis utilizadas.
    var url_1 = "/index.php/getMembers/" + subclasse + "/2";
    var url_2 = "/index.php/selectSubClasses/" + subclasse;
    var options = null;
    var dataSubClasses = null;

    if (obj)
    {
        //Obter a resposta dos URLs.
        options = requestInformation(obj, url_1);
        dataSubClasses = requestInformation(obj, url_2);

        if (type == "subclasse")
        {
            options = options + dataSubClasses;
        }

        if (dataSubClasses != null)
        {
            var classes = dataSubClasses.split("\"");
            var count = 7;
            $.each(classes, function(index, chunk)
            {
                if (index == count)
                {
                    count = count + 4;
                    options = options + getRecursiveRange(chunk, type, obj);
                }
            });
        }

        return options;
    }
}

function botaoAdd(id)
{
    var all_tr_in_a_table = $("#propriedades tr");
    $(all_tr_in_a_table).each(function()
    {
        var parentID = $(this).attr('id');
        if (parentID == id)
        {
            $("#" + id + " #range").append("<tr><td><select>" + $("#" + id + " #range select").html() + "</select></td><td><button onclick=\"deleteSelect(this);return false;\"><img src=\"/assets/images/delete.png\" width=\"24px\" height=\"24px\"/></button></td></tr>");
        }
    });
}

function deleteSelect(element)
{
    //Obtêm o elemento 'span' mais perto do botão.
    var selectElement = $(element).closest('tr');
    
    $(selectElement).remove();
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
    url_insert_comment = "/index.php/insertClass/?type=comentario&class=" + classLabel + "&chamada=1";
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

function consultProperty(propertyLabel)
{
    //Endereços utilizados.
    var url_uri = "/index.php/printURI";
    var url_range = "/index.php/getPropertyRange/" + propertyLabel + "/2";
    var url_comment = "/index.php/getComment/" + propertyLabel;
    var url_type = "" + propertyLabel;    
    var url_insert_comment = "/index.php/insertClass/?type=comentario&class=" + propertyLabel + "&chamada=1";
    
    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    var obj = XMLHttpObject();
    
    //Obter a URI da propriedade seleccionada.
    var result_uri = requestInformation(obj, url_uri);
    //Obter o comentário da propriedade seleccionada.
    var result_comment = requestInformation(obj, url_comment);
    //Obter o range da propriedade seleccionada.
    var result_range = requestInformation(obj, url_range);
    
    //Construção da DIV de conteúdo
    $(".content").append("<h3>Informa&ccedil;&otilde;es relativa &agrave; propriedade: " + propertyLabel + "<h3>");
    
    $(".content").append("<b>URI</b>: <a href=\"" + result_uri + "#" + propertyLabel + "\" onclick=\"callFunctionsFromLink('" + propertyLabel + "',5);return false;\">" + result_uri + "#" + propertyLabel + "</a><br><br>");
    
    $(".content").append("<b>Coment&aacute;rio:</b> " + result_comment + "<br>");
    
    $(".content").append("&#8594; Para adicionar ou actualizar o coment&aacute;rio, clique no bot&atildeo ");
    $(".content").append("<button type=\"button\" onclick=\"createModalWindow('" + url_insert_comment + "','" + propertyLabel + "', 3)\"><img src=\"/assets/images/add.png\" width=\"24px\" height=\"24px\"/></button><br><br>");

    $(".content").append("<b>Range da propriedade " + propertyLabel + ":</b> " + result_range);
    
    $(".content").append("<br><br><b>URI</b>: <a href=\"" + result_uri + "#" + result_range + "\" onclick=\"callFunctionsFromLink('" + result_range + "',2);return false;\">" + result_uri + "#" + result_range + "</a><br><br>");
    
    $(".content").append("<b>Tipo da propriedade " + propertyLabel + ":</b> ");
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

function insertClass(classLabel, superClassLabel)
{
    //Variáveis utilizadas.
    var url_insertClass = "/index.php/insertData/subclasse/" + classLabel + "/" + superClassLabel;

    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    var obj = XMLHttpObject();

    //Pedido POST para a inserção do comentário.
    var update = requestUpdate(obj, url_insertClass);

    return update;
}

function insertMember(memberLabel, ClassLabel)
{
    //Variáveis utilizadas.
    var url_insertMember = "/index.php/insertData/membro/" + memberLabel + "/" + ClassLabel;

    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    var obj = XMLHttpObject();

    //Pedido POST para a inserção do comentário.
    var update = requestUpdate(obj, url_insertMember);

    return update;
}

function insertComment(elementName, comment)
{
    //Variáveis utilizadas.
    var url_insertComment = "/index.php/insertData/comentario/" + elementName + "/\"" + comment + "\"";

    //Eliminar comentário anterior caso exista.
    deleteComment(elementName);

    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    var obj = XMLHttpObject();

    //Pedido POST para a inserção do comentário.
    var update = requestUpdate(obj, url_insertComment);

    return update;
}

function insertProperty(table_element, element, type)
{
    //Variáveis utilizadas.
    var parentID = $(table_element).attr('id');
    var propriedade = $("#" + parentID + " #valor").attr('value');
    var result = null;

    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    var obj = XMLHttpObject();

    //Para os td com selects.
    var all_selects_in_td = $("#" + parentID + " #range select");

    $(all_selects_in_td).each(function()
    {
        var opcaoSelecionada = $(this).children("option").filter(":selected").text();
        var tipoPropriedade = $(this).children("option").filter(":selected").attr('id');

        if (opcaoSelecionada != "" && opcaoSelecionada != "-")
        {
            var url_1 = "/index.php/insertProperty/";

            if (type == "membro")
            {
                url_1 = url_1 + type + "/" + element + "/" + propriedade + "/" + opcaoSelecionada + "/null";
            }
            else
            {
                if (tipoPropriedade == "Classe")
                {
                    url_1 = url_1 + "naoFixo/" + element + "/null/" + propriedade + "/" + opcaoSelecionada;
                }
                else if (tipoPropriedade == "Membro")
                {
                    url_1 = url_1 + "fixo/" + element + "/null/" + propriedade + "/" + opcaoSelecionada;
                }
            }
            //Pedido POST para a inserção da propriedade.
            var update = requestUpdate(obj, url_1);
        }
        result = update;
    });

    return result;

    //Para os td com inputs (os das propriedades datatype).
//    var all_inputs_in_td = $("#" + parentID + " #range input");
//    
//    $(all_inputs_in_td).each(function() 
//    {
//        var opcaoSelecionada = $(this).val();
//        
//        if (opcaoSelecionada != "") 
//        {
//            var url_2 = "/index.php/insertProperty/";
//            
//            if (type == "membro") 
//            {
//                url_2 = url_2 + type + "/" + propriedade + "/" + opcaoSelecionada;
//            } 
//            else 
//            {
//                if (tipoPropriedade == "Classe") 
//                {
//                    url_2 = url_2 + "naoFixo/" + propriedade + "/" + opcaoSelecionada;
//                } 
//                else if (tipoPropriedade == "Membro") 
//                {
//                    url_2 = url_2 + "fixo/" + propriedade + "/" + opcaoSelecionada;
//                }
//            }
//            $.post(url_2, function(result)
//            {
//                return(result);
//            });
//        }
//    });
}

function insertNewProperty(step, propertyName, predicate, propertyType)
{
    //Variáveis utilizadas.
    var url_insertNewProperty = "/index.php/insertProperty/" + step + "/" + propertyName + "/" + predicate + "/" + propertyType + "/ignore";

    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    var obj = XMLHttpObject();

    //Pedido POST para a inserção da propriedade.
    var update = requestUpdate(obj, url_insertNewProperty);

    if (update != 1)
    {
        alert("Erro: Insercao de propriedade sem sucesso.");
    }
    else
    {
        if (step == "novo2")
        {
            alert("Erro: Insercao de propriedade com sucesso.");
        }
    }
}

function deleteClass(classLabel, superClassLabel)
{
    //Variáveis utilizadas
    var url_membersList = "/index.php/getMembers/" + classLabel + "/3";
    var url_subClassesList = "/index.php/listSubClasses/" + classLabel;
    var url_deleteClass = "/index.php/deleteData/classe/" + classLabel + "/" + superClassLabel;
    var divCont = document.getElementById("content");

    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    var obj = XMLHttpObject();
    //Obter a lista de subclasses da Classe seleccionada.
    var result_subClassesList = requestInformation(obj, url_subClassesList);
    //Obter a lista de membros da Classe seleccionada.
    var result_membersList = requestInformation(obj, url_membersList);
    //Obter o comentário da Classe seleccionada.
    var result_comment = requestInformation(obj, url_comment);

    //Obter o tamanho da lista de subClasses.
    var subClassesList_length = $(result_subClassesList).find('li').length;
    //Obter o tamanho da lista de membros.
    var membersList_length = $(result_membersList).find('li').length;

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

    //Pedido POST para a eliminação da classe indicada.
    var update = requestUpdate(obj, url_deleteClass);

    if (update != 1)
    {
        alert("Erro: Eliminacao da classe sem sucesso...");
    }
}

function deleteMember(memberLabel, classLabel)
{
    //Variáveis utilizadas
    var url_deleteMember = "/index.php/deleteData/membro/" + memberLabel + "/" + classLabel;
    var divCont = document.getElementById("content");

    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    var obj = XMLHttpObject();

    //Pedido POST para a eliminação da classe indicada.
    var update = requestUpdate(obj, url_deleteMember);

    if (update != 1)
    {
        alert("Erro: Eliminacao do membro sem sucesso...");
    }
    else
    {
        //Eliminação do comentário associado ao membro.
        deleteComment(memberLabel);
    }
}

function deleteComment(element)
{
    //Variáveis utilizadas
    var url_deleteComment = "/index.php/deleteData/comentario/" + element + "/";
    var url_comment = "/index.php/getComment/" + element;

    //Retorna o objecto XMLHttpRequest de acordo com o tipo de browser.
    var obj = XMLHttpObject();

    //Obter o comentário (se existir) do elemento indicado.
    var comment = requestInformation(obj, url_comment);
    var convertString = $('<div>').html(comment).text();

    url_deleteComment = url_deleteComment + "\"" + convertString + "\"";

    //Pedido POST para a eliminação da classe indicada.
    var update = requestUpdate(obj, url_deleteComment);

    if (update != 1)
    {
        alert("Erro: Eliminacao do comentário sem sucesso...");
    }
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
        cleanDIV(divContent);
        consultClass(selectedClass);
    }
    else if (chamada == "4")
    {
        deleteMember(label, selectedClass);
        //Atualização da div de conteúdo.
        cleanDIV(divContent);
        consultClass(selectedClass);
    }
    else if (chamada == "5")
    {
        //Atualização da div de conteúdo.
        cleanDIV(divContent);
        consultProperty(label);
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
                    minW: 400,
                    minH: 400
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
                        else if (chamada == 2)
                        {
                            consultMember(classParent);
                        }
                        else
                        {
                            consultProperty(classParent);
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
                    minH: 500
                }
    });
    $.nmManual(url);

    return false;
}

function testes()
{
    alert("Info: Ainda em fase de testes...");
}
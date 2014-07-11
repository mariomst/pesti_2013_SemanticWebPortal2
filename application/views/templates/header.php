<?php
header('Content-Type: text/html; charset=utf-8');
?>

<html>
    <head>
        <link href="/assets/images/rdf.ico" rel="shortcut icon" type="image/x-icon" />
        <title>
            <?php echo $title ?>
        </title>
    </head>
    <body>       
        <!-- Funções JavaScript -->
        <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
        <script type="text/javascript" src="/assets/js/JSfunctions.js"></script>

        <!-- Inclusão do nyroModal para criação das janelas modais -->
        <script type="text/javascript" src="/assets/plugins/nyroModal/js/jquery.nyroModal.custom.js"></script>

        <!-- Cascading Style Sheets -->
        <link rel="stylesheet" href="/assets/plugins/nyroModal/styles/nyroModal.css" type="text/css" media="screen"/>
        <link rel="stylesheet" href="/assets/css/stylesheet.css" type="text/css" media="screen"/>

        <div id="header" class="header">
            <h1> Semantic Web Portal (alpha version)</h1>
            <font id="session" class="session"></font>
        </div>

        <div id="header_menu" class="header_menu">
            <a href="/index.php/home" class="link_menu">Home</a>&nbsp;
            <a id="user_session" class="link_menu"></a>
            <a id="user_register" href="/index.php/register" class="link_menu" onclick="createModelessWindow('/index.php/register');return false;">Registar</a>&nbsp;
            <a href="/index.php/about" class="link_menu">About</a>&nbsp;
            <a id="adminPage" href="/index.php/admin" class="link_menu">Administração</a>&nbsp;
        </div>

        <script type="text/javascript">
            $(function()
            {
                $('.nyroModal').nyroModal();
            });

            $(window).ready(function()
            {
                var user = getUserName(document.cookie);

                if (user == null)
                {
                    //Criação do cookie que armazena o utilizador.
                    document.cookie = "user=;";
                    document.cookie = "level=;";
                }                            

                var user = getUserName(document.cookie);

                if (user == "")
                {
                    $("#session").append("A visualizar página como utilizador anónimo.");
                    $("#user_session").attr("href", "/index.php/login");
                    $("#user_session").attr("onclick", "createModelessWindow('/index.php/login');return false;");
                    $("#user_session").append("Login");
                    $("#user_register").show();
                    $("#adminPage").hide();
                }
                else
                {
                    $("#session").append("Utilizador: " + user);
                    $("#user_session").attr("href", "/index.php/login");
                    $("#user_session").attr("onclick", "logout();return false;");
                    $("#user_session").append("Logout");
                    $("#user_register").hide();
                    $("#adminPage").hide();
                }
                
                var userLevel = checkUserLevel(user);
                
                if(userLevel == "0")
                {
                    $("#adminPage").show();
                }
            });
        </script>
<html>
    <head>
        <title>
            <?php echo $title ?>
        </title>
        <!-- Carregamento dos ficheiros JavaScript. -->
        <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
        <script type="text/javascript" src="/assets/js/JSfunctions.js"></script>
        <!-- Carregamento dos ficheiros CSS. -->
        <link rel="stylesheet" href="/assets/css/stylesheet.css" type="text/css" media="screen"/>
    </head>
    <body>
        <h3>&#8226; Login</h3>

        <form align="center" class="forms">
            <b>Username:</b>
            <input type="text" id="username" class="textbox" style="width: 280px">                      
            <br>
            <b>Password:</b>
            <input type="password" id="password" class="passwordbox" style="width: 280px">
            <br>
            <span id="errorMessage1" style="display: none;"><font color="red">Erro: O campo Username está vazio!</font><br></span>
            <span id="errorMessage2" style="display: none;"><font color="red">Erro: O campo Password está vazio!</font><br></span>
            <span id="errorMessage3" style="display: none;"><font color="red">Erro: Verifique se o username e password estão correctos.</font><br></span>  
            <button id="Entrar">Entrar</button>
        </form>

        <!-- Parece que o nyroModal não gosta muito de ficheiros JS externos, os scripts tem que ser todos declarados na própria página -->
        <script type="text/javascript">
            $(document).ready(function()
            {
                var button = document.getElementById("Entrar");
                var address = window.location.hostname;

                button.onclick = function()
                {
                    var username = document.getElementById("username").value;
                    var password = document.getElementById("password").value;

                    if (username == "")
                    {
                        $('.textbox').addClass('error_textbox');
                        $("#errorMessage1").show();

                    }
                    else
                    {
                        $('.error_textbox').removeClass('error_textbox');
                        $("#errorMessage1").hide();
                    }
                    if (password == "")
                    {
                        $('.passwordbox').addClass('error_textbox');
                        $("#errorMessage2").show();
                    }
                    else
                    {
                        $('.error_textbox').removeClass('error_textbox');
                        $("#errorMessage2").hide();
                    }

                    if (username != "" && password != "")
                    {
                        var result = checkUser(username, password);

                        if (result == "1")
                        {
                            var userLevel = checkUserLevel(username);
                            $("#errorMessage3").hide();
                            document.cookie = "user=" + username + "; ";
                            document.cookie = "level=" + userLevel + "; ";
                            document.cookie = "domain=" + address + "; ";
                            document.cookie = "path=/index.php/; ";
                            $.nmTop().close();
                        }
                        else
                        {
                            $("#errorMessage3").show();
                        }
                    }

                    return false;
                }
            });
        </script>

    </body>
    <html>
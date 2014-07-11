<html>
    <head>
        <title>
            <?php echo $title ?>
        </title>
    </head>
    <body>
        <!-- Funções JavaScript -->
        <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
        <script type="text/javascript" src="/assets/js/JSfunctions.js"></script>

        <!-- Cascading Style Sheets -->
        <link rel="stylesheet" href="/assets/css/stylesheet.css" type="text/css" media="screen"/>

        <h3>&#8226; Registo de novo utilizador</h3>

        <form align="center" class="forms">            
            <b>Username:</b>
            <input type="text" id="username" class="usernamebox" style="width: 280px"/>
            <br>
            <b>Password:</b>
            <input type="password" id="password" class="passwordbox" style="width: 280px"/>
            <br>
            <b>Confirma a Password:</b>
            <input type="password" id="checkpswd" class="checkpass" style="width: 280px"/>
            <br>
            <span id="errorMessage1" style="display: none;"><font color="red">Erro: O campo Username está vazio!</font><br></span>
            <span id="errorMessage2" style="display: none;"><font color="red">Erro: O campo Password está vazio!</font><br></span>
            <span id="errorMessage3" style="display: none;"><font color="red">Erro: As passwords não são iguais!</font><br></span>
            <span id="errorMessage4" style="display: none;"><font color="red">Erro: Username já existe!</font><br></span>
            <button id="Inserir">Inserir</button>
        </form>

        <!-- Parece que o nyroModal não gosta muito de ficheiros JS externos, os scripts tem que ser todos declarados na própria página -->
        <script type="text/javascript">
            $(document).ready(function()
            {
                var button = document.getElementById("Inserir");
                var check = document.getElementById("checkpswd");

                check.onblur = function()
                {
                    var password = document.getElementById("password").value;
                    var checkpswd = check.value;

                    if (password != checkpswd)
                    {
                        $('.passwordbox').addClass('error_textbox');
                        $('.checkpass').addClass('error_textbox');
                        $("#errorMessage3").show();
                    }
                    else
                    {
                        $('.passwordbox').removeClass('error_textbox');
                        $('.checkpass').removeClass('error_textbox');
                        $("#errorMessage3").hide();
                    }
                };

                button.onclick = function()
                {
                    var username = document.getElementById("username").value;
                    var password = document.getElementById("password").value;
                    var checkpswd = check.value;

                    if (username == "")
                    {
                        $('.usernamebox').addClass('error_textbox');
                        $("#errorMessage1").show();
                    }
                    else
                    {
                        //Remover caso exista, espaços no nome do user.
                        username = username.replace(/\s/g, "");

                        var checkUser = checkUserExists(username);
                        if (checkUser == 1)
                        {
                            $("#errorMessage1").hide();
                            $('.usernamebox').addClass('error_textbox');
                            $("#errorMessage4").show();
                        }
                        else
                        {
                            $('.error_textbox').removeClass('error_textbox');
                            $("#errorMessage1").hide();
                            $("#errorMessage4").hide();
                        }
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
                    
                    if (password != checkpswd)
                    {
                        $('.passwordbox').addClass('error_textbox');
                        $('.checkpass').addClass('error_textbox');
                        $("#errorMessage3").show();
                    }
                    else
                    {
                        $('.passwordbox').removeClass('error_textbox');
                        $('.checkpass').removeClass('error_textbox');
                        $("#errorMessage3").hide();
                    }

                    if (username != "" && password != "" && password == checkpswd)
                    {
                        //Remover caso exista, espaços no nome do user.
                        username = username.replace(/\s/g, "");

                        var checkUser = checkUserExists(username);
                        if (checkUser == 1)
                        {
                            $("#errorMessage1").hide();
                            $('.usernamebox').addClass('error_textbox');
                            $("#errorMessage4").show();
                        }
                        else
                        {
                            $('.error_textbox').removeClass('error_textbox');
                            $("#errorMessage1").hide();
                            $("#errorMessage4").hide();

                            var update = insertNewUser(username, password);

                            if (update == 1)
                            {
                                alert("Info: Registo de novo utilizador com sucesso!");
                                var userLevel = checkUserLevel(username);
                                document.cookie = "user=" + username + ";";
                                document.cookie = "level=" + userLevel + ";";
                                $.nmTop().close();
                            }
                            else
                            {
                                alert("Erro: Registo de novo utilizador sem sucesso!");
                            }
                        }
                    }

                    return false;
                };
            });
        </script>

    </body>
    <html>
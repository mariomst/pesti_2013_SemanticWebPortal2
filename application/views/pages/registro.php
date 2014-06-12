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
            <b>Nome:</b>
            <input type="text" id="nome" class="nomebox" style="width: 280px"/>
            <br>
            <b>E-Mail:</b>
            <input type="text" id="email" class="emailbox" style="width: 280px"/>
            <br>
            <b>Username:</b>
            <input type="text" id="username" class="usernamebox" style="width: 280px"/>
			<br>
            <b>Password:</b>
            <input type="password" id="password" class="passwordbox" style="width: 280px"/>
			<br>
            <b>Confirma a Password:</b>
            <input type="password" id="checkpswd" class="checkpass" style="width: 280px"/>
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

                if(password != checkpswd)
                {
                    $('.passwordbox').addClass('error_textbox');
                    $('.checkpass').addClass('error_textbox');
                }
                else
                {
                    $('.passwordbox').removeClass('error_textbox');
                    $('.checkpass').removeClass('error_textbox');
                }
            }

            button.onclick = function()
            {
                var name = document.getElementById("nome").value;
                var email = document.getElementById("email").value;
                var username = document.getElementById("username").value;
                var password = document.getElementById("password").value;
                var checkpswd = check.value;

                if(name == "")
                {
                    $('.nomebox').addClass('error_textbox');
                }
                else
                {
                    $('.error_textbox').removeClass('error_textbox');  
                }    
                if(email == "")
                {
                    $('.emailbox').addClass('error_textbox');
                }
                else
                {
                    $('.error_textbox').removeClass('error_textbox');  
                }    
                if(username == "")
                {
                    $('.usernamebox').addClass('error_textbox');
                }
                else
                {
                    $('.error_textbox').removeClass('error_textbox');  
                }    
                if(password == "")
                {
                    $('.passwordbox').addClass('error_textbox');                  
                }
                else
                {
                    $('.error_textbox').removeClass('error_textbox');  
                }       

                alert("Funcionalidade ainda não implementada...");     
            
                return false;
            }
        });
    </script>

    </body>
<html>
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

        <h3>&#8226; Login</h3>

        <form align="center" class="forms">
            <b>Username:</b>
            <input type="text" id="username" class="textbox" style="width: 280px">
			<br>
            <b>Password:</b>
            <input type="password" id="password" class="passwordbox" style="width: 280px">
			<br>
            <button id="Entrar">Entrar</button>
        </form>

    <!-- Parece que o nyroModal não gosta muito de ficheiros JS externos, os scripts tem que ser todos declarados na própria página -->
    <script type="text/javascript">
        $(document).ready(function()
        {	
            var button = document.getElementById("Entrar");

            button.onclick = function()
            {
                var username = document.getElementById("username").value;
                var password = document.getElementById("password").value;

                if(username == "")
                {
                    $('.textbox').addClass('error_textbox');
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
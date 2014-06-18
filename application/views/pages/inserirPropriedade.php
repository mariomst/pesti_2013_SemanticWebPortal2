<!-- Página de inserção de uma propriedade já existente 
     Versão 1.0     
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
            &#8226; Inser&ccedil;&atilde;o de uma propriedade no(a)
            <?php echo htmlspecialchars($_GET["type"]);?>:
            <?php echo htmlspecialchars($_GET["class"]);?> 
        </h2>
        
        <div align="center">
            <form align="center" class="forms">                
                <b>Escolha a(s) propriedade(s) que deseja adicionar: </b>
                <br>
                <br>
            </form>            
        </div>
    </body>    
</html>
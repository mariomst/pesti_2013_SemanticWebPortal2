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

    <!-- Inclusão do nyroModal para criação das janelas modais -->
	<script type="text/javascript" src="/assets/plugins/nyroModal/js/jquery.nyroModal.custom.js"></script>

    <!-- Cascading Style Sheets -->
    <link rel="stylesheet" href="/assets/plugins/nyroModal/styles/nyroModal.css" type="text/css" media="screen"/>
    <link rel="stylesheet" href="/assets/css/stylesheet.css" type="text/css" media="screen"/>

	<div id="header" class="header">
        <h1> Semantic Web Portal (alpha version)</h1>
        <br>
    </div>

    <div id="header_menu" class="header_menu">
        <a href="/index.php/home" class="link_menu">Home</a>&nbsp;
        <a href="/index.php/login" class="link_menu nyroModal">Login</a>&nbsp;
        <a href="/index.php/register" class="link_menu nyroModal">Registar</a>&nbsp;
        <a href="/index.php/about" class="link_menu">About</a>
    </div>

    <script type="text/javascript">
        $(function()
        {
            $('.nyroModal').nyroModal();
        });
    </script>
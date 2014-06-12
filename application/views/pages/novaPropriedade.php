<!-- Página de Inserção de uma nova propriedade -->
<!-- Versão 1.0									-->

<html>
    <head>
        <title>
		    <?php echo $title ?>
	    </title>
    </head>    
	<body>
		<!-- Funções JavaScript -->
        <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
		
		<!-- Cascading Style Sheets -->
		<link rel="stylesheet" href="/assets/css/stylesheet.css" type="text/css" media="screen"/>
		
		<h2 align="center">&#8226; Inser&ccedil;&atilde;o de uma nova propriedade</h2>
		
		<div align = "center">
			<form align = "center" class="forms">
				<b>Insira o nome da propriedade: </b>
				<input type="text" id="propName" style="width: 280px">
				<br>
				<br>
				<b>Escolha o tipo de propriedade: </b>
				<br>
				<br>
				<input type="radio" name="propType" value="DatatypeProperty">DatatypeProperty</input>
					<div align="right" class="propTypeDiv" id="DTP_div" style="display: none;">
						<!-- Vai ser tratado pelo JavaScript -->						
					</div>
				<br>
				<input type="radio" name="propType" value="ObjectProperty">ObjectProperty</input>
					<div align="left" class="propTypeDiv" id="OP_div" style="display: none;">
						<input type="checkbox" class="property" value="FunctionalProperty">FunctionalProperty</input></br>
						<input type="checkbox" class="property" value="InverseFunctionalProperty">InverseFunctionalProperty</input></br>
						<input type="checkbox" class="property" value="TransitiveProperty">TransitiveProperty</input></br>
						<input type="checkbox" class="property" value="SymmetricProperty">SymmetricProperty</input></br>
						<input type="checkbox" class="property" value="AsymmetricProperty">AsymmetricProperty</input></br>
						<input type="checkbox" class="property" value="ReflexiveProperty">ReflexiveProperty</input></br>
						<input type="checkbox" class="property" value="IrreflexiveProperty">IrreflexiveProperty</input></br>
						<input type="checkbox" class="property" value="InverseOf">InverseOf</input></br>
						<input type="checkbox" class="property" value="EquivalentProperty">EquivalentProperty</input></br>
						<input type="checkbox" class="property" value="Range">Range</input></br>
						<input type="checkbox" class="property" value="SubPropertyOf">SubPropertyOf</input>
					</div>
				<br>
				<br>
				<button id="goBack">&#8592; Voltar</button>
				<button id="insertNewProp">Inserir</button>
			</form>
		</div>
		
		<!-- Scripts JavaScript -->
		<script type="text/javascript">
			var backButton = document.getElementById("goBack");
			var insertButton = document.getElementById("insertNewProp");			
		
			var $_GET = <?php echo json_encode($_GET); ?>;			//Permitir ao JavaScript buscar variaveis do PHP.
			var type = $_GET["type"];								//Atribui o type que foi recebido pelo PHP (Se membro ou Classe).

			if(type == "membro")
			{
				$('#DTP_div').append("Especifico: <input type=\"text\" id=\"DTP_esp\" style=\"width: 200px\">");
			}
			else if(type == "classe")
			{
				$('#DTP_div').append("Minimo: <input type=\"text\" id=\"dtpv_min\" style=\"width: 200px\">&nbsp;</br>");
				$('#DTP_div').append("Maximo: <input type=\"text\" id=\"dtpv_max\" style=\"width: 200px\">&nbsp;</br>");
				$('#DTP_div').append("Especifico: <input type=\"text\" id=\"DTP_esp\" style=\"width: 200px\">&nbsp;");
			}
		
		    $('input[type="radio"]').click(function()
			{
				if($(this).attr("value")=="DatatypeProperty")
				{
					$("#DTP_div").show();
					$("#OP_div").hide();
				}
				if($(this).attr("value")=="ObjectProperty")
				{
					$("#DTP_div").hide();
					$("#OP_div").show();
				}
			});
			
			insertButton.onclick = function()
			{
			    alert("INFO: Ainda nao implementado...");
			    $.nmTop().close();
			    return false;
			}
			
			backButton.onclick = function()
			{
			    $.nmTop().close();
			    return false;
			};
		</script>
	</body>
</html>
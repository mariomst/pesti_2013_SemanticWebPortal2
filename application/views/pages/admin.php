<div id="admin" class="admin">
    <h3 align="center">Informações sobre os servidores Fuseki:</h3>
    
    <table border="1" align="center" id="fusekiServers">
        <tr>
            <th width="55%">Endereço:</th>
            <th width="30%">Dataset:</th>
            <th width="15%">Estado:</th>
        </tr>        
    </table>

    <h3 align="center">Nrº de utilizadores registados:</h3> 
    <p align="center" id="nrElements"></p>    
    
    <h3 align="center">Utilizadores registados no sistema:</h3>

    <div id="usersTable" align="center">

    </div>           
</div>

<script>
    $(window).ready(function()
    {
        var accessLevel = getUserLevel(document.cookie);
        var object = XMLHttpObject();        
        
        if(accessLevel == '' || accessLevel == 1)
        {          
            var h3_1 = document.createElement('h3');
            var h3_2 = document.createElement('h3');
            var address = "http://" + window.location.hostname + "/index.php/home";
            
            document.getElementById('admin').innerHTML = '';
            
            h3_1.setAttribute('align', 'center');
            h3_1.setAttribute('style', 'color:red');
            h3_1.innerHTML = "Não tem permissões para visualizar esta página!";
            document.getElementById('admin').appendChild(h3_1);        
            
            h3_2.setAttribute('align', 'center');
            h3_2.innerHTML = "Vai ser redireccionado para a página principal dentro de 5 segundos.";
            document.getElementById('admin').appendChild(h3_2);      
            
            setTimeout("location.href = '" + address + "';", 5000);
        }
        else
        {
            var url_fuseki_1 = '/index.php/getFusekiTDB';
            var status_fuseki_1 = '/index.php/checkFusekiTDB';
            var result_1 = requestInformation(object, url_fuseki_1);
            var result_status_1 = requestInformation(object, status_fuseki_1); 
            splitFusekiURL(result_1, result_status_1);
            
            var url_fuseki_2 = '/index.php/getFusekiUserTDB';
            var status_fuseki_2 = '/index.php/checkFusekiUserTDB';
            var result_2 = requestInformation(object, url_fuseki_2);
            var result_status_2 = requestInformation(object, status_fuseki_2);
            splitFusekiURL(result_2, result_status_2);
            
            consultUsers('#usersTable');            
            var nrElements = ($('#usersTable tr').length) - 1;
            $('#nrElements').append(nrElements);
        }
    });
</script>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>Gestion des collaborateurs</title>
<meta name="keywords" content="" lang="fr">
<meta name="description" content="">

<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!--[if IE]><script language="javascript" type="text/javascript" src="http://www.wuro.fr/static/js/excanvas.js"></script><![endif]-->	

<link rel="stylesheet" type="text/css" href="index.css" media="all">
</head>
<body>

<?php
include ("./menu.php");
?>

<div id="contenant">
		

<div class="box">
	<h2>Gestion des collaborateurs</h2>
		
			
		
	<!--###############################################################
	############################# MENU ################################
	###############################################################-->
 
	<ul id="menu">
	        <li>
		<a class="active" href="" rel="user_list"><img src="../images/user.png" alt="Liste des utilisateurs"> <span>Liste</span></a>
        </li>
    					
		<li>
					<a href="ajouter_-_supprimer_-_modifier_un_collaborateur.html?action=user_add" rel="user_add"><img src="../images/user_add.png" alt="Ajouter un utilisateur"> <span>Ajouter</span></a>
				</li>
    
        </ul>
	
	<!--###############################################################
	############### AFFICHAGE DE MESSAGES DIVERS... ###################
	###############################################################-->
	
		<div id="messages" class="">
					</div>

	<!--###############################################################
	####################### LISTING DES USERS #########################
	###############################################################-->
    
    
		
	<div id="user_list" class="content">
	
	
			
				
        <table cellspacing="0">
            <tbody><tr>
                <th colspan="2" class="aleft">Collaborateur</th>
               
                <th class="aleft">Type de salarié</th>
                <th class="aleft" width="200">Salaire & extras</th>
                <th colspan="2">&nbsp;</th>
            </tr>
            
            <tr id="lig1782" class="alternate">
                                <td width="64"><img src="../images/user.png" alt="cscpl" height="64" width="64"></td>
                                <td class="vtop" width="220"><strong>CSCPL </strong></td>
								<td class="vtop">Administrateur</td>
                                <td class="vtop"></td>
                                <td class="vtop" width="32"></td>
                                <td class="vtop" width="32"><a href="ajouter_-_supprimer_-_modifier_un_collaborateur.html?action=user_modify" title="Modifier"><img src="../images/pencil.png" alt="Modifier"></a></td>
                                </tr>
                                        
        </tbody></table>
        

        
	</div>
	
	

	
</div></div>

<?php
include('./footer.php');
?>

</body>
</html>

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

<link rel="stylesheet" type="text/css" href="../css/ajouter_-_supprimer_-_modifier_un_collaborateur.css" media="all">
<script language="JavaScript" type="text/javascript" SRC="../scripts/div.js"></script>
</head>
<body> 

<?php
include ("./menu.php");
$action = $_GET['action'];
                    
if ($action == "user_add") {
	$txt_bouton = 'Ajouter';

						}
elseif ($action =="user_modify") {
	$txt_bouton = 'Modifier';
	
}
					
					?>



<div id="contenant">
		

<div class="box">
	<h2>Gestion des collaborateurs</h2>
		
			
		
	<!--###############################################################
	############################# MENU ################################
	###############################################################-->
 
	<ul id="menu">
	        <li>
		<a href="../rh-outils/gestion_des_collaborateurs.html" rel="user_list"><img src="../images/user.png" alt="Liste des utilisateurs" /> <span>Liste</span></a>
        </li>
    					
		<li>
					<a href="" rel="user_add" class="active"><img src="../images/user_add.png" alt="Modifier un utilisateur" /><span><?php echo $txt_bouton; ?></span></a>
				</li>
    					
		
    
        </ul>
	
	<!--###############################################################
	############### AFFICHAGE DE MESSAGES DIVERS... ###################
	###############################################################-->
	
		<div id="messages" class="">
					</div>
	

	<!--###############################################################
	####################### AJOUTER UN USER ##########################
	###############################################################-->
		<div style="display: block;" id="user_add" class=" content">
		 		       		       
           <form action="" method="post" id="ajout_user" enctype="multipart/form-data"> 
                          
            <h3 class="margin-bottom margin-top">Coordonnées</h3>
           
            <fieldset>	
                    <p class="gris01">
                    	<span class="toolTip" title="Entrez ici le nom et le prénom de votre nouveau collaborateur">&nbsp;</span>
                        <label for="civilite">Civilité : </label>
                        <select name="civilite" id="civilite">
                            <option value="Mlle">Mlle</option>
                            <option style="background-color: rgb(227, 227, 227);" value="Mme">Mme</option>
                            <option value="M" selected="selected">M</option>
                        </select>
                        
                        <input name="nom" id="nom" value="" placeholder="Nom" title="Nom" class="textfield" type="text">
                        
                        <input name="prenom" id="prenom" value="" placeholder="Prénom" title="Prénom" class="textfield" type="text">
                    </p>
                    
		 			
		 			<p>   
                 
                    	<span class="toolTip" title="Un utilisateur peut faire partie d'un groupe d'utilisateurs pour lequel vous pourrez attribuer ou limiter les droits sur l'espace de gestion (Ex : restreindre l'accès au module Factures) <br />
Vous avez la possibilité de créer des groupes dans Groupes - Ajouter un groupe">&nbsp;</span>
                        <label for="mail">Type de salarié : </label>
                        <select name="groupe" id="groupe">
                            <option value="">-</option>
                            <option style="background-color: rgb(227, 227, 227);" value="administrateur" selected="selected">Administrateur</option><option value="employe">Employé</option><option value="ouvrier">Ouvrier</option><option value="apprenti">Apprenti</option><option style="background-color: rgb(227, 227, 227);" value="na">N/A</option>                        </select>
                    </p>
            </fieldset>
            
            <h3 class="margin-bottom">Contrat dans l'entreprise</h3>
            <fieldset>	
                    
                    
                    <p class="gris01" style="padding-left: 30px;">
                        <label for="solde3083">Salaire brut :</label><input name="salaire" id="salaire" value="" class="textfield margin-right " type="text"> €                      
                    </p>
                    <p style="padding-left: 30px;">
                        <label for="contrat">Autres frais brut : </label>
                        <input name="extra" id="extra" value="" class="textfield margin-right " type="text"> €
                    </p>
            </fieldset>
            
                        
            
            
                        
                   

					
            <p class="acenter gris01">
            <?php
			if ($action == "user_modify"){ ?>
                <a class="button submit"><span><img src="../images/accept.png" alt="Modifier le collaborateur">Modifier le collaborateur</span></a>
				<a class="margin-left button delete_collaborateur" rel="1782"><span><img src="../images/delete_001.png" alt="Supprimer l\'utilisateur'">Supprimer le collaborateur</span></a>
                <?php }
			elseif ($action == "user_add"){ ?>
                
                <a class="button submit"><span><img src="../images/accept.png" alt="Ajouter le collaborateur">Ajouter le collaborateur</span></a>
                <?php } ?>
                
                    			<input name="u_id" value="1782" type="hidden">                
            </p>
        </form>	</div>
	




	
	
		
</div></div>

</body>
</html>

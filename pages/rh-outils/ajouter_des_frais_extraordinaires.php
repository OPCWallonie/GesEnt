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
                    
if ($action == "extra_add") {
	$txt_bouton = 'Ajouter';	
						}
elseif ($action =="extra_modify") {
	$txt_bouton = 'Modifier';
	
}
else {
	$action = "liste";
	$txt_bouton = 'Ajouter';
	
	}
?>

<div id="contenant">
		

<div class="box">
	<h2>Frais extraordinaires</h2>
		
			
		
	<!--###############################################################
	############################# MENU ################################
	###############################################################-->
 
	<ul id="menu">
	        <li>
		<a <?php if ($action=="liste") { ?> class="active"<?php } ?> href="../rh-outils/frais_extraordinaires.html?action=liste" rel="user_list"><img src="../images/clipboard_64.png" alt="Liste des utilisateurs"> <span>Liste</span></a>
        </li>
    					
		<li>
					<a <?php if ($action=="extra_add" or $action=="extra_modify") { ?> class="active"<?php } ?> href="" rel="user_add"><img src="../images/clipboard_add.png" alt="Ajouter un utilisateur"> <span><?php echo $txt_bouton; ?></span></a>
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
    
    <div id="extra_edit" class="content">
    
    <form action="" method="post" id="ajout_user" enctype="multipart/form-data"> 
                          
            <h3 class="margin-bottom margin-top">Informations</h3>
           
            <fieldset>	
    
    			<div class="gris01">
					<label for="detail">Détail : </label>
					<textarea name="detail" id="detail" rows="10" cols="75"></textarea>
					
					
				</div>
                
                
                <p>
                        <label for="montantHT">Montant HT :</label><input name="montantHT" id="montantHT" value="" class="textfield margin-right " type="text"> €                      
                    </p>
                    
                <p class="gris01">
                        <label for="montantTVAC">Montant TVAC :</label><input name="montantTVAC" id="montantTVAC" value="" class="textfield margin-right " type="text"> €                      
                    </p>
                    
                    
                    
             </fieldset>
             
             
             <p class="acenter gris01">
            <?php
			if ($action == "extra_modify"){ ?>
                <a class="button submit"><span><img src="../images/accept.png" alt="Modifier la note de frais">Modifier la note de frais</span></a>
				<a class="margin-left button delete_collaborateur" rel="1782"><span><img src="../images/delete_001.png" alt="Supprimer la note de frais">Supprimer la note de frais</span></a>
                <?php }
			elseif ($action == "extra_add"){ ?>
                
                <a class="button submit"><span><img src="../images/accept.png" alt="Ajouter la note de frais">Ajouter la note de frais</span></a>
                <?php } ?>
                
                    			<input name="u_id" value="1782" type="hidden">                
            </p>
             
             
             
    </form>
    
    
    </div>
    
    
    
	
	

	
</div></div>

<?php
include('./footer.php');
?>

</body>
</html>

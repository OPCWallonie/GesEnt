<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>
<link rel="stylesheet" type="text/css" href="../index.css" media="all">
</head>
<body>

<?php 
	// Vérification si code erreur
	switch ($_GET['code']) {
		case 'mpvide' : 		$txt_code = 'Veuillez indiquer votre mot de passe !'; 			$txt_color = 'fail'; break;
		case 'loginvide' : 		$txt_code = 'Veuillez indiquer votre mot nom d\'utilisateur !'; $txt_color = 'fail'; break;
		case 'loginmpvide' : 	$txt_code = 'Veuillez indiquer vos identifiants !'; 			$txt_color = 'fail'; break;
		case 'unknownlogin' : 	$txt_code = 'Nom d\'utilisateur ou mot de passe inconnu !'; 	$txt_color = 'fail'; break;
		case 'success' : 		$txt_code = 'Vous &ecirc;tes connect&eacute; !'; 				$txt_color = 'pass'; break;
		case 'deconnected' : 	$txt_code = 'Vous &ecirc;tes d&eacute;connect&eacute; !'; 		$txt_color = 'pass'; break;
	}
		
	if (!empty($_SESSION['ent_nom'])) {
		// C'est ok :)
	}
	else {
		
		require_once('pages/login/connect.php');
		exit();
		
	}
	?>





<div id="header">

    <div id="header-content">
    	<div style="position:absolute">
    	<img src="../images/logo_site.png" />
    	</div>	       
        <ul id="tools">      
	        <li><a href="../outils/export.html" class="icon-export small">Export</a></li>
            <li><a href="../outils/statistiques.html" class="icon-stats small">Statistiques</a></li>
            <li><a href="../outils/aide.html" class="icon-aide small">Aide</a></li>
            <li><a href="../outils/chantiers.html" class="icon-chantier small">Mes chantiers</a></li>        
         </ul>
         
        <div id="infos_user">  
<?php
echo '             
			<h1>'.$_SESSION['ent_nom'].'</h1>
			<br />
            <p>'.$_SESSION['ent_adresse'].'</p>
			<p>'.$_SESSION['ent_cp'].' '.$_SESSION['ent_localite'].'</p>
<br />			'; 
?>			
			<a href="../include/deconnexion.inc.php" id="deconnexion" class="small" title="Se déconnecter">déconnexion</a>
	        
            
		</div>
			<div id="notifications" class="hide">
				<img src="./images/delete.png" alt="Fermer" class="fermer_notifications">
				<h4>Liste des notifications</h4>
				
				<p class="acenter">Aucune notification récente</p>	
				
				<p><a href="" class="underline">Voir toutes les notifications</a></p>
			</div>
			

		
    
    </div>

</div>

</body>
</html>
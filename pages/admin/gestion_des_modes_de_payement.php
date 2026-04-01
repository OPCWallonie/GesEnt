<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>Gestion des modes de paiement</title>
<meta name="keywords" content="" lang="fr">
<meta name="description" content="">

<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!--[if IE]><script language="javascript" type="text/javascript" src="http://www.wuro.fr/static/js/excanvas.js"></script><![endif]-->	

<link rel="stylesheet" type="text/css" href="../css/modes_payement.css" media="all">
<?php

// fonction d'unicité de la valeur défaut... NE FONCTIONNE PAS !!!!!

function unicite($id, $defaut)
{
	if(!isset($id)){$id='99999999';}
$sql="SELECT id, defaut FROM ".$tab_modepayement." WHERE defaut = '".$defaut."' AND id != '".$id. "'";
$result = mysql_query($sql);

while($data = mysql_fetch_assoc($result))
							{
								echo $data['id'];
								$sql = "UPDATE " .$tab_modepayement. " SET defaut='0' WHERE id='".$data['id']."'";
mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error()); 
								
							}

}
//Fin de fonction

$action = $_GET['action']; 



if ($action == "modifier"){
	$ajouter_payement = "none";
	$modifier_payement = "";	
}
elseif ($action == "effectuer_ajouter") {
	
	unicite($_POST['id'], $_POST['mode_paiement_defaut']);
	
$sql = "INSERT INTO " .$tab_modepayement. "(id, nom, defaut, utilise) VALUES('','".$_POST['mode_paiement_nom']."','".$_POST['mode_paiement_defaut']."','".$_POST['mode_utilise']."')";
mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());


$ajouter_payement = "";
$modifier_payement = "none";


}

elseif ($action == "effectuer_supprimer") {
	
	$sql = "DELETE FROM " .$tab_modepayement. " WHERE id = '".$_GET['id']."'";
mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error()); 

	
 


$ajouter_payement = "";
$modifier_payement = "none";


}

elseif ($action == "effectuer_modifier") {
	
	$sql = "UPDATE " .$tab_modepayement. " SET nom='".$_POST['mode_paiement_nom']."', defaut='".$_POST['mode_paiement_defaut']."', utilise='".$_POST['mode_utilise']."' WHERE id = '".$_POST['id']."'";
mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error()); 

	
 


$ajouter_payement = "";
$modifier_payement = "none";


}

else {
	
	$ajouter_payement = "";
	$modifier_payement = "none";
	
}

?>

<style type="text/css">
#ajouter_payement{display:<?php echo $ajouter_payement;?>;}
#modifier_payement{display:<?php echo $modifier_payement;?>;}
#bouton_ajouter{display:<?php echo $modifier_payement;?>;}
</style>

</head>
<body>

<?php
include ("./menu.php");

					?>

<div id="contenant">
		

<div class="box">


	<h2>Gestion des modes de paiement</h2>
	
	<div class="col_left" id="liste_paiements">
					<h3>Modes de paiement</h3>
                    
<?php
// On récupère les données nécessaires à la suite du traitement
		$requete = 'SELECT id, nom, defaut, utilise FROM ' . $tab_modepayement ;
		$mysql_result = mysql_query($requete, $db);
?>                    
                   
                    
					<ul class="fieldlist">
							<?php
							$i = 0;
							while($data = mysql_fetch_assoc($mysql_result))
							{
                            echo '
                            <li class="';
								if ($data['defaut'] == '1' ) { echo 'gras'; }
							
							echo '">
							<span class="hide">
							<a href="?action=modifier" title="Modifier">
							<img src="../images/pencil.png" alt="Modifier">
							</a>
							</span>
							<a href="?action=modifier&id='.$data['id'].'" class="cat" title="">'.$data['nom'].'</a>
							</li>';
							// Pour ma variable de session mode_payement
							$mode_payement[$i] = array( 	id => $data['id'],
																nom => $data['nom'],
																defaut => $data['defaut']);
								$i++;
							}
// Et j'enregistre le tout dans ma variable de session
$_SESSION['mode_payement'] = $mode_payement;							
							?>
							</ul>
<div id="bouton_ajouter">                            
<a class="button" href="?action=ajouter"><span><img src="../images/add.png" alt="Ajouter">Ajouter un mode</span></a>
</div>                            
                            
                            
			</div>
            

<div id="ajouter_payement" class="col_right">		
		
		
		<h3>Ajouter un mode de paiement</h3>
		<form method="post" id="formulaire" enctype="multipart/form-data" action="?action=effectuer_ajouter">
			<input name="id" value="" type="hidden">
            <fieldset>
			<p class="gris01">
				<span class="toolTip" title="Indiquez ici le nom du moyen de paiement">&nbsp;</span>
				<label for="mode_paiement_nom">Mode de paiement :</label>
				<input class="textfield" name="mode_paiement_nom" id="mode_paiement_nom" value="" type="text">
			</p>
			
	
						
								
			<p>
				<span class="toolTip" title="Cochez cette case s'il s'agit du mode de paiement qui s'affichera par défaut lors de la création de vos factures">&nbsp;</span>
				<label for="mode_paiement_defaut">Mode par défaut :</label>
				<input name="mode_paiement_defaut" id="mode_paiement_defaut" class="checkbox" value="1" type="checkbox">&nbsp;
			</p>
					
			<p class="gris01">
				<span class="toolTip" title="Cochez cette case si vous voulez voir apparaître ce type de paiement dans le formulaire de création de factures">&nbsp;</span>
				<label for="mode_utilise">Utilisé :</label>
				<input name="mode_utilise" id="mode_utilise" class="checkbox" value="1" type="checkbox">&nbsp;
			</p>
            </fieldset>
            </form>
						
            <p>
                <a href="#" onclick='javascript:document.getElementById("formulaire").submit()' class="button submit"><span><img src="../images/accept.png" alt="Ajouter">Ajouter ce mode de paiement</span></a>
            	            </p>
		
		</div>
        


<div id="modifier_payement" class="col_right">

<?php 
$id = $_GET['id'];
// On récupère les données nécessaires à la suite du traitement
		$requete = 'SELECT id, nom, defaut, utilise FROM ' . $tab_modepayement . ' WHERE id = ' .$id;
		$mysql_result = mysql_query($requete, $db);
?>                    
                   
                    
					<ul class="fieldlist">
							<?php
							while($data = mysql_fetch_assoc($mysql_result)){
								$id = $data['id'];
								?>


<h3>Modifier un mode de paiement</h3>
		<form id="formulaire2" method="post" enctype="multipart/form-data" action="?action=effectuer_modifier">
			<input name="id" value="<?php echo $id; ?>" type="hidden">
            <fieldset>
			<p class="gris01">
				<span class="toolTip" title="Indiquez ici le nom du moyen de paiement">&nbsp;</span>
				<label for="mode_paiement_nom">Mode de paiement :</label>
				<input class="textfield" name="mode_paiement_nom" id="mode_paiement_nom" value="<?php echo $data['nom']; ?>" type="text">
			</p>
			
				
								
			<p class="gris01">
				<span class="toolTip" title="Cochez cette case s'il s'agit du mode de paiement qui s'affichera par défaut lors de la création de vos factures">&nbsp;</span>
				<label for="mode_paiement_defaut">Mode par défaut :</label>
				<input name="mode_paiement_defaut" id="mode_paiement_defaut" <?php if($data['defaut'] == '1') { echo 'checked="yes"'; } ?> class="checkbox" value="<?php echo $data['defaut'];?>" type="checkbox">&nbsp;
			</p>
					
			<p>
				<span class="toolTip" title="Cochez cette case si vous voulez voir apparaître ce type de paiement dans le formulaire de création de factures">&nbsp;</span>
				<label for="mode_utilise">Utilisé :</label>
				<input name="mode_utilise" id="mode_utilise" <?php if($data['utilise'] == '1') { echo 'checked="yes"'; } ?> class="checkbox" value="<?php echo $data['utilise'];?>" type="checkbox">&nbsp;
			</p>
            </fieldset>
            <?php
							}
							?>
						
            <p class="gris01">
                <a href="#"  onclick='javascript:document.getElementById("formulaire2").submit()' class="button submit"><span><img src="../images/accept.png" alt="Modifier">Modifier ce mode de paiement</span></a>
            	<a href="?action=effectuer_supprimer&id=<?php echo $id; ?>"  class="button sup_mode_paiement margin-left" rel="2596"><span><img src="../images/delete_001.png" alt="Supprimer"> Supprimer ce mode de paiement</span></a>&nbsp;            </p>
		</form>
        </ul>
		</div>


        
        
        
        
        
        
        
        
        <hr>
        
</div></div>

<?php
include('./footer.php');
?>

</body>
</html>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>Gestion des taux de tva</title>
<meta name="keywords" content="" lang="fr">
<meta name="description" content="">

<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!--[if IE]><script language="javascript" type="text/javascript" src="http://www.wuro.fr/static/js/excanvas.js"></script><![endif]-->	

<link rel="stylesheet" type="text/css" href="../css/taux_tva.css" media="all">

<?php
$action = $_GET['action']; 



if ($action == "modifier"){
	$ajouter_tva = "none";
	$modifier_tva = "";	

}
elseif ($action == "effectuer_ajouter") {
	
	
$sql = "INSERT INTO " .$tab_tauxtva. "(id, tva) VALUES('','".$_POST['taux_de_tva']."')";
mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());


$ajouter_tva = "";
$modifier_tva = "none";



}

elseif ($action == "effectuer_supprimer") {
	
	$sql = "DELETE FROM " .$tab_tauxtva. " WHERE id = '".$_GET['id']."'";
mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error()); 

	
 


$ajouter_tva = "";
$modifier_tva = "none";



}

elseif ($action == "effectuer_modifier") {
	
	$sql = "UPDATE " .$tab_tauxtva. " SET tva='".$_POST['taux_de_tva']."' WHERE id = '".$_POST['id']."'";
mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error()); 

	
 


$ajouter_tva = "";
$modifier_tva = "none";



}

else {
	
	$ajouter_tva = "";
	$modifier_tva = "none";
	
	
}

?>

<style type="text/css">
#ajouter_tva{display:<?php echo $ajouter_tva;?>;}
#modifier_tva{display:<?php echo $modifier_tva;?>;}
#bouton_ajouter{display:<?php echo $modifier_tva;?>;}
</style>



</head>
<body>

<?php
include ("./menu.php");
?>

<div id="contenant">
		

<div class="box">


	<h2>Gestion des taux de tva</h2>
	
	
	<div class="col_left" id="liste_paiements">
    
   <?php
// On récupère les données nécessaires à la suite du traitement
		$requete = 'SELECT id, tva FROM ' . $tab_tauxtva . ' ORDER BY tva ASC' ;
		$mysql_result = mysql_query($requete, $db);
?>   
   
					<h3>Taux de TVA</h3>
					<ul class="fieldlist">
                    <?php
$i = 0;
							while($data = mysql_fetch_assoc($mysql_result))
							{
                            echo '
					<li>
					<span>
							<a href="?action=modifier&id='.$data['id'].'" title="Modifier">
							<img src="../images/pencil.png" alt="Modifier">
					</a>
					</span>
					<a href="?action=modifier&id='.$data['id'].'" onclick="return false">'.$data['tva'].'%</a>
					</li>';
					
// Pour mettre à jour ma variable de session si il y a des modifications
$taux_tva[$i] = $data['tva'];
				$i++;					
							}
$_SESSION['taux_tva'] = $taux_tva;
							?>
					
                    </ul>
                    
                    <div id="bouton_ajouter">                            
<a class="button" href="?action=ajouter"><span><img src="../images/add.png" alt="Ajouter">Ajouter un taux</span></a>
</div>
                    
                    </div>	
                    
                    
                    
                    
                    
<div id="ajouter_tva" class="col_right">
          
                    
	<h3>Ajouter un taux de tva</h3>
	<form method="post" id="formulaire" enctype="multipart/form-data" action="?action=effectuer_ajouter">
		<p class="gris01">
			<label for="taux_de_tva">Taux de tva :</label>
			<input value="" name="taux_de_tva" id="taux_de_tva" class="textfield aright number" type="text">&nbsp;%
		</p>
        </form>
		<p class="acenter">
            <a href="#" onclick='javascript:document.getElementById("formulaire").submit()' class="button submit"><span><img src="../images/accept.png" alt="Ajouter">Ajouter ce taux de TVA</span></a>
		</p>
	
    </div>
    

<div id="modifier_tva" class="col_right">
          
          <?php 
$id = $_GET['id'];
// On récupère les données nécessaires à la suite du traitement
		$requete = 'SELECT id, tva FROM ' . $tab_tauxtva . ' WHERE id = ' .$id;
		$mysql_result = mysql_query($requete, $db);
		
							while($data = mysql_fetch_assoc($mysql_result)){
								$id = $data['id'];
								?>
                    
	<h3>Modifier ou Supprimer un taux de tva</h3>
	<form id="formulaire2" method="post" enctype="multipart/form-data" action="?action=effectuer_modifier">
    <input name="id" value="<?php echo $id; ?>" type="hidden">
		<p class="gris01">
			<label for="taux_de_tva">Taux de tva :</label>
			<input value="<?php echo $data['tva']; ?>" name="taux_de_tva" id="taux_de_tva" class="textfield aright number" type="text">&nbsp;%
		</p>
        
        </form>
        
        <p class="gris01">
                <a href="#"  onclick='javascript:document.getElementById("formulaire2").submit()' class="button submit"><span><img src="../images/accept.png" alt="Modifier">Modifier ce taux de TVA</span></a>
            	<a href="?action=effectuer_supprimer&id=<?php echo $id; ?>"  class="button sup_mode_paiement margin-left" rel="2596"><span><img src="../images/delete_001.png" alt="Supprimer"> Supprimer ce taux de TVA</span></a>&nbsp;            </p>
        
		
	
    <?php
							}
							?>                        
    </div>    
    
    
    
    
    
    <hr>
	
	
</div></div>

<?php
include('./footer.php');
?>

</body>
</html>

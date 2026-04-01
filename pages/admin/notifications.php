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
$date = date('Ymd'); 



if ($action == "modifier"){
	$ajouter_tva = "none";
	$modifier_tva = "";	

}
elseif ($action == "effectuer_ajouter") {
	
	
$sql = "INSERT INTO " .$tab_notifications. "(id, titre, notification, date) VALUES('','".$_POST['titre']."','".$_POST['contenu']."','".$date."')";
mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());


$ajouter_tva = "";
$modifier_tva = "none";



}

elseif ($action == "effectuer_supprimer") {
	
	$sql = "DELETE FROM " .$tab_notifications. " WHERE id = '".$_GET['id']."'";
mysql_query($sql) or die('Erreur SQL !'.$sql.'<br />'.mysql_error()); 

	
 


$ajouter_tva = "";
$modifier_tva = "none";


}

elseif ($action == "effectuer_modifier") {
	
	$sql = "UPDATE " .$tab_notifications. " SET titre='".$_POST['titre']."', notification='".$_POST['contenu2']."', date='".$_POST['date']."' WHERE id = '".$_POST['id']."'";
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
#ajouter_notification{display:<?php echo $ajouter_tva;?>;}
#modifier_notification{display:<?php echo $modifier_tva;?>;}
#bouton_ajouter{display:<?php echo $modifier_tva;?>;}
</style>

<script type="text/javascript" src="../scripts/ckeditor/ckeditor.js"></script>

</head>
<body>

<?php
include ("./menu.php");

?>

<div id="contenant">
		

<div class="box">


	<h2>Gestion des notifications</h2>
	
	
	<div class="col_left" id="liste_paiements">
    
   <?php
// On récupère les données nécessaires à la suite du traitement
		$requete = 'SELECT id, titre, notification, date FROM ' . $tab_notifications . ' ORDER BY date ASC' ;
		$mysql_result = mysql_query($requete, $db);
?>   
   
					<h3>Notifications</h3>
					<ul class="fieldlist">
                    <?php
							while($data = mysql_fetch_assoc($mysql_result))
							{
                            echo '
					<li>
					
					<span>
							<a href="?action=modifier&id='.$data['id'].'" title="Modifier">
							<img src="../images/pencil.png" alt="Modifier">
					</a>
					</span>
					<a href="?action=modifier&id='.$data['id'].'" onclick="return false">'.$data['titre'].'</a>
					</li>';
							}
							?>
					
                    </ul>
                    
                    <div id="bouton_ajouter">                            
<a class="button" href="?action=ajouter"><span><img src="../images/add.png" alt="Ajouter">Ajouter une notifications</span></a>
</div>
                    
                    </div>	
                    
                    
                    
                    
                    
<div id="ajouter_notification" class="col_right">
          
                    
	<h3>Ajouter un taux de tva</h3>
	<form method="post" id="formulaire" enctype="multipart/form-data" action="?action=effectuer_ajouter">
		<p class="gris01">
			<label for="titre">Titre :</label>
			<input value="" name="titre" id="titre" class="textfield" size="80" type="text">
        </p>
        <p>
        	<label for="notification">Notification :</label><br />
            <textarea class="do_ckeditor" name="contenu" id="contenu"></textarea><br />
		</p>
        </form>
		<p class="acenter">
            <a href="#" onclick='javascript:document.getElementById("formulaire").submit()' class="button submit"><span><img src="../images/accept.png" alt="Ajouter">Ajouter cette notification</span></a>
		</p>
	
    </div>
    

<div id="modifier_notification" class="col_right">
          
          <?php 
$id = $_GET['id'];
// On récupère les données nécessaires à la suite du traitement
		$requete = 'SELECT id, titre, notification, date FROM ' . $tab_notifications . ' WHERE id = ' .$id;
		$mysql_result = mysql_query($requete, $db);
		
							while($data = mysql_fetch_assoc($mysql_result)){
								$id = $data['id'];
								$contenu = $data['notification'];
								?>
                    
	<h3>Modifier ou Supprimer une notification</h3>
	<form id="formulaire2" method="post" enctype="multipart/form-data" action="?action=effectuer_modifier">
    <p class="gris01">
    <input name="id" value="<?php echo $id; ?>" type="hidden">
    <input name="date" value="<?php echo $data['date']; ?>" type="hidden">
			<label for="titre">Titre :</label>
			<input value="<?php echo $data['titre']; ?>" name="titre" id="titre" class="textfield" size="80" type="text" />
        </p>
        <p>
        	<label for="notification">Notification :</label><br />
            <textarea class="do_ckeditor" name="contenu2" id="contenu2"><?php echo $contenu; ?></textarea>
            
            <script type="text/javascript">
//<![CDATA[

CKEDITOR.replace( 'contenu2',
	{	
		skin : 'kama',
		uiColor: '#E0E6F1',
		toolbar : [
			['Cut','Copy','Paste','-','Undo','Redo','-','SpellChecker','Scayt'],
			['Maximize'],
			'/',
			['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
			['NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote'],
			['Link','Unlink']
		],
		resize_dir : 'vertical', // The directions where resizing is enabled. It can be 'both', 'vertical' or 'horizontal' 
		scayt_autoStartup : true,
		scayt_sLang : 'fr_FR'
	});
		
//]]>
</script>
            
            
            
            <br />
		</p>
        
        </form>
        
        <p class="gris01">
                <a href="#"  onclick='javascript:document.getElementById("formulaire2").submit()' class="button submit"><span><img src="../images/accept.png" alt="Modifier">Modifier cette notification</span></a>
            	<a href="?action=effectuer_supprimer&id=<?php echo $id; ?>"  class="button sup_mode_paiement margin-left" rel="2596"><span><img src="../images/delete_001.png" alt="Supprimer"> Supprimer cette notification</span></a>&nbsp;            </p>
        
		
	
    <?php
							}
							?>                        
    </div>    
    
    
    
    
    
    <hr>
	
	
</div></div>

<script type="text/javascript">
//<![CDATA[

CKEDITOR.replace( 'contenu',
	{	
		skin : 'kama',
		uiColor: '#E0E6F1',
		toolbar : [
			['Cut','Copy','Paste','-','Undo','Redo','-','SpellChecker','Scayt'],
			['Maximize'],
			'/',
			['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
			['NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote'],
			['Link','Unlink']
		],
		resize_dir : 'vertical', // The directions where resizing is enabled. It can be 'both', 'vertical' or 'horizontal' 
		scayt_autoStartup : true,
		scayt_sLang : 'fr_FR'
	});
		
//]]>
</script>

<?php
include('./footer.php');
?>

</body>
</html>

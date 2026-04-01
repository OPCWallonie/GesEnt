<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>Administration</title>
<meta name="keywords" content="" lang="fr">
<meta name="description" content="">

<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!--[if IE]><script language="javascript" type="text/javascript" src="http://www.wuro.fr/static/js/excanvas.js"></script><![endif]-->	

<link rel="stylesheet" type="text/css" href="../css/administration_users.css" media="all">

<script type="text/javascript" src="../scripts/validationEngine/jquery.validationEngine-fr.js"></script>
	<script type="text/javascript" src="../scripts/validationEngine/jquery.validationEngine.js"></script>
	<script type="text/javascript" src="../scripts/fileuploader/fileuploader.js"></script>


</head>
<body>

<?php
include ("./menu.php");
?>

<div id="contenant">
		

<div class="box">
	<h2>Administration</h2>

	<div class="gris01">
    	<p>
    	Nous vous proposons 2 méthodes différentes afin de calculer votre cout horaire, charges fixes incluses. Une destinée aux entrepreneurs, une destinée aux comptables.
    	<form id="simple" name="simple" enctype="multipart/form-data" action="?action=simple" method="post">
        	<input type="radio" name="radioSimple" class="radioSimple" value="simple" <?php if ($_SESSION['simple']=="simple") {echo 'checked="checked"';} ?> /> Méthode Simple <br /> 
			<input type="radio" name="radioSimple" class="radioSimple" value="detail" <?php if ($_SESSION['simple']=="detail") {echo 'checked="checked"';} ?>/> Méthode Détaillée <br />
        </form>
    	</p>
    </div>
<br />
<hr />
<br />
		
<?php 
// Ici se trouve tout le script sur l'upload des conditions générales...

/* Script pour ajouter des photos */

if (!empty($_POST['send'])) {

	// on défini le répertoire où sont stockées les images de grande taille  
	$dir = './images/condgen/';
	
	// on teste si le champ permettant de soumettre un fichier est vide ou non 
	if (empty($_FILES['photo']['tmp_name'])) { 
	  // si oui, on affiche un petit message d'erreur 
	  $msg = 'Aucun fichier envoy&eacute;.'; 
	} 
	else { 
	  // on examine le fichier uploadé en récupérant de nombreuses informations sur ce fichier
	  $tableau = @getimagesize($_FILES['photo']['tmp_name']); 
	
	  if ($tableau == FALSE) { 
		 // si le fichier uploadé n'est pas une image, on efface le fichier uploadé et on affiche un petit message d'erreur 
		 unlink($_FILES['photo']['tmp_name']); 
		 $msg = 'Votre fichier n\'est pas une image.'; 
	  } 
	  else { 
		 // on teste le type de notre image : gif (1), jpeg (2) ou png (3)
		 if ($tableau[2] == 2) { 
		 
			// Nom du fichier
			$file_upload = $dir . '/'.$_SESSION['ent_affil'].'_condgen.jpg';
			
			// Taille de l'image d'origine
			list($width, $height) = $tableau;
			$newwidth = 2480;
			$newheight = $height / ($width / 2480); // Hauteur proportionnelle
			
			// Chargement
			$thumb = imagecreatetruecolor($newwidth, $newheight);
			$source = imagecreatefromjpeg($_FILES['photo']['tmp_name']);
	
			// Redimensionnement
			imagecopyresized ($thumb ,$source ,0 ,0 ,0 ,0 ,$newwidth ,$newheight ,$width , $height);
			imagejpeg ($thumb, $file_upload);
			
			$msg = 'Photo import&eacute;e !'; 
		 } 
		 else { 
			// si notre image n'est pas de type jpeg ou png, on supprime le fichier uploadé et on affiche un petit message d'erreur 
			unlink($_FILES['photo']['tmp_name']); 
			$msg = 'Votre image est d\'un format non supporté.'; 
		 } 
	  } 
	} 
} 


// Mise à jour des données de marges et hauteurs ( ainsi que vérifications :) )
$action = $_GET['action'];
if ($action == 'updateMarges')
{
	
	
	
	// Controlons déjà les données sinon on renvoit pour une correction
	// Je pars sur une base que les marges doivent se situer entre -2 et 1. La hauteur entre 26 et 30. La largeur entre 18 et 22
	if ($_POST['marGauche'] >= -2 && $_POST['marGauche'] <= 1 && $_POST['marHaut'] >= -2 && $_POST['marHaut'] <= 1)
	{
		if($_POST['largeur'] >= 18 && $_POST['largeur'] <= 22 && $_POST['hauteur'] >= 26 && $_POST['hauteur'] <= 30 )
		{
			// Je sauvegarde ET converti mes données au cas où une virgule devrait être transformée en point ( ex : 18,5 => 18.5 )
			$_SESSION['condGen']['marGauche'] =	str_replace(',','.',$_POST['marGauche']);
			$_SESSION['condGen']['marHaut'] =	str_replace(',','.',$_POST['marHaut']);
			$_SESSION['condGen']['largeur'] =	str_replace(',','.',$_POST['largeur']);
			$_SESSION['condGen']['hauteur'] =	str_replace(',','.',$_POST['hauteur']);
	
			// On inclu les infos de connection à la DB
			
	
			$sql = "UPDATE " . $tab_infos . " 
					SET 
						marGauche='".$_SESSION['condGen']['marGauche']."',
						marHaut='".$_SESSION['condGen']['marHaut']."',
						largeur='".$_SESSION['condGen']['largeur']."',
						hauteur='".$_SESSION['condGen']['hauteur']."'
					WHERE ent_tva = '".$_SESSION['ent_tva']."'";
			
			
		}
		else // Sinon les paramètres ne sont pas bon...
		{
		echo '
		<script language="javascript">
			alert("La largeur doit être comprise entre 18 et 22; la hauteur doit être comprise entre 26 et 30");
		</script>
		';
		}
		 
	}
	else // Sinon les paramètres ne sont pas bon...
		{
		echo '
		<script language="javascript">
			alert("La marge gauche/haute doit être comprise entre -2 et 1");
		</script>
		';
			}	
	
}







?>

<script language="Javascript" type="text/javascript">

// Fonction de récupération de l'extension du fichier soumit
function recup_extension(fichier)
	{
	// Si le champ fichier n'est pas vide
	if (fichier != "")
		{
		nom_fichier = fichier; // On récupere le chemin complet du fichier
		nbchar = nom_fichier.length; // On compte le nombre de caractere que compose ce chemin
		extension = nom_fichier.substring(nbchar - 4, nbchar); // On récupère les 4 derniers caracteres
		extension = extension.toLowerCase(); // On uniforme les caracteres en minuscules au cas ou cela aurait été écris en majuscule
		return extension; // On renvoi l'extension vers la fonction appelante
		}
	}
	
// Fonction de vérification de l'extension aprés avoir choisi le fichier
function verif_extension(fichier)
	{
	// On appelle la fonction de récupération de l'extension et on récupère l'extension
	ext = recup_extension(fichier);
	
	// Si extension = jpg, gif ou png --> ok
	if(ext != ".jpg") { alert("L'extension du fichier que vous voulez uploader est : '"+extension+"'\nCette extension n'est pas autorisée !\nSeules les extensions suivantes sont autorisées : '.JPG' !"); }
	}

// Fonction de validation du formulaire
function Verification()
	{
	// Récupération des variables
	photo = document.form.photo.value;

	// Tests sur les champs
	if (photo == "")
		{
		alert("Le champs Image est obligatoire !");
		document.form.photo.focus();
		return false;
		}		
	else
		{
		// On appelle la fonction de récupération de l'extension et on récupere l'extension
		ext = recup_extension(photo);
		
		// Si extension = jpg, gif ou png --> ok
		if(ext != ".jpg")
			{
			alert("L'extension du fichier que vous voulez uploader est : '"+extension+"'\nCette extension n'est pas autorisée !\nSeules les extensions suivantes sont autorisées : '.JPG' !");
			document.form.photo.focus();
			return false;			
			}
			else { return true; }
		}
	}	
</script>



<div>
<?php
if (file_exists('./images/condgen/'.$_SESSION['ent_affil'].'_condgen.jpg'))
	{

	echo '	<a href="#?w=700" rel="voir_condgen" class="poplight rouge"><img src="../images/livr_loup.png" class="livr_loup" /></a>';		$com = "Cliquez sur l'icone à gauche pour regarder vos conditions générales actuelles <br /><br />"; 
	}
else
	{
		$com = "<br /><br />";
	}
?>	
<?php
echo '<span class="rp_weltxt">'.$com.'Fichier .jpg avec vos conditions générales :</span>';
	 if (!empty($msg)) $msg_nb_result = '<p class="' . $pclass . '">' . $msg . '</p>'; ?>
	<span class="rp_weltxt">
	<form name="form" id="form" action="administration.html" method="post" enctype="multipart/form-data" OnSubmit="return Verification()">
		<input type="file" name="photo" onChange="verif_extension(this.value);" /><br /><br />
		<input name="send" type="hidden" value="president">
		<input type="submit" name="go" value="Envoyer" />
	</form>  
</span>
<br />
<br />
<br />
</div>
<hr />

<div class="gris01">

<form id="marges" name="marges" enctype="multipart/form-data" action="?action=updateMarges" method="post">
<table >
	<tr>
		<td colspan="2" style="font-weight:bold" class="gris01">Marges d'impression des conditions générales</td>
	</tr>

	<tr>
		<td width="5%" class="gris01">
        	<span class="toolTip" title="Marge comprise entre -2 et 1, en cm. Cette valeur vous permet de centrer horizontalement vos conditions générales sur le verso du document à imprimer">&nbsp;</span>
			Marge gauche : <input type="text" name="marGauche" id="marGauche" value="<?php echo $_SESSION['condGen']['marGauche']; ?>" /><br />
        </td>
        <td width="5%" class="gris01">
        	<span class="toolTip" title="Marge comprise entre -2 et 1, en cm. Cette valeur vous permet de centrer verticalement vos conditions générales sur le verso du document à imprimer">&nbsp;</span>
            Marge haut : <input type="text" name="marHaut" id="marHaut" value="<?php echo $_SESSION['condGen']['marHaut']; ?>" /><br />
        </td>
	</tr>
	<tr>
		<td class="gris01">
        	<span class="toolTip" title="La largeur du format A4 est de 21cm. Néanmoins, à causes des marges lors de l'impression et selon les marges déjà présentes sur l'image scannée de vos conditions générales, vous devrez régler cette valeur afin que votre document ne soit pas tronqué. Par défaut nous avons réglé cette valeur à 20,5cms">&nbsp;</span>
        	Largeur : <input type="text" name="largeur" id="largeur" value="<?php echo $_SESSION['condGen']['largeur']; ?>" /><br />
        </td>
		<td class="gris01">
        	<span class="toolTip" title="La hauteur du format A4 est de 29,7cm. Néanmoins, à causes des marges lors de l'impression et selon les marges déjà présentes sur l'image scannée de vos conditions générales, vous devrez régler cette valeur afin que votre document ne soit pas tronqué. Par défaut nous avons réglé cette valeur à 28cms">&nbsp;</span>
            Hauteur : <input type="text" name="hauteur" id="hauteur" value="<?php echo $_SESSION['condGen']['hauteur']; ?>" /><br />
        </td>
	</tr>
    <tr>
    	<td colspan="2" align="center" class="gris01"><input type="submit" value="Modifier"  /></td>
    </tr>
</table>

</form>
</div>





<?php
echo'
<div id="voir_condgen" class="popup_block">
<img src="../images/condgen/'.$_SESSION['ent_affil'].'_condgen.jpg" width=700 height=560 />
</div>
';


?>     
        
<script language="javascript">
$(document).ready(function() {
	
	//Lorsque vous cliquez sur un lien de la classe poplight et que le href commence par #
$('a.poplight[href^=#]').click(function() {
	var popID = $(this).attr('rel'); //Trouver la pop-up correspondante
	var popURL = $(this).attr('href'); //Retrouver la largeur dans le href

	//RÃ©cupÃ©rer les variables depuis le lien
	var query= popURL.split('?');
	var dim= query[1].split('&');
	var popWidth = dim[0].split('=')[1]; //La premiÃ¨re valeur du lien

	//Faire apparaitre la pop-up et ajouter le bouton de fermeture
	$('#' + popID).fadeIn().css({
		'width': Number(popWidth)
	})
	.prepend('<a href="#" class="close"><img src="../images/close_pop.png" class="btn_close" title="Close Window" alt="Close" /></a>');

	//RÃ©cupÃ©ration du margin, qui permettra de centrer la fenÃªtre - on ajuste de 80px en conformitÃ© avec le CSS
	var popMargTop = ($('#' + popID).height() + 80) / 2;
	var popMargLeft = ($('#' + popID).width() + 80) / 2;

	//On affecte le margin
	$('#' + popID).css({
		'margin-top' : -popMargTop,
		'margin-left' : -popMargLeft
	});

	//Effet fade-in du fond opaque
	$('body').append(''); //Ajout du fond opaque noir
	//Apparition du fond - .css({'filter' : 'alpha(opacity=80)'}) pour corriger les bogues de IE
	$('#fade').css({'filter' : 'alpha(opacity=80)'}).fadeIn();

	return false;
});

//Fermeture de la pop-up et du fond
$('a.close, #fade').live('click', function() { //Au clic sur le bouton ou sur le calque...
	$('#fade , .popup_block').fadeOut(function() {
		$('#fade, a.close').remove();  //...ils disparaissent ensemble
	});
	return false;
});
	
	//Le code ici
});
</script>
        
        
        
        

	
</div></div>

<?php
include('./footer.php');
?>

</body>
<script language="javascript" type="text/javascript">
$(".radioSimple").click(function(e) {
	
	valeur = $('input[name=radioSimple]:checked', '#simple').val();
	
	Shadowbox.open({
	        content:    '../comptabilite/sauvedata.html?origine=administration&base=gf_infospratiques&champsBase=simple&donnee=' + valeur,
	        player:     "iframe",
	        title:      "Sauvegarde",
	        height:     140,
	        width:      400
	    });
}); 



</script>
</html>

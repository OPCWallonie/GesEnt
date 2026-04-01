<?php
// Session créée ? Sinon, en créer une nouvelle
if($PHPSESSID) session_start($PHPSESSID);
else session_start();
unset ($_SESSION['chantiers']);


// Je crée cette valeur afin de toujours avoir une variable de session qui sait où on se trouve
$_SESSION['localisation'] = 'chantiers';

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 

<title>Gestion des chantiers</title>
<meta name="keywords" content="" lang="fr">
<meta name="description" content="">

<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Script-Type" content="text/javascript">	
<link rel="stylesheet" type="text/css" href="../css/factures_achats.css" media="all" />
<link type="text/css" href="../css/datepicker2.css" rel="stylesheet" />
<style>
.pointer{
	cursor:pointer;
}
.archive{
	display:none;	
}
</style>
<script language="javascript">


$(document).ready(function() {
 	$( "#dateRech").datepicker({  
            inline: true,  
            showOtherMonths: true,  
            dayNamesMin: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],  
        });
 });
</script>
</head>
<body>



 <?php
include ("./menu.php");

$action = $_GET['action'];
$idDoc = $_GET['idDoc'];

if ($action == "ajouter_chantier") {
	
	$aInserer = "INSERT INTO gf_chantiers  (id, 
											chantier,
											annee,
											status,
											ent_tva,
											nomClient,
											adresseClient,
											codePostal,
											ville,
											pays,
											tvaClient,
											codeClient,
											mailClient 
											) 
				 VALUES
				 
												('',
												'".mysql_real_escape_string($_POST['refChantier'])."',
												'".date('Y')."',
												'actif',
												'".$_SESSION['ent_tva']."',
												'".mysql_real_escape_string($_POST['nomClient'])."',
												'".mysql_real_escape_string($_POST['adresseClient'])."',
												'".mysql_real_escape_string($_POST['codePostal'])."',
												'".mysql_real_escape_string($_POST['ville'])."',
												'".mysql_real_escape_string($_POST['pays'])."',
												'".mysql_real_escape_string($_POST['tvaClient'])."',
												'".mysql_real_escape_string($_POST['codeClient'])."',
												'".mysql_real_escape_string($_POST['mailClient'])."'
												)";
				// echo $aInserer;
				// On effectue l'insertion
				mysql_query('set names utf8');
				mysql_query($aInserer) or die('Erreur SQL !'.$aInserer.'<br />'.mysql_error());	
				
				/* Vu qu'on va insérer la possiblité d'inscrire des produits dans la chantier d'achat, il faut aussi dès lors prévoir l'enregistrement de ces données dans la table "lignes"... Particularité : dans cette table, le champs chantiers devra représenter l'id unique de cette chantier or on vient d'enregistrer les données et il vient de créer l'enregistrement ci-dessus... On doit dès lors ré-ouvrir l'enregistrement afin de récupérer la seule valeur qui nous est inconnue... L'ID */
				
				
				
				unset($aInserer);
				?>
                
                <script language="javascript">
				
				top.document.location = "../outils/chantiers.html"; // Comme ça j'évite que, si l'utilisateur raffraichi la page, il n'ajoute 2 fois la même chantier :)
				</script>
                
                <?php
}

/* Je veux maintenant charger les données hors de la base => nouvelle connexion :)
*/
		
// check connection 
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}


// Modification du jeu de résultats en utf8 
mysqli_set_charset($mysqli, "utf8");


$query = "SELECT * FROM gf_chantiers WHERE ent_tva = '".$_SESSION['ent_tva']. "'";

$cnt = 0; // Je dois utiliser un petit compteur que j'initialise ici :)
if ($result = $mysqli->query($query)) {
    // fetch associative array
    while ($row = $result->fetch_assoc()) {
		$_SESSION['chantiers'][$cnt] = array( 	id => $row['id'],
												chantier => $row['chantier'],
												annee => $row['annee'],
												status => $row['status'],
												ent_tva => $row['ent_tva'],
												nomClient => $row['nomClient'],
												adresseClient => $row['adresseClient'],
												codePostal => $row['codePostal'],
												ville => $row['ville'],
												pays => $row['pays'],
												tvaClient => $row['tvaClient'],
												codeClient => $row['codeClient'],
												mailClient => $row['mailClient']
												);
												
											
												
												
		// Ensuite je vérifie que les champs sont remplis sinon je leur donne la valeur "A remplir"
		if (empty($_SESSION['chantiers'][$cnt]['chantier'])) {
			$_SESSION['chantiers'][$cnt]['chantier'] = 'A remplir';
		}
		if (empty($_SESSION['chantiers'][$cnt]['nomClient'])) {
			$_SESSION['chantiers'][$cnt]['nomClient'] = 'A remplir';
		}
		if (empty($_SESSION['chantiers'][$cnt]['adresseClient'])) {
			$_SESSION['chantiers'][$cnt]['adresseClient'] = 'A remplir';
		}
		if (empty($_SESSION['chantiers'][$cnt]['codePostal'])) {
			$_SESSION['chantiers'][$cnt]['codePostal'] = 'A remplir';
		}
		if (empty($_SESSION['chantiers'][$cnt]['ville'])) {
			$_SESSION['chantiers'][$cnt]['ville'] = 'A remplir';
		}
		if (empty($_SESSION['chantiers'][$cnt]['pays'])) {
			$_SESSION['chantiers'][$cnt]['pays'] = 'A remplir';
		}
		if (empty($_SESSION['chantiers'][$cnt]['codeClient'])) {
			$_SESSION['chantiers'][$cnt]['codeClient'] = 'A remplir';
		}
		
		
		
			$cnt++; // Et j'incrémente le tout 
	}	
   	// free result set
    $result->free();
}

			
			?> 

<div id="contenant">
<div class="box">
	<h2>Gestion des chantiers</h2>
	<ul id="menu">        
    	<li>
			<a class="actif" id="chantiersList" href="#_chantiers_list" rel="chantiers_list">
				<img src="../images/chantiers.png" alt="Liste des chantiers" /> 
				<span>Liste des chantiers</span>
			</a>
        </li>
        <li>
			<a class="" id="chantierAdd" href="#_chantiers_add" rel="chantiers_add">
				<img src="../images/chantier_nouv.png" alt="Ajouter un chantier" /> 
				<span>Ajouter un chantier</span>
			</a>
        </li>
        <li>
			<a class="affichMask" href="#" rel="afficher">
				<img src="../images/archive_on.png" alt="Afficher les archives" /> 
				<span>Afficher les archives</span>
			</a>
        </li>
        </ul>	
	

		<div style="display: show;" id="chantiers_list" class="content">	
		<table cellspacing="0">
			<tbody><tr>
            	<th width="20">N°</th>
				<th width="150">Chantier</th>
                <th width="150">Nom</th>
				<th width="80">Année</th>
				<th>Code client</th>
				<th class="aleft" width="100">status</th>
			</tr>
			
		<?php
		
if (empty($_SESSION['chantiers']) && !empty($rechCritere)){ 
	echo '<tr><td colspan="6" class="acenter">
            Aucun résultat pour votre recherche, essayez d\'autres critères.
            </td></tr>';
}
elseif (empty($_SESSION['chantiers'])){ 
	echo '<tr><td colspan="6" class="acenter">
          Aucune chantier à afficher
          </td></tr>';
	}
else {
	$chantierscount = count($_SESSION['chantiers']);


	for ($i=0; $i<$chantierscount; $i++){
		$index = $i+1;
		echo '
			<tr onclick="document.location.href=\'#\'" rel="'.$i.'" class="poplight rouge pointer '.$_SESSION['chantiers'][$i]['status'].'">
				<td class="acenter">'.$index.'</td>
				
				<td class="acenter">'.$_SESSION['chantiers'][$i]['chantier'].'</td>
			
				<td class="acenter">'.$_SESSION['chantiers'][$i]['nomClient'].'</td>
			
				<td class="acenter">'.$_SESSION['chantiers'][$i]['annee'].'</td>
				<td class="acenter">'.$_SESSION['chantiers'][$i]['codeClient'].'</td>
				
				<td class="acenter">'.$_SESSION['chantiers'][$i]['status'].'</td>
			</tr>';

	}
}

?>
        
        
        </tbody></table>
		
	</div>
    
    
<!-- CI DESSOUS : LE FORMULAIRE --> 

  
    
  
    
    
	
	<div style="display: none;" id="chantiers_add" class="content hide">
  					
		<form id="ajouter_chantier" method="post" action="?action=ajouter_chantier" enctype="multipart/form-data">
	<p>
    	<span class="toolTip" title="Référence obligatoire, nommez votre chantier">&nbsp;</span>
		<label>Référence au chantier : </label>
    	<input type="text" class="textfield" id="refChantier" name="refChantier" value="" />
	</p>
	<p class="gris01">
		<span class="toolTip" title="Renseignez dans ce champ le nom ou la société de votre client. Ce nom apparaîtra dans l'entête du devis">&nbsp;</span>
		<label for="nomClient">Société ou  nom :</label>
		<input name="nomClient" class="textfield" id="nomClient" value="<?php if (isset($infos_generales)) echo $infos_generales['nomClient']; ?>" type="text" placeholder="Société" /> 					
	</p>
	<p>
		<span class="toolTip" title="L'adresse de votre client apparaîtra dans l'entête du devis">&nbsp;</span>
		<label>Adresse : </label>
		<textarea name="adresseClient" id="adresseClient" cols="40" rows="2"><?php if (isset($infos_generales)) echo $infos_generales['adresse']; ?></textarea>
	</p>
	<p class="gris01">
		<span class="toolTip" title="Le code postal apparaîtra dans l'entête du devis">&nbsp;</span>
		<label>Code postal : </label>
		<input autocomplete="off" name="codePostal" class="textfield" id="codePostal" placeholder="Code Postal" type="text" value="<?php if (isset($infos_generales)) echo $infos_generales['code_postal']; ?>" /> 
	</p>
	<p>	
		<span class="toolTip" title="La ville apparaîtra dans l'entête du devis">&nbsp;</span>
		<label>Ville : </label>
        <input name="ville" class="textfield" id="ville" placeholder="Ville" type="text" value="<?php if (isset($infos_generales)) echo $infos_generales['ville']; ?>" />
	</p>
	<p class="gris01">
		<span class="toolTip" title="Le pays apparaîtra dans l'entête du devis">&nbsp;</span>
	    <label>Pays : </label>
        <input name="pays" class="textfield" id="pays" placeholder="Pays" type="text" value="<?php if (isset($infos_generales)) echo $infos_generales['pays']; ?>" />
    </p>
	<p>
		<span class="toolTip" title="Si l'entreprise à facturer possède un numéro de tva intracommunautaire spécifiez-le dans ce champ. Celui-ci apparaitra dans l'entête de la facture. Le numéro de TVA est composé du préfixe du pays suivi d'au maximum 12 chiffres ou caractères alphanumériques">&nbsp;</span>
		<label for="tvaClient">N° TVA intra :</label>
		<input name="tvaClient" class="textfield" id="tvaClient" placeholder="TVA" type="text" value="<?php if (isset($infos_generales)) echo $infos_generales['tva_intra']; ?>" />
	</p>
	<p class="gris01">
		<span class="toolTip" title="Spécifiez le code client qui vous permettra de retrouver le devis lors d'une recherche par code client">&nbsp;</span>
		<label>Code compta/client : </label><input name="codeClient" id="codeClient" class="textfield" value="<?php if (isset($infos_generales)) echo $infos_generales['code_client']; ?>" type="text"  placeholder="cl00001" />
	</p>
   	<p>
		<span class="toolTip" title="Renseignez l'adresse e-mail du client qui apparaîtra sur le devis et vous permettra si vous le souhaitez d'envoyer automatiquement le devis par e-mail">&nbsp;</span>
		<label>E-mail : </label><input name="mailClient" id="mailClient" class="textfield" placeholder="e-mail" type="text" value="<?php if (isset($infos_generales)) echo $infos_generales['mail_client']; ?>" />
	</p>
        
        
        
        

			
            </fieldset>
						<p class="acenter gris01">
				<a onclick='javascript:document.getElementById("ajouter_chantier").submit()' id="ajouter" class="button submit"><span><img src="../images/accept.png" alt="Ajouter">Ajouter ce chantier</span></a>
			</p>
		</form>	</div>
	
	
	
	
</div></div>

<script language="javascript">
$('#dateFact').live('click', function(e) {
   e.preventDefault();
   $(this).attr("autocomplete", "off");  
});

 $(document).ready(function() {
 	$( "#dateFact").datepicker({  
            inline: true,  
            showOtherMonths: true,  
            dayNamesMin: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],  
        });
 });
 </script>



<script type="text/javascript" >
$("input[class^=textfield]").bind("blur", function(){
    var valeur = this.value;
    this.value = valeur.replace(',','.');
  });



</script>

<?php
include('./footer.php');
mysql_close();
?>

<div id="visualise" class="popup_block">
	<h3>Résumé du chantier</h3>
    	<br />
    <span id="contenuVisu"></span>
</div>



<script language="javascript">
$(document).ready(function() {

if ($("#chantiers_add").is(":visible")){
	$('.affichMask').hide();
	
}

	
	//Lorsque vous cliquez sur un lien de la classe poplight et que le href commence par #
$('.poplight').click(function() {
	var popID = 'visualise';
	var idDoc = $(this).attr('rel'); //Trouver la pop-up correspondante
	var popURL = $(this).attr('href'); // Pour reprendre la ligne du document
	
// 	top.document.location = "#_chantiers_list";

	var popWidth = '800'; //La premiÃ¨re valeur du lien

	//Faire apparaitre la pop-up et ajouter le bouton de fermeture
	$('#visualise').fadeIn().css({
		'width': Number(popWidth),
		'height': Number('430')
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
	$('#contenuVisu').html('<iframe src="../outils/visualiser_le_chantier.html?idDoc='+idDoc+'" width="800" height="400"></iframe>')({})
	return false;
});

//Fermeture de la pop-up et du fond
$('a.close, #fade').live('click', function() { //Au clic sur le bouton ou sur le calque...
	$('#fade , .popup_block').fadeOut(function() {
		$('#fade, a.close').remove();  //...ils disparaissent ensemble
	});
	top.document.location = "../outils/chantiers.html"; // Je re-charge la page sinon il n'affiche plus rien...
	return false;
});


	
	//Le code ici
});


//Fermeture de la pop-up et du fond

// Maintenant je regarde si je dois afficher ou masquer les archives...
$('.affichMask').live('click', function(e){
	e.preventDefault();
	if ($('.poplight').hasClass("archive")){
   
		if ( $('.affichMask').attr('rel') == "afficher"){
			$(this).html('<img src="../images/archive_off.png" alt="Afficher les archives" /><span>Masquer les archives</span>');
			$('.archive').show();
			$('a.affichMask').attr('rel', 'masquer');
		}
		else {
			$(this).html('<img src="../images/archive_on.png" alt="Afficher les archives" /><span>Afficher les archives</span>');
			$('.archive').hide();
			$('a.affichMask').attr('rel', 'afficher');
		}
	}
	else {
		alert('Il n\'y a encore aucune archive à afficher');
	}
	
	
});


$('#chantierAdd').live('click', function(e){
		$('.affichMask').hide();
});

$('#chantiersList').live('click', function(e){
		$('.affichMask').show();
});

// La je regarde si chantiers_add est affiché, si oui, je cache l'icone des archives



</script>
</html>

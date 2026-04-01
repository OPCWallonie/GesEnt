<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>Gestion des préférences</title>
<meta name="keywords" content="" lang="fr">
<meta name="description" content="">

<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<link rel="stylesheet" type="text/css" href="../css/canevas.css" media="all" />
<style type="text/css">
#status {
	cursor:pointer;
}
#menu {
	cursor:pointer;
}

</style>

</head>
<body>

<?php

include ("./menu.php");
$rech = $_GET['rech'];

?>

<div id="contenant">
	<div class="box">		
		<h2>Mes chantiers</h2>
        
        
        <form action=""  method="get" id="formuRecherche" class="search">
  		
   			
			<?php
				if ($rech == "arch"){
					$rechCritere = "AND status LIKE 'Archivé' ";
					echo '<input id="rech" name="rech" type="hidden" value="actif" />';
					echo '<a href="#?rech=actif" class="button submit"><span class="boutonRech"><img src="../images/magnifier.png" alt="Recherche">Afficher les chantiers actifs</span></a>';
				} else {
					$rechCritere = "AND status LIKE 'Actif' ";
					echo '<input id="rech" name="rech" type="hidden" value="arch" />';
					echo '<a href="#?rech=arch" class="button submit"><span class="boutonRech"><img src="../images/magnifier.png" alt="Recherche">Afficher les chantiers archivés</span></a>';
				}
			?>
            
            
			<span class="button sumbit"><span class="boutonNew newChantier"><img src="../images/chantier_nouv.png" alt="Nouveau" width="16px">Nouveau chantier</span>	 </span>              
                               
        
        </form>
        <table cellspacing="0">
			<tbody>
            	<tr>
                    <th width="50">&nbsp;</th>
					<th class="aleft">Mes chantiers</th>
                    <th width="100" class="aleft">Année</th>
					<th width="200" class="aleft">Status</th>
					
				</tr>
<?php 




               
                // D'abord j'ouvre ma base pour voir si le chantier existe déjà !
if ($mysqli	= mysqli_connect($db_host,$db_login,$db_pass,$db_base))
{
    // Si la connexion a réussi, rien ne se passe.
}
else // Mais si elle rate…
{
    echo 'Erreur'; // On affiche un message d'erreur.
}



$query = "SELECT * FROM gf_chantiers WHERE ent_tva LIKE '".$_SESSION['ent_tva']. "' ".$rechCritere;
$cnt = 0;
$result = mysqli_query($mysqli, $query);
$i=1;		
    // fetch associative array 
   	while ($row = mysqli_fetch_assoc($result)) {
   		echo '
		<tr>
                	<td onclick="document.location.href=\'#\'" rel="'.$row['chantier'].'" class="poplight rouge pointer" id="noeud">'.$i.'<input type="hidden" id="idChant" value="'.$row['id'].'" /></td>
                	<td id="chantier" onclick="document.location.href=\'#\'" rel="'.$row['chantier'].'" class="poplight rouge pointer">'.$row['chantier'].'</td>
					<td id="annee" onclick="document.location.href=\'#\'" rel="'.$row['chantier'].'" class="poplight rouge pointer">'.$row['annee'].'</td>
                    <td>';
					switch($row['status']){
						case 'Actif':
							echo '<img src="../images/diode_verte.png" class="inline" />&nbsp; <a href="#" class="status underline" id="status">'.$row['status'].'</a>';
							break;
						case 'Archivé':
							echo '<img src="../images/diode_grise.png" class="inline" />&nbsp; <a href="#" class="status underline" id="status">'.$row['status'].'</a>';
							break;
					}
					
					echo '
					</td>
                </tr>
		
		';
	$i++;	  		
   	}

   	// free result set 
    mysqli_free_result($result);
                
?>                
                
				
				
          	</tbody>
        </table>
      
      
      
      <!-- CI DESSOUS : LE FORMULAIRE --> 

      
        
    </div>
</div>

<?php
include('./footer.php');
?>

<div id="visualise" class="popup_block">
	<h3>Résumé de ce chantier</h3>
    	<br />
    <span id="contenuVisu"></span>
</div>

<div id="nouveauChantier" class="popup_block">
	<h3>Nouveau chantier</h3>
    	<br />
    <span id="nouveauChantierVisu"></span>
</div>

</body>
<script language="javascript">
$(document).ready(function() {








	//Lorsque vous cliquez sur un lien de la classe newChantier
$('.newChantier').click(function() {
	var popID = 'nouveauChantier';
	
	
// 	top.document.location = "#_factures_list";

	var popWidth = '500'; //La premiÃ¨re valeur du lien

	//Faire apparaitre la pop-up et ajouter le bouton de fermeture
	$('#nouveauChantier').fadeIn().css({
		'width': Number(popWidth),
		'height': Number('370')
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
	$('#nouveauChantierVisu').html('<iframe src="../comptabilite/nouveau_chantier.html" width="500" height="340"></iframe>')({})
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






//Lorsque vous cliquez sur un lien de la classe poplight et que le href commence par #
$('.poplight').click(function() {
	var popID = 'visualise';
	var idDoc = $(this).attr('rel'); //Trouver la pop-up correspondante
	var popURL = $(this).attr('href'); // Pour reprendre la ligne du document
	
// 	top.document.location = "#_factures_list";

	var popWidth = '800'; //La premiÃ¨re valeur du lien

	//Faire apparaitre la pop-up et ajouter le bouton de fermeture
	$('#visualise').fadeIn().css({
		'width': Number(popWidth),
		'height': Number('370')
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
	$('#contenuVisu').html('<iframe src="../outils/visualiser_le_chantier.html?chantier='+idDoc+'" width="800" height="340"></iframe>')({})
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
	
	
	
</script>






</html>

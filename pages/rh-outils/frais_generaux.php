<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>Frais Généraux</title>
<meta name="keywords" content="" lang="fr">
<meta name="description" content="">

<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!--[if IE]><script language="javascript" type="text/javascript" src="http://www.wuro.fr/static/js/excanvas.js"></script><![endif]-->	

<link rel="stylesheet" type="text/css" href="../css/ajouter_un_devis.css" media="all">
<script language="JavaScript" type="text/javascript" SRC="../scripts/div.js"></script>
</head>
<body>

<?php
include ("./menu.php");
$action = $_GET['action'];
                    
if ($action == "extra_add") {
	$txt_bouton = 'Ajouter';
	echo "<script>javascript:visibilite('extra_edit');</script>";

						}
elseif ($action =="extra_modify") {
	$txt_bouton = 'Modifier';
	echo "<script>javascript:visibilite('extra_edit');</script>";
	
}
else {
	$action = "liste";
	$txt_bouton = 'Ajouter';
	
	}
?>

<div id="contenant">
		

<div class="box">
	<h2>Frais généraux</h2>
		
	<div>
    <p>Les frais généraux d'une entreprise sont des frais annexes au "core-business" c'est à dire des frais n'ayant pas de rapport direct à la production ( un bureau, du matériel informatique, une voiture de société, ... ). Les frais généraux peuvent donc être variables ou fixes. Ces données sont connues et calculées par votre comptable.</p>
    <br />
    <p>Le coût salarial est, quant à lui, calculé par votre secrétariat social et/ou votre comptable. Ces derniers pourront vous fournir avec précision les données relatives au salaires, jours de récupérations, maladies, congés, ...
    </p>
    </div>
    <br />
    <hr />
    <br />
    <form id="formulaire" method="post" enctype="multipart/form-data" action="">
    	<p class="gris01">
			<span class="toolTip" title="Contactez votre secrétariat social ou votre comptable afin d'obtenir cette information">&nbsp;</span>
			<label for="coutSalarial">Coût salarial :</label>
			<input name="coutSalarial" id="coutSalarial" placeholder="" style="width: 40px;" class="textfield" value="" type="text" />
		</p>
        <p>
			<span class="toolTip" title="Contactez votre secrétariat social afin d'obtenir cette information">&nbsp;</span>
			<label for="heuresReelles">heures réelles :</label>
			<input name="heuresReelles" id="heuresReelles" placeholder="" style="width: 40px;" class="textfield" value="" type="text" />
		</p>
        <p class="gris01">
			<span class="toolTip" title="Contactez votre comptable afin d'obtenir cette information">&nbsp;</span>
			<label for="chargesFixes">Charges fixes :</label>
			<input name="chargesFixes" id="chargesFixes" placeholder="" style="width: 40px;" class="textfield" value="" type="text" />
		</p>
	</form>
    <br />
    <p>
    Selon la formule : Coût horaire avec charges fixes = coût salarial + charges fixes / nombre d'heures réelles<br />
    Votre coût horaire est donc de <span id="coutHoraire" class="gras">&nbsp;</span> €
    </p>
        
    
    
    
	
	

	
</div></div>

<?php
include('./footer.php');
?>
<script language="javascript" type="text/javascript">
$("#coutSalarial").blur(function(e) {
		e.preventDefault();
		calcTotal(); // Tableau en bas ( Requis pour Approche Spécifique )	
});
$("#heuresReelles").blur(function(e) {
		e.preventDefault();
		calcTotal(); // Tableau en bas ( Requis pour Approche Spécifique )	
});
$("#chargesFixes").blur(function(e) {
		e.preventDefault();
		calcTotal(); // Tableau en bas ( Requis pour Approche Spécifique )	
});

function calcTotal(){
	
$('#coutHoraire').html((parseFloat($('#coutSalarial').val()) + parseFloat($('#chargesFixes').val())) / parseFloat($('#heuresReelles').val()));
}
</script>
</body>
</html>

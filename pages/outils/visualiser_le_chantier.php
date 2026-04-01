<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>
<style type="text/css">
label {
	font-weight:bold;

}
span {cursor:pointer;}
.gris { background: none repeat scroll 0% 0% rgb(204, 204, 204); }
form p { padding: 5px; line-height: 25px; clear: both; height: auto; min-height: 25px; z-index: 1; }
a.button { display: inline-block; height: 30px; background: url('../images/bouton-right.png') no-repeat scroll 100% 0px transparent; line-height: 30px; font-size: 14px; font-weight: normal; }
a.button span { display: block; height: 30px; margin-right: 3px; padding: 0px 3px 0px 6px; background: url('bouton-left.png') no-repeat scroll 0px 0px transparent; }
a.button:hover, a.button:focus { background-position: 100% -30px; text-decoration: none; cursor: pointer; }
a.button:hover span { background-position: 0px -30px; }
a.button span img { float: left; margin: 8px 5px 7px 0px; display: inline; }



</style>
<link rel="stylesheet" type="text/css" href="../css/ajouter_un_devis.css" media="all" />
<link type="text/css" href="../css/datepicker2.css" rel="stylesheet" />
</head>

<body>
<?php
$i = $_GET['idDoc'];
$_SESSION['numDoc'] = $i;



?>

<form id="formulaire" method="post" enctype="multipart/form-data" action="?action=modifier&idDoc=<?php echo $i;?>">
	<input id="idDoc" type="hidden" value="<?php echo $_SESSION['chantiers'][$i]['id'];?>" />
    <input id="pageOrigine" type="hidden" value="<?php echo $_SESSION['localisation'];?>" />
	<p class="gris">
		<label>Nom du chantier</label>
    	<span id="chantier"><?php echo $_SESSION['chantiers'][$i]['chantier'];?></span>
        <input type="hidden" id="chantier1" value="'+valeur+'" />
	</p>
	<p>
    	<label>Année de création</label>
        <span id="annee"><?php echo $_SESSION['chantiers'][$i]['annee'];?></span>
        <input type="hidden" id="annee1" value="'+valeur+'" />
    </p>
    <p class="gris">
		<label>Status</label>
        <span id="status"><?php echo $_SESSION['chantiers'][$i]['status'];?></span>
        <input type="hidden" id="status" value="'+valeur+'" />
    </p>
    <p>
		<label>Nom du client</label>
        <span id="nomClient"><?php echo $_SESSION['chantiers'][$i]['nomClient'];?></span>
        <input type="hidden" id="nomClient1" value="'+valeur+'" />
    </p>
    <p class="gris">
		<label>Adresse</label>
        <span id="adresseClient"><?php echo $_SESSION['chantiers'][$i]['adresseClient'];?></span>
        <input type="hidden" id="adresseClient1" value="'+valeur+'" />
    </p>
    <p>
		<label>Code Postal</label>
        <span id="codePostal"><?php echo $_SESSION['chantiers'][$i]['codePostal'];?></span>
        <input type="hidden" id="codePostal1" value="'+valeur+'" />
    </p>
    <p class="gris">
		<label>Ville</label>
        <span id="ville"><?php echo $_SESSION['chantiers'][$i]['ville'];?></span>
        <input type="hidden" id="ville1" value="'+valeur+'" />
    </p>
    <p>
		<label>Pays</label>
        <span id="pays"><?php echo $_SESSION['chantiers'][$i]['pays'];?></span>
        <input type="hidden" id="pays1" value="'+valeur+'" />
	</p>
    <p class="gris">
		<label>TVA</label>
        <span id="tvaClient"><?php echo $_SESSION['chantiers'][$i]['tvaClient'];?></span>
        <input type="hidden" id="tvaClient1" value="'+valeur+'" />
    </p>
    <p>
		<label>Code client</label>
        <span id="codeClient"><?php echo $_SESSION['chantiers'][$i]['codeClient'];?></span>
        <input type="hidden" id="codeClient1" value="'+valeur+'" />
    </p>
    <p class="gris">
		<label>E-mail</label>
        <span id="mailClient"><?php echo $_SESSION['chantiers'][$i]['mailClient'];?></span>
        <input type="hidden" id="mailClient1" value="'+valeur+'" />
    </p>

<br />
<br />

</form>
</body>

<script language="javascript">
// Si on clique sur Supprimer, je veux d'abord vérifier si on est sur de vouloir supprimer

/*
********* Ma fonction de sauvegarde et de mise à jour de script ***********
*/



function acte(origine, valeur, old, eur){
	idDoc = $('#idDoc').val();
	
	
	// Ici je redirige vers la page de sauvegarde base
	/*
	$.ajax({
			type: 'GET',
			url: 'sauvedata.html',
			//data: "id=<?php //echo $_SESSION['chantiers'][$i]['id'];?>&champsBase="+ origine + "$donnee=" + valeur
		});
	*/
	
	Shadowbox.open({
	        content:    '../comptabilite/sauvedata.html?origine=<?php echo $_SESSION['localisation'];?>&base=gf_chantiers&champsBase=' + origine + '&donnee=' + valeur + '&id=' + idDoc,
	        player:     "iframe",
	        title:      "Sauvegarde",
	        height:     140,
	        width:      400
	    });
	
	
	
	
	var origine2 = '#'+origine;
	
	// Et mise à jour de la page
	
	if (valeur == ''){
		valeur = old;
		}
		
	if (eur == 'eur'){
		$(origine2).html('<span id="'+ origine +'">'+valeur+' &euro;</span><input type="hidden" id="'+origine+'1" value="'+valeur+'" />');
	}
	else {
		$(origine2).html('<span id="'+ origine +'">'+valeur+'</span><input type="hidden" id="'+origine+'1" value="'+valeur+'" />');	
	}
	
		
	
}





$('#chantier').live('click', function(e){
	e.preventDefault();
	type_doc = $('#type_doc').text();
	var val = $(this).text();
	old = "<?php echo $_SESSION['chantiers'][$i]['chantier'];?>"; 	
	old = old.replace(/\\/g,"\\\\");
	old = old.replace(/\'/g,"\\'");
	old = old.replace(/\"/g,"\\\"");
	$(this).html('<input type="text" id="chantier" value="'+val+'" onblur="acte(\'chantier\', this.value, old)" onKeyDown="sauteChamps(\'chantier\', this.value, old)" />');
	$('#chantier').focus();
});


$('#annee').live('click', function(e){
	e.preventDefault();
	type_doc = $('#type_doc').text();
	var val = $(this).text();
	old = "<?php echo $_SESSION['chantiers'][$i]['annee'];?>"; 	
	old = old.replace(/\\/g,"\\\\");
	old = old.replace(/\'/g,"\\'");
	old = old.replace(/\"/g,"\\\"");
	$(this).html('<input type="text" id="annee" value="'+val+'" onblur="acte(\'annee\', this.value, old)" onKeyDown="sauteChamps(\'annee\', this.value, old)" />');
	$('#annee').focus();
});

$('#status').live('click', function(e){
	e.preventDefault();
	type_doc = $('#type_doc').text();
	old = "<?php echo $_SESSION['chantiers'][$i]['status'];?>"; 	
	old = old.replace(/\\/g,"\\\\");
	old = old.replace(/\'/g,"\\'");
	old = old.replace(/\"/g,"\\\"");
	
	// If else pour qu'il choisisse la valeur en cours lors de l'édition sinon on reste bloqué en archivé ad vitam aeternam
	if (old == "actif"){
		$(this).html('<select id="status2" onchange="acte(\'status\', this.value, old)"><option value="actif">Actif</option><option value="archive">Archivé</option></select>');
	}
	else {
		$(this).html('<select id="status2" onchange="acte(\'status\', this.value, old)"><option value="actif">Actif</option><option value="archive" selected>Archivé</option></select>');
	}
	
	 $('#status2 option[value=' + $(this).text() + ']').attr('selected', 'selected');
		$('#status2').focus();
});

$('#nomClient').live('click', function(e){
	e.preventDefault();
	type_doc = $('#type_doc').text();
	var val = $(this).text();
	old = "<?php echo $_SESSION['chantiers'][$i]['nomClient'];?>"; 	
	old = old.replace(/\\/g,"\\\\");
	old = old.replace(/\'/g,"\\'");
	old = old.replace(/\"/g,"\\\"");
	$(this).html('<input type="text" id="nomClient" value="'+val+'" onblur="acte(\'nomClient\', this.value, old)" onKeyDown="sauteChamps(\'nomClient\', this.value, old)" />');
	alert('ok');
	opener.location.reload();
	$('#nomClient').focus();
});


$('#adresseClient').live('click', function(e){
	e.preventDefault();
	type_doc = $('#type_doc').text();
	var val = $(this).text();
	old = "<?php echo $_SESSION['chantiers'][$i]['adresseClient'];?>"; 	
	old = old.replace(/\\/g,"\\\\");
	old = old.replace(/\'/g,"\'");
	old = old.replace(/\"/g,"\"");
	$(this).html('<input size="75" type="text" id="adresseClient" value="'+val+'" onblur="acte(\'adresseClient\', this.value, old)" onKeyDown="sauteChamps(\'adresseClient\', this.value, old)" />');
	$('#adresseClient').focus();
});


$('#codePostal').live('click', function(e){
	e.preventDefault();
	type_doc = $('#type_doc').text();
	var val = $(this).text();
	old = "<?php echo $_SESSION['chantiers'][$i]['codePostal'];?>"; 	
	old = old.replace(/\\/g,"\\\\");
	old = old.replace(/\'/g,"\'");
	old = old.replace(/\"/g,"\"");
	$(this).html('<input type="text" id="codePostal" value="'+val+'" onblur="acte(\'codePostal\', this.value, old)" onKeyDown="sauteChamps(\'codePostal\', this.value, old)" />');
	$('#codePostal').focus();
});



$('#ville').live('click', function(e){
	e.preventDefault();
	type_doc = $('#type_doc').text();
	var val = $(this).text();
	old = "<?php echo $_SESSION['chantiers'][$i]['ville'];?>"; 	
	old = old.replace(/\\/g,"\\\\");
	old = old.replace(/\'/g,"\'");
	old = old.replace(/\"/g,"\"");
	$(this).html('<input size="75" type="text" id="ville" value="'+val+'" onblur="acte(\'ville\', this.value, old)" onKeyDown="sauteChamps(\'ville\', this.value, old)" />');
	$('#ville').focus();
});



$('#pays').live('click', function(e){
	e.preventDefault();
	type_doc = $('#type_doc').text();
	var val = $(this).text();
	old = "<?php echo $_SESSION['chantiers'][$i]['pays'];?>"; 	
	old = old.replace(/\\/g,"\\\\");
	old = old.replace(/\'/g,"\'");
	old = old.replace(/\"/g,"\"");
	$(this).html('<input size="75" type="text" id="pays" value="'+val+'" onblur="acte(\'pays\', this.value, old)" onKeyDown="sauteChamps(\'pays\', this.value, old)" />');
	$('#pays').focus();
});

$('#tvaClient').live('click', function(e){
	e.preventDefault();
	type_doc = $('#type_doc').text();
	var val = $(this).text();
	old = "<?php echo $_SESSION['chantiers'][$i]['tvaClient'];?>"; 	
	old = old.replace(/\\/g,"\\\\");
	old = old.replace(/\'/g,"\'");
	old = old.replace(/\"/g,"\"");
	$(this).html('<input size="75" type="text" id="tvaClient" value="'+val+'" onblur="acte(\'tvaClient\', this.value, old)" onKeyDown="sauteChamps(\'tvaClient\', this.value, old)" />');
	$('#tvaClient').focus();
});

$('#codeClient').live('click', function(e){
	e.preventDefault();
	type_doc = $('#type_doc').text();
	var val = $(this).text();
	old = "<?php echo $_SESSION['chantiers'][$i]['codeClient'];?>"; 	
	old = old.replace(/\\/g,"\\\\");
	old = old.replace(/\'/g,"\'");
	old = old.replace(/\"/g,"\"");
	$(this).html('<input size="75" type="text" id="codeClient" value="'+val+'" onblur="acte(\'codeClient\', this.value, old)" onKeyDown="sauteChamps(\'codeClient\', this.value, old)" />');
	$('#codeClient').focus();
});

$('#mailClient').live('click', function(e){
	e.preventDefault();
	type_doc = $('#type_doc').text();
	var val = $(this).text();
	old = "<?php echo $_SESSION['chantiers'][$i]['mailClient'];?>"; 	
	old = old.replace(/\\/g,"\\\\");
	old = old.replace(/\'/g,"\'");
	old = old.replace(/\"/g,"\"");
	$(this).html('<input size="75" type="text" id="mailClient" value="'+val+'" onblur="acte(\'mailClient\', this.value, old)" onKeyDown="sauteChamps(\'mailClient\', this.value, old)" />');
	$('#mailClient').focus();
});


// Un petit script pour permettre de sortir de l'édition quand on saute de champs

function sauteChamps(box, val, old, eur, e)
{
	var e = window.event || e;
    
     
	if(e.keyCode == 9 || e.keyCode == 13)
	{
		
		if(e.preventDefault) { e.preventDefault();}
		else { e.returnValue = false; }
		
		setTimeout(function() {}, 0);
		acte(box, val, old, eur);
		
	}
	
	
	
}

</script>
<script language="javascript" type="application/javascript">
						function noBack(){window.history.forward()} 
						noBack(); 
						window.onload=noBack; 
						window.onpageshow=function(evt){if(evt.persisted)noBack()} 
						window.onunload=function(){void(0)} 
						javascript:window.print();
						</script>
</html>
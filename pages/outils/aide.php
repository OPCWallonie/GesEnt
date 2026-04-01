<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Ajouter une facture</title>
<link rel="stylesheet" type="text/css" href="../css/aide.css" media="all" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js">
</script>
</head>

<body>
<?php
include ("./menu.php");
?>


<div id="contenant">
		<span class="hide" id="type_doc">Aide</span>


	<h2>Aide</h2>
    
    <dl class="tab">
        <dt>Comptabilité</dt>
        <dd class="box index">
        <h3>1.Devis</h3>
        <p>Cette section vous permet d'encoder vos revenus</p>
        <br />
        <h3>2.Factures</h3>
        <p>Cette section vous permet d'encoder vos charges ainsi que l'achat de matériel de production</p>
        <br />
        <h3>3.Achats</h3>
        <p>Cette section vous permet d'encoder vos achats de matières premières ainsi que tout ce qui est nécessaire à la réalisation de votre métier</p>
        </dd>
    </dl>
    
    <dl class="tab">
        <dt>Ressources humaines & outils</dt>
        <dd class="box index">
        	<h3>1.Collaborateurs</h3>
        	<p>Renseignez ici les informations de tous vos salariés</p>
        	<br />
            <h3>2.Frais extraordinaires</h3>
            <p>Indiquez ici toutes les dépenses qui ne rentrent dans aucune autre catégorie ( frais de déplacements, etc )</p>
        </dd>
    </dl>  
    
    
    
    
<h2>FAQ</h2>      

<dl class="tab">
        <dt>Configuration générale</dt>
        <dd class="box index">
        <p><a href="#?w=500" rel="quel_browser" class="poplight rouge">Quel navigateur Internet puis-je utiliser ?</a></p>
        <p><a href="#?w=800" rel="imprim_fond" class="poplight rouge">Ma facture s'imprime sans la couleur ainsi qu'avec des informations sur la date et l'URL du site...</a></p>
        <p><a href="#?w=500" rel="install_condgen" class="poplight rouge">Comment imprimer mes conditions générales au verso de ma facture ?</a></p>
        <p><a href="#?w=750" rel="marges" class="poplight rouge">Mes conditions générales ne s'impriment pas bien ( plusieurs pages, non centré, ... )</a></p>
        </dd>
        </dl>



</div>


<div id="quel_browser" class="popup_block">
	<h2>Quel navigateur Internet puis-je utiliser ?</h2>
	<p>Voici une liste des navigateurs pour lesquels ce site est optimisé</p>
    <br />
    <ul>
    	<li>- Safari 5 et sup.</li>
        <li>- Internet Explorer 9 et sup.</li>
        <li>- Firefox 16 et sup.</li>
    </ul>
</div>

<div id="imprim_fond" class="popup_block">
<iframe src="../outils/faq_impression.html" width="800" height="600"></iframe>
</div>

<div id="install_condgen" class="popup_block">
	<h3>Comment imprimer mes conditions générales au verso de ma facture ?</h3>
    <br />
    <p>1. Vous devez importer vos conditions générales sur le site. Pour ce faire, rendez-vous dans la section "Personalisation" et uploadez un scan que vous aurez réalisé au préalable. Le fichier doit être sous format jpg ( jpeg ). Notez que pour un résultat optimal il est conseillé de "recadrer" votre image afin d'éviter des marges inutiles</p>
    <br />
    <p>2. Vous devez impérativement disposer d'une imprimante étant capable d'imprimer en recto/verso et configurer celle-ci selon ce mode. Si ce n'est pas le cas, vos conditions générales seront imprimées sur une feuille séparée et il vous faudra dès lors les agrafer ensemble.</p>
</div>

<div id="marges" class="popup_block">
<h3>Mes conditions générales ne s'impriment pas bien ( plusieurs pages, non centré, ... )</h3>
<br />
<p>Pour régler les marges ainsi que la hauteur et la largeur de vos conditions générales, vous pouvez vous rendre dans la section "Personalisation" et régler les divers paramètres. En effet, le programme est réglé sur des bases génériques mais, d'une imprimante à l'autre, le rendu peut varier.</p> 
<p style="font-style:italic; font-size:12px">Pour ce qui est d'une impression sans couleurs, avec des informations du navigateur internet, ... Veuillez vous référez à l'aide "Ma facture s'imprime sans la couleur ainsi qu'avec des informations sur la date et l'URL du site..."</p> <br />
<p>1. Une impression des conditions générales sur plusieurs pages sera le produit d'un mauvais réglage de la hauteur/largeur du document.</p>
<p>2. Une impression non centrée de vos conditions générales résultera d'un mauvais réglage des marges.</p>
</div>






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


<?php
include('./footer.php');
?>
</body>
</html>
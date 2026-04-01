<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>Gestion Comptable CCL</title>
<meta name="keywords" content="" lang="fr">
<meta name="description" content="">

<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!--[if IE]><script language="javascript" type="text/javascript" src="http://www.wuro.fr/static/js/excanvas.js"></script><![endif]-->	
</head>
<body>
<?php
include ("./header.php");
include ("./menu.php");

if (isset($_SESSION[fact_data]))
{
unset($_SESSION[fact_data]);
}
?>




<div id="contenant">
	<dl class="tab">
        <dt>Comptabilité</dt>
        <dd class="box index">
        	<a href="../comptabilite/devis.html" class="icon-devis highlight">Devis</a>
            <a href="../comptabilite/bons_de_commandes.html" class="icon-bdc highlight">Bons de Commandes</a>
        	<a href="../comptabilite/facture.html" class="icon-factures highlight">Factures</a>  
            <a href="" class="icon-barre highlight_bis">&nbsp;</a>       	        	
            <a href="../comptabilite/factures_achats.html" class="icon-achat highlight">Achats</a>      		       	    	
         </dd>
    </dl>
    	
    <dl class="tab">
        <dt>Ressources humaines & outils</dt>
        <dd class="box index">
        	<?php
			// On va regarder si on affiche la méthode simple ou détaillée
			if ($_SESSION['simple'] == 'simple') {
				echo '<a href="../rh-outils/frais_generaux.html" class="icon-todolist highlight">Frais généraux</a>';
			}
			else {
				echo '	<a href="../rh-outils/gestion_des_collaborateurs.html" class="icon-users highlight">Collaborateurs</a>	   		
            			<a href="../rh-outils/frais_generaux_detail.html" class="icon-todolist highlight">Frais généraux</a>';	
			}
			?>	   		
            <a href="../rh-outils/gestion_des_clients.html" class="icon-clients highlight">Gestion clients et prospects</a>
            <a class="icon-prefs highlight" href="../rh-outils/administration.html">Personalisation</a>
            <a class="highlight" href="http://maps.google.be/" style="background-image: url('../thumb_001.php');" target="_new">Plans et itinéraires</a>
        	<a class="highlight" href="http://www.gmail.com/" style="background-image: url('../thumb_002.php');" target="_new">Gmail</a>
            <a class="highlight" href="http://www.ccl.be/" style="background-image: url('../images/ccl.png');">Accès à la CCL</a>        
        </dd>
    </dl>
<?php
if ($_SESSION['ent_nom'] == $admin_name){
?>

    <dl class="tab">
        <dt>Administration CCL</dt>
        <dd class="box index">
        <a class="icon-prefs highlight" href="../admin/administration_generale.html">Administration Générale</a>
        </dd>
    </dl>
 <?php
}
 ?>

</div>

<div id="brand">
	<a href="http://www.ccl.be/">CCL</a>
</div>
<?php
include('./footer.php');
?>

</body>
</html>

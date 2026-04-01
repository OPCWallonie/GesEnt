<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>Gestion des préférences</title>
<meta name="keywords" content="" lang="fr">
<meta name="description" content="">

<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!--[if IE]><script language="javascript" type="text/javascript" src="http://www.wuro.fr/static/js/excanvas.js"></script><![endif]-->	


</head>
<body>

<?php
include ("./menu.php");
?>

<div id="contenant">
		

	<h2>Gestion des préférences</h2>
	
	
   <dl class="tab">
    	<dt>Paramètres avancés</dt>
		<dd class="box index">
			<a class="icon-payment highlight" href="../admin/gestion_des_modes_de_payement.html">Modes de paiement</a>
			<a class="icon-tva highlight" href="../admin/taux_de_tva.html">Taux de TVA</a>
            <a class="icon-svgd_bd highlight" href="../admin/sauvegarde_de_la_base.html">Sauvegarde de la BD</a>
        	<a class="icon-notif highlight" href="../admin/notifications.html">Notifications</a>
         
         </dd>
    </dl>
    <dl class="tab">
         <dt>Statistiques</dt>
		<dd class="box index">
        	<a class="icon-statutil highlight" href=""> Statistiques Utilisateurs</a>
            <a class="icon-statnav highlight" href="">Statistiques naviguation</a>
        </dd>
	</dl>
</div>

<?php
include('./footer.php');
?>

</body>
</html>

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

<link rel="stylesheet" type="text/css" href="../css/ajouter_-_supprimer_-_modifier_un_collaborateur.css" media="all">
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
	<h2>Frais généraux détaillés</h2>
		
	<div>
    <p>Texte
    <input type="text" size="10" />
    </p>
    </div>
    
    
	
	

	
</div></div>

<?php
include('./footer.php');
?>

</body>
</html>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>
</head>
<body>
<?php
// OU TROUVER LES INFOS DE CONNECTION ? MENU A MON AVIS

// Je me connecte à la base afin de charger la liste de mes chantiers
$mysqli = new mysqli($db_host,$db_login,$db_pass,$db_base);
		
// J'initialise ma variable contenant la liste des chantiers avec 1 première entrée intitulée "général" si il s'agit d'une facture globale pour l'entreprise CAD pour tous les chantiers
		
$listeChantiers = '<option value="defaut" >---------</option>';
		
// check connection 
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}	

		
$query = "SELECT DISTINCT chantier FROM gf_chantiers WHERE ent_tva = '".$_SESSION['ent_tva']. "' AND status = 'actif'";

if ($result = $mysqli->query($query)) {
    // fetch associative array
    while ($row = $result->fetch_assoc()) {
		$listeChantiers = $listeChantiers.'<option value="'.$row['chantier'].'">'.$row['chantier'].'</option>';
		
	}	
   	// free result set
    $result->free();
}


?>
</body>
</html>
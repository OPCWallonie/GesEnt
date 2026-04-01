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
    
    <p>
<?
echo "Votre base est en cours de sauvegarde.......

";

$date = date('Ymd');


//Dans les scripts ci-dessous, remplacez nom_de_la_base.sql par le nom de votre fichier, serveur_sql par le nom du serveur sur lequel votre base est installée, nom_de_la_base par le nom de votre base de donnée et mot_de_passe par le mot de passe associé à votre base.

system("mysqldump --lock-tables=false --host=".$db_host." --user=".$db_login." --password=".$db_pass." ".$db_base." > ".$db_base."_".$date.".sql");

echo "C'est fini. Vous pouvez la récupérer ci-dessous ( click droit - enregistrer sous )";
?>
</p>

<div class="box">
<h2>Liste des sauvegardes</h2>

<?php 
$dirname = './'; 
$dir = opendir($dirname); 

while($file = readdir($dir)) { 

// Decouvre de quel type de fichier il s'agit ( lit l'extension .sql )

$extension=strrchr($file,'.');
 
// Comme le point ne vous intéresse pas
// forcément on le supprime
 
$extension=substr($extension,1) ;

if($file != '.' && $file != '..' && !is_dir($dirname.$file) && $extension == "sql") 
{ 
echo '- <a href=".'.$dirname.$file.'">'.$file.'</a>'.'<br /><br />'; 
} 
} 

closedir($dir); 
?> 

</div>

<br />
</div>

<?php
include('./footer.php');
?>


</body>
</html>

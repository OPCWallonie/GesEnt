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
// include('../scripts/config.inc.php');
?>

<div id="contenant">
		

	<h2>Export de vos données</h2>
    
    <p>
    Vos données vont être automatiquement exportées au format CSV compatible excel, OpenOffice, ...
	</p>
    
<!-- Script d'export... Il faudra rajouter la balise PHP



// Connexion MySQL
// Obligatoire pour la suite !

// la variable qui va contenir les données CSV
$outputCsv = '';

// Quel jour sommes-nous ?
$date = date('Ymd');

// Nom du fichier final
$fileName = $_SESSION['ent_affil'].'_'.$date.'.csv';

$requete = "SELECT * FROM 				 ORDER BY 					";
$sql = mysql_query($requete);
if(mysql_num_rows($sql) > 0)
{
    $i = 0;

    while($Row = mysql_fetch_assoc($sql))
    {
        $i++;

        // Si c'est la 1er boucle, on affiche le nom des champs pour avoir un titre pour chaque colonne
        if($i == 1)
            foreach($Row as $clef => $valeur)
                $outputCsv .= trim($clef).';';

            $outputCsv = rtrim($outputCsv, ';');
            $outputCsv .= "\n";
        }

        {
        // On parcours $Row et on ajout chaque valeur à cette ligne
        foreach($Row as $clef => $valeur)
            $outputCsv .= trim($valeur).';';

        // Suppression du ; qui traine à la fin
        $outputCsv = rtrim($outputCsv, ';');

        // Saut de ligne
        $outputCsv .= "\n";

    }

}
else
    exit('Aucune donnée à enregistrer.');

// Entêtes (headers) PHP qui vont bien pour la création d'un fichier Excel CSV
header("Content-disposition: attachment; filename=".$fileName);
header("Content-Type: application/force-download");
header("Content-Transfer-Encoding: application/vnd.ms-excel\n");
header("Pragma: no-cache");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
header("Expires: 0");

echo $outputCsv;
exit();





-->    
    
    
    
    
    

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

if($file != '.' && $file != '..' && !is_dir($dirname.$file) && $extension == "csv") 
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
include('../admin/footer.php');
?>


</body>
</html>

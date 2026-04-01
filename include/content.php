<?php
// Répertoire par défaut des pages
$repertoire 	= 'pages';
$fichier_404 	= $repertoire . '/erreurs/404.php';
$fichier_index 	= $repertoire . '/accueil/index.php';
$fichier_db		= $repertoire . '/ressources/ressources.php'; // Si page générée à partir de la DB
$adresse_site 	= "http://www.cscpl.org/gesent/pages/comptabilite/facture.html";

// Vérification si la variable existe et contient des caractères "normaux" (pour une URL)
// (lettres a --> z, chiffres 1 --> 9, et underscore et tiret)
if( ( isset($_GET['p']) && preg_match("/^[a-z0-9_-]+$/i", $_GET['p']) ) && ( isset($_GET['r']) && preg_match("/^[a-z0-9_-]+$/i", $_GET['r']) ) ) {
	// La page (on met tout en minuscules) et la rubrique
	$p = strtolower($_GET['p']);
	$r = strtolower($_GET['r']);
	
	// Le chemin complet de la page
	$fichier = $repertoire . '/' . $r . '/' . $p . '.php';
	
	// Vérification si le fichier existe (si oui, on l'affiche, sinon on affiche la 404)
	if($r != 'ressources') file_exists($fichier) ? include($fichier) : include($fichier_404);
	else include($fichier_db);
}
else include($fichier_index);
?>
<?php 
/* Script de deconnexion */

// Ouverture de la session
if($PHPSESSID) session_start($PHPSESSID);
else session_start();

// Les infos de configuration
include('../scripts/config.inc.php');

// Recupération du login du membre
$login_user_index 		= $_SESSION['login_user'];

// On remplace les cookie actuels avec un temps négatif pour le supprimer
setcookie('login_user_index', $login_user_index, time()-31536000); 	// Le cookie utiliser pour pré-remplir le champs login sur la page d'index
setcookie('keep', false, time()-31536000); 						// Le cookie pour une connexion automatique

// Destruction de la session
session_unset(); // On réecrit le tableau (avec une valeur vide)
session_destroy(); // On détruit le tableau réécrit

// Redirection vers la page appropriée avec le code
$url_site = '../accueil/index.html?code=deconnected';
header('Location: ' . $url_site);
exit();
?>
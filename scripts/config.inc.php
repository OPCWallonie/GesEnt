<?php
##############################################################################################################
// Fichier de configuration
##############################################################################################################

// On récupère la date de début de génération de la page
$execution_time = microtime(true);

// Constante
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT']);
define('DIRECTORY_SEPARATOR', '/');

// Site en maintenance ou non (true / false)<strong></strong>
$maintenance = false;

##############################################################################################################

// Paramètres de connexion à la base de données
$db_host 						= 'localhost'; 						// Nom du serveur
$db_login					 	= 'root';							// Nom d'utilisateur
$db_pass 						= 'root';							// Mot de passe
$db_base 						= 'gesent'; 							// Nom de la base de données



// La connexion à la DB
$db = mysql_connect($db_host, $db_login, $db_pass) or die('Base Down !');
mysql_select_db($db_base, $db) or die('Base Down !');
$mysqli							= new MySQLi($db_host,$db_login,$db_pass,$db_base);




// Les tables de la DB
$tab_ent						= 'gf_entreprises'; 						// Liste des entreprises
$tab_modepayement				= 'gf_modepayement';					// Liste des modes de payements
$tab_tauxtva					= 'gf_tauxtva';							// Liste des taux de TVA
$tab_notifications				= 'gf_notifications';					// Liste des notifications
$tab_devis						= 'gf_devis';							// Liste des devis	
$tab_bdc						= 'gf_bdc';								// Liste des bons de commandes	
$tab_facture					= 'gf_facture';							// Liste des factures
$tab_infos						= 'gf_infospratiques';					// Liste des infos pratiques pour les entrepreneurs
$tab_produits					= 'gf_produits';						// Liste des produits/Articles créés par les utilisateurs


##############################################################################################################

// Divers
$admin_login = 'cscpl';
$admin_name = 'CSCPL';
$url_site = 'localhost:8888/gesent/pages/';
?>
<?php
/* Script de connexion */

// Session créée ? Sinon, en créer une nouvelle
if($PHPSESSID) session_start($PHPSESSID);
else session_start();

// On inclu les infos de connection à la DB
include('../scripts/config.inc.php');
include('../scripts/function.inc.php');

// Récupération des variables éventuellement passées par le formulaire de connexion
if(!empty($_POST['login'])) 	$login 		= $_POST['login'];
if(!empty($_POST['password'])) 	$password 	= $_POST['password'];
if(!empty($_POST['keep'])) 		$keep 		= $_POST['keep']; // Indique si le membre veut rester connecté automatiquement au site

#######################################################################################################################

// On vérifie si tous les champs sont bien remplis (empty id_user_referer pour ne pas avoir de problème si c'est une reconnexion automatique pour laquel on a pas du retaper les MP etc.)
	
	// Si login est vide alors on récupère le cookie
	if(empty($login)) $login = $_COOKIE['login_user'];
	
	// Vérification compte admin
	$url_file = '../scripts/uppc_admin_access.txt'; // URL du fichier qui stock le mot de passe
	$fp = fopen ($url_file, 'r');
	$admin_password = fgets ($fp);
	fclose ($fp);
	echo $admin_password; // A COMMENTER APRES CAR CA AFFICHE LE MDP
	if (($login == $admin_login) && ($password == decrypt($admin_password))) {
		$_SESSION['ent_nom'] 		= $admin_name;
		$_SESSION['ent_affil']		= 'cscpl';
		$_SESSION['statut'] 		= 'admin';
		$_SESSION['ent_stat'] 		= 'ASBL';
		$_SESSION['ent_adresse'] 	= 'Galerie de la Sauveni&egrave;re, 5';
		$_SESSION['ent_cp'] 		= '4000';
		$_SESSION['ent_localite'] 	= 'Li&egrave;ge';
		$_SESSION['ent_tel'] 		= '04 232 42 75';
		$_SESSION['ent_fax'] 		= '04 222 39 54';
		$_SESSION['ent_email'] 		= 'informatique@ccl.be';
		$_SESSION['ent_tva'] 		= '0404233246';
		$_SESSION['ent_gsm'] 		= '';
		$_SESSION['ent_site'] 		= 'http://www.ccl.be';
		
		
		
		
		
		// La page vers laquelle rediriger après la connexion
		$url = '../accueil/index.html?code=success';
		
		// Le cookie utiliser pour pré-remplir le champs login
		$expire = 31536000; // Définition de la durée des cookie --> 1 an
		setcookie('login_user', $login, time()+$expire);
	}
	
if (empty($login) || empty($password)) {
	if(empty($login) && empty($password)) 	$url = '../accueil/index.html?code=loginmpvide';
	elseif(empty($login)) 					$url = '../accueil/index.html?code=loginvide';
	elseif(empty($password)) 				$url = '../accueil/index.html?code=mpvide';
}
else {
		/* echo '<script>alert("'.$login.'");</script>'; */
				
		// Acces = NUM TVA + NUM AFFILIE
		$login 		= ereg_replace("[^0-9]","",$login); // On ne garde que les chiffres (le +0 permet de convertir la variable en entier et de supprimer les 0 devant)
		$password 	= ereg_replace("[^0-9]","",$password) + 0; // On ne garde que les chiffres (le +0 permet de convertir la variable en entier et de supprimer les 0 devant)
	
		// On récupère les données nécessaires à la suite du traitement
		$requete = 'SELECT * FROM ' . $tab_ent . ' WHERE (ent_tva = "' . $login . '" AND ent_affil LIKE "%' . $password . '") OR (ent_tva LIKE "%' . $password . '" AND ent_affil = "' . $login . '") GROUP BY ent_id';
		
		$mysql_result = mysql_query($requete, $db);
		$nb_result = mysql_num_rows($mysql_result);
		
		// (ent_tva LIKE "%' . $login . '" AND ent_affil LIKE "%' . $password . '") OR (ent_tva LIKE "%' . $password . '" AND ent_affil LIKE "%' . $login . '") GROUP BY ent_id
		
	
		// Vérification si matching d'un login
	if ($nb_result == 1) {
			
			
			$row = mysql_fetch_array($mysql_result);
			$ent_nom = $row['ent_nom'];
			$ent_affil = $row['ent_affil'];
			//$ent_stat = $row['ent_stat'];
			//$ent_adresse = $row['ent_adresse'];
			//$ent_cp = $row['ent_cp'];
			//$ent_localite = $row['ent_localite'];
			//$ent_tel = $row['ent_tel'];
			//$ent_fax = $row['ent_'];
			
			// Le cookie utiliser pour pré-remplir le champs login
			$expire = 31536000; // Définition de la durée des cookie --> 1 an
			setcookie('login_user', $login, time()+$expire);
			
			// Ajout du cookie pour la connexion automatique
			// On l'envoi, le "/" permet de le rendre accessible même à partir des répertoires parents
			if($keep == true) setcookie('keep', true, time()+$expire);
	
			// On met les variables utilisateur en session			
			$_SESSION['ent_nom'] = $ent_nom;
			$_SESSION['ent_affil'] = $row['ent_affil'];
			$_SESSION['ent_stat'] = $row['ent_stat'];
			$_SESSION['ent_adresse'] = $row['ent_adresse'];
			$_SESSION['ent_cp'] = $row['ent_cp'];
			$_SESSION['ent_localite'] = $row['ent_localite'];
			$_SESSION['ent_tel'] = $row['ent_tel'];
			$_SESSION['ent_fax'] = $row['ent_fax'];
			$_SESSION['ent_email'] = $row['ent_email'];
			$_SESSION['ent_tva'] = $row['ent_tva'];
			$_SESSION['ent_gsm'] = $row['ent_gsm'];
			$_SESSION['ent_site'] = $row['ent_site'];
			
			
			
			// La page vers laquelle rediriger après la connexion
			$url = '../accueil/index.html?code=success';
			
			// Test
			// echo "Password OK";
		}
	else $url = '../accueil/index.html?code=unknownlogin'; // Si le pseudo n a pas été trouvé dans la DB
}

// Mode de Payements : Je vais me créer un tableau avec les divers modes de payement
// On récupère les données nécessaires à la suite du traitement des moyens de payements
		mysql_query('set names utf8'); // Instruction magique qui normalise les accents et permet d'avoir les mêmes caractères côté base et côté site... Ce qui ne gâche rien ;)
		$requete = 'SELECT id, nom, defaut, utilise FROM ' . $tab_modepayement ;
		$mysql_result = mysql_query($requete, $db);
$i = 0;
							while($data = mysql_fetch_assoc($mysql_result))
							{
								$mode_payement[$i] = array( 	id => $data['id'],
																nom => $data['nom'],
																defaut => $data['defaut']);
								$i++;
							}
	// Et je rentre ces données dans une variable de session
$_SESSION['mode_payement'] = $mode_payement;

// TVA : Je m'occupe maintenant de la TVA
// On récupère les données nécessaires à la suite du traitement des taux de TVA
		$requete = 'SELECT id, tva FROM ' . $tab_tauxtva . ' ORDER BY tva ASC' ;
		$mysql_result = mysql_query($requete, $db);	
$i=0;		
		while($data = mysql_fetch_assoc($mysql_result))
			{
				$taux_tva[$i] = $data['tva'];
				$i++;
			}
// Et maintenant on place ça dans une variable de session
$_SESSION['taux_tva'] = $taux_tva;


// Je charge mes infos pratiques dans des variables de sessions


$requete2 = 'SELECT devNumLast, bdcNumLast, factNumLast, marGauche, marHaut, largeur, hauteur, simple, nbrConnexions FROM ' . $tab_infos . ' WHERE ent_tva = "'.$_SESSION['ent_tva'].'"';
$mysql_result2 = mysql_query($requete2, $db);
$i = 0;
							while($data2 = mysql_fetch_assoc($mysql_result2))
							{
								$_SESSION['devNumLast'] = $data2['devNumLast'];
								$_SESSION['bdcNumLast'] = $data2['bdcNumLast'];
								$_SESSION['factNumLast'] = $data2['factNumLast'];
								$_SESSION['condGen']['marGauche'] = $data2['marGauche'];
								$_SESSION['condGen']['marHaut'] = $data2['marHaut'];
								$_SESSION['condGen']['largeur'] = $data2['largeur'];
								$_SESSION['condGen']['hauteur'] = $data2['hauteur'];
								$_SESSION['simple'] = $data2['simple'];
								$nbrConnexions = $data2['nbrConnexions'];
								$i++;
							}
// Si il s'agit de la 1ere connexion de l'entrepreneur, il faut créer une première valeur qui sera 0000...

if ($_SESSION['devNumLast'] == '' && $_SESSION['bdcNumLast'] == '' && $_SESSION['factNumLast'] == '')
{
	// J'aurais volontier déclaré une fonction pour ce qui suit mais malheureusement vu qu'on est déjà dans un require, avec l'url rewriting, ça foire
	
	// Donc j'initialise les données ici :)

	$chaineSplitee['1'] = date('Y');
	$chaineSplitee['2'] = '0000';
	$_SESSION['devNumLast'] = 'DEV/'.$chaineSplitee['1'].'/'.$chaineSplitee['2'];
	$_SESSION['bdcNumLast'] = 'BDC/'.$chaineSplitee['1'].'/'.$chaineSplitee['2'];
	$_SESSION['factNumLast'] = 'FAC/'.$chaineSplitee['1'].'/'.$chaineSplitee['2'];

	// J'inscris maintenant les valeurs pour les marges des conditions générales
	$_SESSION['condGen']['marGauche'] = -1;
	$_SESSION['condGen']['marHaut'] = 0;
	$_SESSION['condGen']['largeur'] = 20.5;
	$_SESSION['condGen']['hauteur'] = 28;
	
	// J'initialise la date du jour pour "dernière connexion"
	$dateDerConn = date('Y-m-j');
	
	
	// ET je crée l'info dans infosgenerales
	$sql = "INSERT INTO gf_infospratiques (ent_tva, devNumLast, bdcNumLast, factNumLast, marGauche, marHaut, largeur, hauteur, simple, nbrConnexions, dateDerConn) VALUES ('".$_SESSION['ent_tva']."','".$_SESSION['devNumLast']."','".$_SESSION['bdcNumLast']."','".$_SESSION['factNumLast']."', '".$_SESSION['condGen']['marGauche']."', '".$_SESSION['condGen']['marHaut']."', '".$_SESSION['condGen']['largeur']."', '".$_SESSION['condGen']['hauteur']."', '".$_SESSION['simple']."','1', '".$dateDerConn."' )";
				
				mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
				
				
				
}	
else
{
	// J'initialise la date du jour pour "dernière connexion"
	$dateDerConn = date('Y-m-j');
	// Et j'incrémente le compteur
	$nbrConnexions++;
	
	// Et j'update ces 2 infos dans ma BD
	$sql = "UPDATE gf_infospratiques 
	SET 
		nbrConnexions='".$nbrConnexions."', 
		dateDerConn='".$dateDerConn."'
	WHERE ent_tva = '".$_SESSION['ent_tva']."'";
	
	mysql_query('set names utf8');
	mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());					
		
			
}


/*
Je vais ici nettoyer la base lignes des éléments qui ne sont plus utilisés par devis/bdc/factures/factureachat

En gros, si ils sont vides, on peut virer la ligne
*/		
/*
$sql = 'DELETE FROM gf_lignes WHERE devis="" AND bdc="" AND facture="" AND factAchat=""';
mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error()); 
unset($sql);
mysql_close();
*/
// Redirection vers la page appropriée avec le code
// header ('Location: ' . $url);
// exit();


echo '<meta http-equiv="Refresh" content="0;url='.$url.'">';
	exit;


?>
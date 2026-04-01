<?php
// Retourne l'URL affichée mais sans les arguments (ex: rubrique/page.html?argument=toto --> rubrique/page.html)
function requestURIClean($this_page = NULL) {
	if ($this_page == NULL) $this_page = $_SERVER['REQUEST_URI'];
	if (strpos($this_page, '?') !== false) $this_page = reset(explode('?', $this_page)); // reset : Retourne la valeur du premier élément du tableau, ou FALSE si le tableau est vide
	return $this_page; 
}

// Format le n° d'entreprise
function formatTVA($num_ent) {
	if (strlen($num_ent) == 9) $num_ent = '0' . $num_ent; // Rajout d'un zéro si nécessaire
	$num_ent = substr_replace($num_ent,'.',4,0); // On rajoute un point pour séparer les séries de chiffres
	$num_ent = substr_replace($num_ent,'.',8,0); // idem
	$num_ent = 'BE ' . $num_ent; // Ajout du BE
	return $num_ent;
}

/* ########################################################################################################## */
/* ################################            SECURITE              ######################################## */
/* ########################################################################################################## */

// Cryptage - decryptage
function encrypt($data) {
	
	// Clé de 8 caractères max
    $key = 'Kalimero';  
	
	// Linéarise une variable
    $data = serialize($data);
	
	// Pour compliquer un peu la chaîne a crypter, on insère un "grain de sel"
	$data = '!' . $data;
	
	// Ouverture du module de l'algorithme et du mode à utiliser (chiffrement, mode)
    $td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_ECB, '');
	
	// Retourne la taille maximale de la clé pour un mode
	$key_size = mcrypt_enc_get_key_size($td);

	if (strlen($key) <= $key_size) { 
	
		// Retourne la taille du vecteur d'initialisation (IV) utilisé par le couple chiffrement/mode précédemment définit (ici 8)
		// NB : Un IV est un bloc de bits combiné avec le premier bloc de données lors d'une opération de chiffrement
		$iv_size = mcrypt_enc_get_iv_size($td);
		
		// Crée un vecteur d'initialisation de la taille requise par l'algorithme à partir d'une source aléatoire
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND); 
		
		// Initialise tous les buffers nécessaires
		mcrypt_generic_init($td, $key, $iv);
		
		// Chiffre les données
		$data = mcrypt_generic($td, $data);
		
		// Encode une chaîne en MIME base64
		$data = base64_encode($data);
		
		// Prépare le module pour le déchargement
		mcrypt_generic_deinit($td);
		
		return $data;
	}
	else return false;
}

function decrypt($data) {

    $key = 'Kalimero'; 
	
    $td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_ECB, '');
	
	$iv_size = mcrypt_enc_get_iv_size($td);
	
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND); 
	
    mcrypt_generic_init($td, $key, $iv);
	
	// Decode une chaîne en MIME base64
    $data = base64_decode($data);
	
	// Déchiffre les données
    $data = mdecrypt_generic($td, $data);
	
	// Supprime le caractère ajouté en debut
	if (substr($data, 0, 1) == '!') $data = substr($data, 1, strlen($data)-1);
	
	// Crée une variable PHP à partir d'une valeur linéarisée
	$data = unserialize($data);
	
	mcrypt_generic_deinit($td);
	
    return $data; 
}

/* ########################################################################################################## */

// Pour "crypter" les URL
function encryptURL($arg) {
	return urlencode(base64_encode($arg));
}

function decryptURL($arg) {
	return base64_decode(urldecode($arg));
}

/* ########################################################################################################## */

// Fonction pour hasher un mot de passe 
// Nécessite aussi le username pour éviter d'avoir le même hash pour 2 login qui utiliserai le même mot de passe
function doubleSalt($toHash, $username) {
	$password = str_split($toHash, (strlen($toHash)/2)+1); // On coupe le mot de passe en 2
	$hash = hash('md5', $username.$password[0] . 'ç^2L' . $password[1]); // Hash en insérant un "grain de sel" pour compliquer
	return $hash;
} 

// générer un mot de passe
function mpGenerate() {
	// Ensemble des caractères utilisés pour le créer
	$cars="abcdefghijklmnopqrstuvwxyz0123456789";
	// Combien on en a mis au fait ?
	$wlong=strlen($cars);
	// Au départ, il est vide ce mot de passe ;)
	$wpas="";
	// Combien on veut de caractères pour ce mot de passe ?
	$taille=6;
	// On initialise la fonction aléatoire
	srand((double)microtime()*1000000);
	// On boucle sur le nombre de caractères voulus
	for($i=0;$i<$taille;$i++){
	// Tirage aléatoire d'une valeur entre 1 et wlong
		  $wpos=rand(0,$wlong-1);
	// On cumule le caractère dans le mot de passe
		  $wpas=$wpas.substr($cars,$wpos,1);
	// On continue avec le caractère suivant à générer      
	}
	// On retourne le mot de passe généré
	return $wpas;
}

/* ########################################################################################################## */

// Fonction pour vérifier la structure d'une chaine
function isMail($email) {
	return preg_match('/^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$/i', $email) ? true : false;
}

function isLogin($login) {
	return preg_match('/^[A-Zéèêçàâî]{1}[A-Zéèêçàâî0-9._@-]{1,31}$/i', $login) ? true : false;
}

function isMobile($mobile) {
	return preg_match('/[0-9 ()-+\.\\/]{10,20}$/i', $mobile) ? true : false;
}

// Analyse le numéro de téléphone au format international
function convIntMobile($mobile) {

	// On nettoie la chaine (pour avoir une chaine du style +32497937340 ou 0497937340)
	$toReplace = array('.', ' ', '/', '\\', '-', '(', ')');
	$mobile = str_replace($toReplace, '', $mobile);
		
	// Vérification si préfixe (+32) et ajout sinon
	if (!preg_match('/^[+]{1}[0-9]{2,3}/i', $mobile)) 
		{
		// Si pas de préfixe doit commencer par 0
		if (preg_match('/^[0]{1}/i', $mobile)) return preg_replace ('/[0]/', '+32', $mobile, 1);
		else return false;
		}
	else return $mobile;
}

function isPhone($phone) {
	return preg_match('/[0-9 ()-+\.\\/]{9,19}$/i', $phone) ? true : false;
}

function isMP($mp) {
	return (strlen($mp) < 4) ? false : true;
}

/* ########################################################################################################## */
/* ################################            FICHIERS              ######################################## */
/* ########################################################################################################## */

// array of files without directories... optionally filtered by extension
function file_list($dir, $ext = NULL) {
	if (is_dir($dir)) { // Si le répertoire existe
		foreach(@array_diff(scandir($dir), array('.', '..')) as $file) if(is_file($dir . '/' . $file) && (($ext) ? ereg($ext . '$', $file) : true)) $list[] = $file;
		return $list;
	}
}

// array of directories
function dir_list($dir) {
	if (is_dir($dir)) { // Si le répertoire existe
		foreach(@array_diff(scandir($dir), array('.','..')) as $file) if(is_dir($dir . '/' . $file)) $list[] = $file;
		return $list;
	}
} 

// Liste les fichiers de tous les sous-dossiers
function all_file_list($dir) {
	if (is_dir($dir)) { // Si le répertoire existe
		$dir_list = dir_list($dir);
		if(is_array($dir_list)) { // Si un tableau est bien retourner
			foreach($dir_list as $sous_dir) { // Pour chaque dossier on va lister les fichiers
				$file_list = file_list($dir . '/' . $sous_dir);
				if(is_array($file_list)) foreach($file_list as $file) $list[] = $sous_dir . '/' . $file;
			}
		}
		return $list;
	}
}

/* ########################################################################################################## */

// Supprimer un répertoire et son contenu
// Pramètre : false = suppression uniquement de contenu et pas le répertoire lui-même
function clearDir($dossier, $deleteDir = true) {

	$ouverture = @opendir($dossier);
	if (!$ouverture) return;
	
	while($fichier = readdir($ouverture)) {
		if ($fichier == '.' || $fichier == '..') continue; 	// Passe directement au fichier suivant si le nom du fichier est . ou ..
			
			if (is_dir($dossier . '/' . $fichier)) { 		// Vérifie si le fichier n'est pas un (sous-)dossier
				$r = clearDir($dossier . '/' . $fichier); 	// Si c'est le cas, on réexécute ce script afin de vider d'abord ce sous-dossier
				if (!$r) return false;
			}
			else {
				$r = @unlink($dossier . '/' . $fichier); 	// Suppression des fichiers
				if (!$r) return false;
			}
	}
	
	closedir($ouverture);
	
	if ($deleteDir) {
		$r = @rmdir($dossier); 								// Efface le dossier (si celui-ci est vide)
		@rename($dossier, 'trash'); 						// Si impossible de supprimer le dossier on le renomme
	}
	
	return true;
}

/* ########################################################################################################## */

// Récupération de l'extension du fichier et association à un icone (nécessite le chemin complet du fichier)
function iconExtension($file) {

	$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	
	switch($ext) {
		case '3gp' 	: $icon_type = '3gp'; 	break;
		case 'ai' 	: $icon_type = 'ai'; 	break;
		case 'aiff' : $icon_type = 'aiff'; 	break;
		case 'avi' 	: $icon_type = 'avi'; 	break;
		case 'bmp' 	: $icon_type = 'bmp'; 	break;
		case 'gif' 	: $icon_type = 'gif'; 	break;
		case 'htm' 	: $icon_type = 'html'; 	break;
		case 'html' : $icon_type = 'html'; 	break;
		case 'jpg' 	: $icon_type = 'jpeg'; 	break;
		case 'mov' 	: $icon_type = 'mov'; 	break;
		case 'mp3' 	: $icon_type = 'mp3'; 	break;
		case 'mpg' 	: $icon_type = 'mpeg'; 	break;
		case 'pdf' 	: $icon_type = 'pdf'; 	break;
		case 'png' 	: $icon_type = 'png'; 	break;
		case 'ppt' 	: $icon_type = 'ppt'; 	break;
		case 'pps' 	: $icon_type = 'ppt'; 	break;
		case 'pptx' : $icon_type = 'ppt'; 	break;
		case 'ppsx'	: $icon_type = 'ppt'; 	break;
		case 'psd' 	: $icon_type = 'psd'; 	break;
		case 'rar' 	: $icon_type = 'rar'; 	break;
		case 'sit' 	: $icon_type = 'sit'; 	break;
		case 'tif' 	: $icon_type = 'tiff'; 	break;
		case 'tiff' : $icon_type = 'tiff'; 	break;
		case 'txt' 	: $icon_type = 'txt'; 	break;
		case 'wav' 	: $icon_type = 'wav'; 	break;
		case 'wma' 	: $icon_type = 'wma'; 	break;
		case 'wmv' 	: $icon_type = 'wmv'; 	break;
		case 'doc' 	: $icon_type = 'word'; 	break;
		case 'docx' : $icon_type = 'word'; 	break;
		case 'xml' 	: $icon_type = 'xml'; 	break;
		case 'xls' 	: $icon_type = 'xls'; 	break;
		case 'xlsx' : $icon_type = 'xls'; 	break;
		case 'zip' 	: $icon_type = 'zip'; 	break;
		default : $icon_type = 'file';
	}
	return $icon_type;
}

/* ########################################################################################################## */

/* GetExtensionName - Renvoie l'extension d'un fichier (nécessite juste le nom)
. $File (char): Nom du fichier
. $Dot  (bool): avec le point true/false */
function getExtension($file, $dot = false) {
	if ($dot == true)	$ext = strtolower(substr($file, strrpos($file, '.')));
	else				$ext = strtolower(substr($file, strrpos($file, '.') + 1));
	return $ext;
}

// Remplace la dernière occurence
function replace_last_occurrence($haystack, $needle) {
	$last = strrpos($haystack, $needle, 0);
	return substr_replace($haystack, '', $last, strlen($needle));
}

/* removeExtensionName - Renvoie le nom d'un fichier sans son extension (nécessite juste le nom)
. $File (char): Nom fichier */
function removeExtension($file) {
	$ext = '.' . strtolower(pathinfo($file, PATHINFO_EXTENSION));
	return strtolower(replace_last_occurrence($file, $ext));
}

/* ########################################################################################################## */

// Retourne un nouveau nom d'un fichier à copier (pour éviter d'écraser un fichier qui porterai déjà le même nom que le fichier à copier)
// ex : foo.txt -> foo copy.txt -> foo copy 1.txt -> foo copy 2.txt [etc]
// $orig = current name, of course
// $list = array of filenames in the target directory (if none given, it will still return a new name)
// $max = max length of filename
function duplicateName($orig, $list = array(), $addcopy = true, $max = 64) {
	
	$ext 		= '';
	$counter 	= 0;
	$list 		= (array) $list;
	$max 		= (int) $max;
	$newname 	= $orig;

	do {
		$name = $newname; # name in, newname out
		##########################################################################
		if (preg_match('/ copie$| copie \d+$/', $name, $matches)) {
			// don't even check for extension, name ends with " copy[ digits]"
			// preg hereunder matches anything with at least one period in the middle and an extension of 1-5 characters
		}
		elseif (preg_match('/(.+)\.([^.]{1,5})$/', $name, $parts)) {
			// split to name & extension
			list($name, $ext) = array($parts[1], $parts[2]);
		}
		############################## Nouveau nom ##############################
		if($addcopy == true) { /* Version orginal */
			if (preg_match('/ copie (\d+)$/', $name, $digits)) {
				$newname = substr($name, 0, - strlen($digits[1])) . ($digits[1] + 1);
				# $cutlen is only used for the bit at the end where it checks on max filename length
				$cutlen = 7 + strlen($digits[1]+1); // ' copie ' + digits
			}
			elseif(preg_match('/ copie$/', $name, $digits)) {
				$newname = $name . ' 1';
				$cutlen = 8; // ' copie' + ' 1'
			}
			else {
				$newname = $name . ' copie';
				$cutlen = 6; // ' copie'
			}
		}
		else { /* Juste un numéro */
			if (preg_match('/ (\d+)$/', $name, $digits)) {
				$newname = substr($name, 0, - strlen($digits[1])) . ($digits[1] + 1);
				$cutlen = 1 + strlen($digits[1]+1);
			}
			else {
				$newname = $name . ' 2';
				$cutlen = 2;
			}
		}
		##########################################################################
		if ($ext) {
			$newname .= '.' . $ext;
			$cutlen += strlen($ext) + 1;
		}
		##########################################################################
		if ($max > 0) {
			if (strlen($newname) > $max) {
				$newname = substr($newname, 0, max($max - $cutlen, 0)) . substr($newname, -$cutlen);
				if (strlen($newname) > $max) { echo "duplicate_name() erreur: Ne peut conserver le nom sous la longueur maximum.\n"; return false; }
			}
		}
		##########################################################################
		if ($counter++ > 999) { echo "duplicate_name() erreur: Trop de fichier portant un nom similaire.\n"; return false; }
		
		} while (in_array($newname, $list)); // Tant que le nom du fichier généré fichier existe, on continue

	return $newname;
} 

/* ########################################################################################################## */

/*
* La fonction raccourcirChaine() permet de réduire une chaine trop longue passée en paramètre.
* @param : string $chaine le texte trop long à tronquer
* @param : integer $tailleMax la taille maximale de la chaine tronquée
* @param : bool $fileName si la chaine est un nom de fichier
* @return : string
*/
function shortenString($chaine, $tailleMax, $fileName = false) {
	
	if(strlen($chaine) >= $tailleMax) {
		if ($fileName != true) {
			// On rogne tout ce qui dépasse
			$chaine = substr($chaine, 0, $tailleMax);
			// Vérification si il y a des espaces ou on peut couper la chaine sans tronquer au milieu d'un mot (pour ajouter les ...)
			if(strpos($chaine, ' ') == true) $positionDernierEspace = strrpos($chaine, ' ');
			else $positionDernierEspace = $tailleMax - 3;
			// On ajoute les ...
			$chaine = substr($chaine, 0, $positionDernierEspace) . '...';
		}
		else {
			// Récupération de l'extension et de sa taille
			$ext = getExtension($chaine, true);
			$extLen = strlen($ext);
			// On rogne tout ce qui dépasse
			$chaine = substr($chaine, 0, $tailleMax);
			// On ajout les ...
			$positionDernierEspace = $tailleMax - $extLen - 5;
			$chaine = substr($chaine, 0, $positionDernierEspace) . '[...]' . $ext;
		}
	}
 
	return $chaine;
}

/* ########################################################################################################## */

// Converti la taille d'un fichier d'une unité à une autre
// b -> Bit, o -> Octet, k -> Kilo Octet, m -> Mega Octet, g -> Giga Octet, t -> Tera Octet, p -> Peta Octet, e -> Exa Octet, z -> Zetta Octet, y -> Yotta Octet
// Paramètre facultatif qui indique l'unité destination (automatique par défaut)
// Paramètre facultatif qui indique l'unite de depart (octet par défaut)
// Parametre facultatif qui indique si vrai conversion (TRUE) (facteur 2^10 ou 1024) ou fausse "marketing" (FALSE) (facteur 10^3 ou 1000)
// Paramètre facultatif pour afficher l'unité en octet (= 'o'), en bytes (= 'b'), ou rien du tout (= NULL)
// Paramètre facultatif pour afficher l'unité avec le préfixe SI (Mo, Go, etc.) (= FALSE) ou Binaire (Mio, Gio, etc.) (= TRUE) (NB : fonctionne uniquement si conversion vrai)
// Paramètre facultatif reprenant le nombre de chiffres après la virgule (2 par défaut)
function convertSize($size, $from = 'o', $to = NULL, $bin = TRUE, $unit = 'o', $binUnit = FALSE, $round = 2) {

	// Liste des préfixes des unités prises en charge
	$prefixList = array('b','o','k','m','g','t','p','e','z','y');
	
	// Type (base 1024 = TRUE ou 1000 = FALSE)
	$bin == TRUE ? $base = bcpow(2, 10) : $base  = bcpow(10, 3);

 	// Si mauvaise valeur indiquée pour l'unité
	if ($unit != 'o' && $unit != 'b' && $unit != NULL) $unit = 'o';

	// Conversion de la taille en integer
	$size = intval($size);
	
	// On converti d'abord l'unité FROM en bytes
	switch($from) {
		case 'b': 	$size = $size / 8; 					break;
		case 'k': 	$size = $size * $base;				break;	
		case 'm': 	$size = $size * bcpow($base, 2);	break;
		case 'g': 	$size = $size * bcpow($base, 3);	break;
		case 't': 	$size = $size * bcpow($base, 4);	break;
		case 'p': 	$size = $size * bcpow($base, 5);	break;
		case 'e': 	$size = $size * bcpow($base, 6);	break;
		case 'z': 	$size = $size * bcpow($base, 7);	break;
		case 'y': 	$size = $size * bcpow($base, 8);	break;
		default: 	$from = 'o'; 						break; // En cas de mauvais paramètre
	}
			
	// Si pas d'unité TO, sélection automatique de la plus adéquate
	if(!in_array($to, $prefixList))	{
		switch($size) {
			case ($size < 1000) : 			$to = 'o'; 		break;
			case ($size < bcpow(1000, 2)) : $to = 'k';		break;
			case ($size < bcpow(1000, 3)) : $to = 'm';		break;
			case ($size < bcpow(1000, 4)) : $to = 'g';		break;
			case ($size < bcpow(1000, 5)) : $to = 't';		break;
			case ($size < bcpow(1000, 6)) : $to = 'p';		break;
			case ($size < bcpow(1000, 7)) : $to = 'e';		break;
			case ($size < bcpow(1000, 8)) : $to = 'z';		break;
			case ($size < bcpow(1000, 9)) : $to = 'y';		break;
			default: 						$to = 'y';		break; // Si on est encore plus haut
		}
	}
	
	// Conversion vers l'unité TO à partir des bytes
	switch($to)	{
		case 'b': $size = $size * 8;				$unit = 'bit';								break;
		case 'o': $size = $size;					$unit = $unit == 'b' ? 'bytes' : 'octets';	break;
		case 'k': $size = $size / $base;			$prefix = 'K';								break;
		case 'm': $size = $size / bcpow($base, 2);	$prefix = 'M';								break;
		case 'g': $size = $size / bcpow($base, 3);	$prefix = 'G';								break;
		case 't': $size = $size / bcpow($base, 4);	$prefix = 'T';								break;
		case 'p': $size = $size / bcpow($base, 5);	$prefix = 'P';								break;
		case 'e': $size = $size / bcpow($base, 6);	$prefix = 'E';								break;
		case 'z': $size = $size / bcpow($base, 7);	$prefix = 'Z';								break;
		case 'y': $size = $size / bcpow($base, 8);	$prefix = 'Y';								break;
	}

	// Unité (si à afficher)
	if ($unit != NULL) {
		if ($to != 'b' && $to != 'o') $size = number_format(round($size, intval($round)), intval($round)); 	// Arrondi à x chiffres après la virgule, et affichage effectif des x chiffres (ex : 6,399 arrondi à 2 chiffre -> 6,4 -> 6,40) NB : Si bit pas d'arrondi
		if ($unit == 'o') $size = str_replace('.', ',', $size);												// Si format français, utilisation de la virgule au lieu du point
		if ($binUnit == TRUE && $bin == TRUE) $prefix = $prefix . 'i';										// Si système Binaire ou SI (ex : Go ou Gio)
		if ($to != 'b' && $to != 'o') $unit = $prefix . $unit; 												// Si préfixe (ex : Go ou Gb)
		$size = $size . ' ' . $unit; 																		// Taille + Unité (ex : 10 Mo)
	}
	
	return $size;
}

function accentDelete($name) {
	return strtr($name, 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ', 'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
}

/* ########################################################################################################## */
/* ################################              UTIL                ######################################## */
/* ########################################################################################################## */

// Trie un tableau mais case insensitive
function asorti($arr) {
	$arr2 = $arr; // Copie du tableau original
	foreach($arr2 as $key => $val) { $arr2[$key] = strtolower($val); } // Tout en minuscules
 	asort($arr2); // Tri
	foreach($arr2 as $key => $val) { $arr2[$key] = $arr[$key]; } // Une fois le tableau trié, on remet la valeur originales (avec majuscules)
	return $arr2;
} 
?>
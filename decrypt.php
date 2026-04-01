<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Document sans titre</title>
</head>

<body>

<?php


$data = 'p4GrFeLfHkOyUaX2swZAUw==';

    $key = 'Kalimero'; 
	echo '1';
    $td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_ECB, '');
	
	$iv_size = mcrypt_enc_get_iv_size($td);
	
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND); 
	echo '2';
	
    mcrypt_generic_init($td, $key, $iv);
	
	// Decode une chaîne en MIME base64
    $data = base64_decode($data);
	
	// Déchiffre les données
    $data = mdecrypt_generic($td, $data);
	echo '3';
	// Supprime le caractère ajouté en debut
	if (substr($data, 0, 1) == '!') $data = substr($data, 1, strlen($data)-1);
	echo '4';
	// Crée une variable PHP à partir d'une valeur linéarisée
	$data = unserialize($data);
	
	mcrypt_generic_deinit($td);
	
echo 'resultat : '.$data;



?>
</body>
</html>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>

<script type="text/javascript" src="js/prototype.js"></script>
<script type="text/javascript" src="js/scriptaculous.js?load=effects,builder"></script>
<script type="text/javascript" src="js/lightbox.js"></script>

<link rel="stylesheet" href="css/lightbox.css" type="text/css" media="screen" />

</head>

<body>
<h1> Les diverses photos</h1>
<br />

<h3>2013</h3>
<?php
// ------- ouvre le dossier passé en paramètre (ici /chemin/vers/fichiers)
if ($handle = opendir('images/2013/')) {
    // ------- Initialise le compteur de gifs à 0
    $i = 0;
    // ------- On parcourt le répertoire.
    // ------- Tant qu'il y a des fichiers dans le répertoire, on passe dans la boucle pour chaque fichier
    while ($file = readdir($handle)) {
        // ------- Si avant l'extension du fichier nous avons pt, on incrémente
        if (substr($file,-7, 3) == "_pt") {
			
            // ------- on incrémente le compteur
            $i++;
        }
    }
    // ------- On ferme le répertoire
    closedir($handle);
    // ------- on affiche Les images
    
	for ($a = 1; $a <= $i; $a++) {
		
			echo '<a href="images/2013/'.$a.'.jpg" rel="lightbox[2013]"><img src="images/2013/'.$a.'_pt.jpg" alt="2013 '.$a.'" /></a>';
	
		}
	
	
	
	
} else {
    // ------- Si le dossier n'a pas pu être ouvert, affiche un message d'erreur
    echo "erreur d'ouverture du dossier";
}

?>
<hr width="150" />
<br />




<h3>2012</h3>
<?php
// ------- ouvre le dossier passé en paramètre (ici /chemin/vers/fichiers)
if ($handle = opendir('images/2012/')) {
    // ------- Initialise le compteur de gifs à 0
    $i = 0;
    // ------- On parcourt le répertoire.
    // ------- Tant qu'il y a des fichiers dans le répertoire, on passe dans la boucle pour chaque fichier
    while ($file = readdir($handle)) {
        // ------- Si avant l'extension du fichier nous avons pt, on incrémente
        if (substr($file,-7, 3) == "_pt") {
			
            // ------- on incrémente le compteur
            $i++;
        }
    }
    // ------- On ferme le répertoire
    closedir($handle);
    // ------- on affiche Les images
    
	for ($a = 1; $a <= $i; $a++) {
		
			echo '<a href="images/2012/'.$a.'.jpg" rel="lightbox[2012]"><img src="images/2012/'.$a.'_pt.jpg" alt="2012 '.$a.'" /></a>';
	
		}
	
	
	
	
} else {
    // ------- Si le dossier n'a pas pu être ouvert, affiche un message d'erreur
    echo "erreur d'ouverture du dossier";
}

?>
<hr width="150" />
<br />


<br />
<h3>Les événements passés</h3>
<?php
// ------- ouvre le dossier passé en paramètre (ici /chemin/vers/fichiers)
if ($handle = opendir('images/photos/')) {
    // ------- Initialise le compteur de gifs à 0
    $i = 0;
    // ------- On parcourt le répertoire.
    // ------- Tant qu'il y a des fichiers dans le répertoire, on passe dans la boucle pour chaque fichier
    while ($file = readdir($handle)) {
        // ------- Si avant l'extension du fichier nous avons pt, on incrémente
        if (substr($file,-7, 3) == "_pt") {
			
            // ------- on incrémente le compteur
            $i++;
        }
    }
    // ------- On ferme le répertoire
    closedir($handle);
    // ------- on affiche Les images
    
	for ($a = 1; $a <= $i; $a++) {
		
			echo '<a href="images/photos/'.$a.'.jpg" rel="lightbox[photos]"><img src="images/photos/'.$a.'_pt.jpg" alt="ACDM '.$a.'" /></a>';
	
		}
	
	
	
	
} else {
    // ------- Si le dossier n'a pas pu être ouvert, affiche un message d'erreur
    echo "erreur d'ouverture du dossier";
}

?>
<hr width="150" />
<br />




<h3>La Présidente et le bureau</h3>
<a href="images/bureau/1.JPG" rel="lightbox[BAL]"><img src="images/bureau/1_pt.JPG" alt="La présidente et le bureau" title="La présidente et le bureau" /></a>
<a href="images/bureau/2.JPG" rel="lightbox[BAL]"><img src="images/bureau/2_pt.JPG" alt="La présidente et le bureau" title="La présidente et le bureau" /></a>
<a href="images/bureau/3.JPG" rel="lightbox[BAL]"><img src="images/bureau/3_pt.JPG" alt="La présidente et le bureau" title="La présidente et le bureau" /></a>
<a href="images/bureau/4.JPG" rel="lightbox[BAL]"><img src="images/bureau/4_pt.JPG" alt="La présidente et le bureau" title="La présidente et le bureau" /></a>
<a href="images/bureau/5.JPG" rel="lightbox[BAL]"><img src="images/bureau/5_pt.JPG" alt="La présidente" title="La présidente" /></a>
<a href="images/bureau/6.JPG" rel="lightbox[BAL]"><img src="images/bureau/6_pt.JPG" alt="L'accueil : Cécile Leleux et Hélène Stanzos" title="L'accueil : Cécile Leleux et Hélène Stanzos" /></a>


<hr width="150" />
<br />




<h3>Le BAL</h3>
<a href="images/BAL/01.JPG" rel="lightbox[BAL]"><img src="images/BAL/01_pt.JPG" alt="Le BAL - Découverte" title="Le BAL - Découverte" /></a>
<a href="images/BAL/02.JPG" rel="lightbox[BAL]"><img src="images/BAL/02_pt.JPG" alt="Le BAL - Entrée" title="Le BAL - Entrée" /></a>
<a href="images/BAL/03.JPG" rel="lightbox[BAL]"><img src="images/BAL/03_pt.JPG" alt="Le BAL - Intérieur" title="Le BAL - Intérieur" /></a>
<a href="images/BAL/5.JPG" rel="lightbox[BAL]"><img src="images/BAL/5_pt.JPG" alt="Le BAL - Sculpture extérieure" title="Le BAL - Sculpture extérieure" /></a>
<a href="images/BAL/9.JPG" rel="lightbox[BAL]"><img src="images/BAL/9_pt.JPG" alt="Le BAL - Le cadre" title="Le BAL - Le cadre" /></a>
<a href="images/BAL/10.JPG" rel="lightbox[BAL]"><img src="images/BAL/10_pt.JPG" alt="Le BAL - La place" title="Le BAL - la place" /></a>
<hr width="150" />
<br />
<h3>Le MAMAC</h3>
<?php
// ------- ouvre le dossier passé en paramètre (ici /chemin/vers/fichiers)
if ($handle = opendir('images/MAMAC/')) {
    // ------- Initialise le compteur de gifs à 0
    $i = 0;
    // ------- On parcourt le répertoire.
    // ------- Tant qu'il y a des fichiers dans le répertoire, on passe dans la boucle pour chaque fichier
    while ($file = readdir($handle)) {
        // ------- Si avant l'extension du fichier nous avons pt, on incrémente
        if (substr($file,-7, 3) == "_pt") {
			
            // ------- on incrémente le compteur
            $i++;
        }
    }
    // ------- On ferme le répertoire
    closedir($handle);
    // ------- on affiche Les images
    
	for ($a = 1; $a <= $i; $a++) {
		
			echo '<a href="images/MAMAC/'.$a.'.jpg" rel="lightbox[MAMAC]"><img src="images/MAMAC/'.$a.'_pt.jpg" alt="Le MAMAC '.$a.'" /></a>';
	
		}
	
	
	
	
} else {
    // ------- Si le dossier n'a pas pu être ouvert, affiche un message d'erreur
    echo "erreur d'ouverture du dossier";
}

?>
<hr width="150" />
<br />

</body>
</html>

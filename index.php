<?php
// Session créée ? Sinon, en créer une nouvelle
if($PHPSESSID) session_start($PHPSESSID);
else session_start();

// On inclu les infos de connection à la DB, etc.
require_once('scripts/config.inc.php'); 
include_once('scripts/function.inc.php');
require_once('scripts/jsonwrapper.php'); 

// Redirection vers la page d'accueil, si aucune page précisée
$url_page = $_SERVER["REQUEST_URI"]; // URL (avec arguments)
if(!eregi('.html', $url_page))	{
	header('Location: accueil/index.html');
	// echo '<meta http-equiv="Refresh" content="0;url=accueil/index.html">'
	exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Gestion financi&egrave;re</title>

	<script type="text/javascript" src="../scripts/function.js"></script>
    
	<script type="text/javascript" src="../scripts/inherited/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="../scripts/inherited/shadowbox.js"></script>
	<script type="text/javascript" src="../scripts/inherited/jquery-ui-1.7.2.custom.min.js"></script>
	<script type="text/javascript" src="../scripts/inherited/jquery.ui.widget.js"></script>
	<script type="text/javascript" src="../scripts/inherited/jquery.ui.mouse.js"></script>
	<script type="text/javascript" src="../scripts/inherited/jquery.ui.sortable.js"></script>
	<script type="text/javascript" src="../scripts/inherited/ui.datepicker-fr.js"></script>
	<script type="text/javascript" src="../scripts/inherited/history.js"></script>
	<script type="text/javascript" src="../scripts/inherited/jquery.alerts.js"></script>
	<script type="text/javascript" src="../scripts/inherited/jquery.tooltip.js"></script>
	<script type="text/javascript" src="../scripts/inherited/jquery.elastic.source.js"></script>
    <script type="text/javascript" src="../scripts/inherited/jquery.autocomplete.min.js"></script>	
	<script type="text/javascript" src="../scripts/inherited/fonctions.js"></script>
    <script type="text/javascript" src="../scripts/inherited/jquery.validate.js"></script>
    <script type="text/javascript" src="../scripts/inherited/additional-methods.js"></script>
    


</head>

<body>
<?php
// Je relance mes variable de sessions pour qu'elles restent bien "fraiches" ;)
if (isset($_SESSION['ent_tva'])) {
$_SESSION['ent_tva'] = $_SESSION['ent_tva'];
}
?>    

<div id="contentpanel">
		<?php require_once('include/content.php'); ?>
	</div>

</body>
</html>
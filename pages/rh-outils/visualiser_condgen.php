<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>
</head>

<body>
<?php
if (file_exists('./images/condgen/'.$_SESSION['ent_affil'].'_condgen.jpg'))
	{
		echo '
		<div class="cond_gen">
		<img src="../images/condgen/'.$_SESSION['ent_affil'].'_condgen.jpg" style="height : '.$_SESSION['condGen']['hauteur'].'cm ; width : '.$_SESSION['condGen']['largeur'].'cm; margin-left:'.$_SESSION['condGen']['marGauche'].'cm; margin-top:'.$_SESSION['condGen']['marHaut'].'cm;" />
		</div>';
	}
?>
</body>
</html>
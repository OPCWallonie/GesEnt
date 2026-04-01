<link rel="stylesheet" type="text/css" href="../index.css" media="all" />
</head>
<body>

<div id="menuh">

	<ul>
    <li>
    	<a href="../accueil/index.html" title="Retour à l'accueil du tableau de bord">
        	<img src="../images/home_32.png" alt="Accueil du tableau de bord" />
        </a>			
    </li>
    
    <?php
	
// Je formate le nom en remplaçant les underscores par des espaces
$r = ucfirst($_GET['r']);
$p = ucfirst($_GET['p']);
$p = str_replace('_', ' ', $p);


	if ($p != "Index")
		{
			echo'<li><span>' . $p . '</span></li>';	
		}
	else {
			echo '<li><span>' . $r . '</span></li>';
		}
	?>

    </ul>

</div>

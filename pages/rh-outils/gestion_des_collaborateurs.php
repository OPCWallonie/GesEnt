<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>Gestion des collaborateurs</title>
<meta name="keywords" content="" lang="fr">
<meta name="description" content="">

<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!--[if IE]><script language="javascript" type="text/javascript" src="http://www.wuro.fr/static/js/excanvas.js"></script><![endif]-->	

<link rel="stylesheet" type="text/css" href="../css/gestion_collabos.css" media="all">
<style type="text/css">
.topcol
{
	text-align: left;
	float: left;
	margin-top:auto;
	margin-bottom:auto;
}

.abold {
	font-size:14px;
	font-weight:bold;
}
</style>

</head>
<body>

<?php
	include ("./menu.php");
?>

<div id="contenant">
	<div class="box">
		<h2>Gestion des collaborateurs</h2>
			
		<table cellspacing="0">
            <tbody>
            	<tr>
                	<th colspan="4" class="aleft">Estimation du salaire moyen</th>									
            	</tr>
            
            	<tr>
                    <td width="33%"><strong>Nombre de jours ouvrables</strong></td>
					<td class="aleft" style="text-align:left;" width="55px">
                    	<input type="text" id="nbrJours" value="200" size="6" class="calcHeuresTot calcChantier" />
                    </td>
                    <td colspan="2" class="aleft">jours par an</td>
         		</tr>
                <tr>
                    <td width="33%"><strong>Nombre d'heures prest&eacute;es par jour - ouvrier</strong></td>
					<td class="aleft" style="text-align:left;" width="55px">
                    	<input type="text" id="nbrHeuresOuvrier" value="8" size="2" class="calcHeuresTot calcChantier" />
                    </td>
                    <td colspan="2" class="aleft">heures par jour</td>
         		</tr>
                <tr>
                    <td width="33%"><strong>Nombre d'heures prest&eacute;es par jour - patron</strong></td>
					<td class="aleft" style="text-align:left;" width="55px">
                    	<input type="text" id="nbrHeuresPatron" value="8" size="2" class="calcHeuresTot calcChantier" />
                    </td>
                    <td colspan="2" class="aleft">heures par jour</td>
         		</tr>
                <tr>
                	<th class="aright">Total heures prestées par ouvrier par an :</th>
                    <th class="aleft"><span class="heuresTotOuvrier"></span></th>
                    <th class="aright">Total heures prestées par patron par an :</th>
                    <th class="aleft"><span class="heuresTotPatron"></span></th>               
                </tr>
   			</tbody>
		</table>	
		
        
        <p class="toggle open">
			<a href="javascript:visibilite('chantier');" class="options_chantier">Chantier</a>
		</p>
			
		<div id="chantier" style="display:block">        
        <table cellspacing="0">
            <tbody>
                <tr>
                	<th width="300px">Qualification</th>
                    <th class="aleft"  width="100px">Salaire brut</th>
                    <th class="aleft"  width="100px">Nombre</th>
                    <th class="acenter"  width="200px">Total par qualification</th>									
            	</tr>
            
            	<tr>
                    <td>Catégorie I ( Manoeuvre )</td>
					<td class="aleft" style="text-align:left;">
                    	<input type="text" id="salaireI" value="12.43" size="5" class="calcChantier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="valeurI" value="0.00" size="6" class="calcHeuresTot calcChantier" />
                    </td>
                    <td class="acenter"><span id="resultI"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Catégorie IA ( Premier manoeuvre )</td>
					<td class="aleft">
                    	<input type="text" id="salaireIA" value="13.05" size="5" class="calcChantier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="valeurIA" value="0.00" size="6" class="calcHeuresTot calcChantier" />
                    </td>
                    <td class="acenter"><span id="resultIA"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Catégorie II ( Spécialisé )</td>
					<td class="aleft">
                    	<input type="text" id="salaireII" value="13.25" size="5" class="calcChantier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="valeurII" value="0.00" size="6" class="calcHeuresTot calcChantier" />
                    </td>
                    <td class="acenter"><span id="resultII"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Catégorie IIA ( Spécialisé d'élite )</td>
					<td class="aleft">
                    	<input type="text" id="salaireIIA" value="13.25" size="5" class="calcChantier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="valeurIIA" value="0.00" size="6" class="calcHeuresTot calcChantier" />
                    </td>
                    <td class="acenter"><span id="resultIIA"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Catégorie III ( Premier échelon )</td>
					<td class="aleft">
                    	<input type="text" id="salaireIII" value="14.09" size="5" class="calcChantier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="valeurIII" value="0.00" size="6" class="calcHeuresTot calcChantier" />
                    </td>
                    <td class="acenter"><span id="resultIII"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Catégorie IV ( Deuxième échelon )</td>
					<td class="aleft">
                    	<input type="text" id="salaireIV" value="14.96" size="5" class="calcChantier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="valeurIV" value="0.00" size="6" class="calcHeuresTot calcChantier" />
                    </td>
                    <td class="acenter"><span id="resultIV"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Chef d'équipe A</td>
					<td class="aleft">
                    	<input type="text" id="salaireA" value="15.50" size="5" class="calcChantier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="valeurA" value="0.00" size="6" class="calcHeuresTot calcChantier" />
                    </td>
                    <td class="acenter"><span id="resultA"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Chef d'équipe B</td>
					<td class="aleft">
                    	<input type="text" id="salaireB" value="16.45" size="5" class="calcChantier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="valeurB" value="0.00" size="6" class="calcHeuresTot calcChantier" />
                    </td>
                    <td class="acenter"><span id="resultB"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Contrema&icirc;tre</td>
					<td class="aleft">
                    	<input type="text" id="salaireC" value="17.95" size="5" class="calcChantier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="valeurC" value="0.00" size="6" class="calcHeuresTot calcChantier" />
                    </td>
                    <td class="acenter"><span id="resultC"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Patron ( ouvrier )</td>
					<td class="aleft">
                    	<input type="text" id="salaireP" value="20" size="5" class="calcChantier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="valeurP" value="0.00" size="6" class="calcHeuresTot calcChantier" />
                    </td>
                    <td class="acenter"><span id="resultP"></span>&nbsp;&euro;</td>
         		</tr>
                
                <!-- Et le bloc des totaux -->
                
                <tr>
                	<th colspan="2" class="aright">Nombre</th>
                    <td><span id="cTotNombre">5</span></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                	<th colspan="2" class="aright">Total salaire brut</th>
                    <td><span id="cTotSalBrut"></span>&nbsp;&euro;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                	<th colspan="2" class="aright">Salaire horaire moyen brut</th>
                    <td><span id="cTotSalMoyBrut"></span>&nbsp;&euro;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                	<th colspan="2" class="aright">Salaire horaire moyen avec charges sociales</th>
                    <td><span id="cTotSalMoyCC"></span>&nbsp;&euro;</td>
                    <td>&nbsp;</td>
                </tr>
   			</tbody>
		</table>
        </div> <!-- fin du div "chantier" -->	
        
        
        
        <p class="toggle close">
			<a href="javascript:visibilite('atelier');" class="options_atelier">Atelier</a>
		</p>
			
		<div id="atelier" style="display:none">        
        <table cellspacing="0">
            <tbody>
                <tr>
                	<th width="300px">Qualification</th>
                    <th class="aleft"  width="100px">Salaire brut</th>
                    <th class="aleft"  width="100px">Nombre</th>
                    <th class="acenter"  width="200px">Total par qualification</th>									
            	</tr>
            
            	<tr>
                    <td>Catégorie I ( Manoeuvre )</td>
					<td class="aleft" style="text-align:left;">
                    	<input type="text" id="asalaireI" value="12.43" size="5" class="calcAtelier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="avaleurI" value="0.00" size="6" class="calcAtelier" />
                    </td>
                    <td class="acenter"><span id="aresultI"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Catégorie IA ( Premier manoeuvre )</td>
					<td class="aleft">
                    	<input type="text" id="asalaireIA" value="13.05" size="5" class="calcAtelier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="avaleurIA" value="0.00" size="6" class="calcAtelier" />
                    </td>
                    <td class="acenter"><span id="aresultIA"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Catégorie II ( Spécialisé )</td>
					<td class="aleft">
                    	<input type="text" id="asalaireII" value="13.25" size="5" class="calcAtelier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="avaleurII" value="0.00" size="6" class="calcAtelier" />
                    </td>
                    <td class="acenter"><span id="aresultII"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Catégorie IIA ( Spécialisé d'élite )</td>
					<td class="aleft">
                    	<input type="text" id="asalaireIIA" value="13.25" size="5" class="calcAtelier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="avaleurIIA" value="0.00" size="6" class="calcAtelier" />
                    </td>
                    <td class="acenter"><span id="aresultIIA"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Catégorie III ( Premier échelon )</td>
					<td class="aleft">
                    	<input type="text" id="asalaireIII" value="14.09" size="5" class="calcAtelier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="avaleurIII" value="0.00" size="6" class="calcAtelier" />
                    </td>
                    <td class="acenter"><span id="aresultIII"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Catégorie IV ( Deuxième échelon )</td>
					<td class="aleft">
                    	<input type="text" id="asalaireIV" value="14.96" size="5" class="calcAtelier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="avaleurIV" value="0.00" size="6" class="calcAtelier" />
                    </td>
                    <td class="acenter"><span id="aresultIV"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Chef d'équipe A</td>
					<td class="aleft">
                    	<input type="text" id="asalaireA" value="15.50" size="5" class="calcAtelier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="avaleurA" value="0.00" size="6" class="calcAtelier" />
                    </td>
                    <td class="acenter"><span id="aresultA"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Chef d'équipe B</td>
					<td class="aleft">
                    	<input type="text" id="asalaireB" value="16.45" size="5" class="calcAtelier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="avaleurB" value="0.00" size="6" class="calcAtelier" />
                    </td>
                    <td class="acenter"><span id="aresultB"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Contrema&icirc;tre</td>
					<td class="aleft">
                    	<input type="text" id="asalaireC" value="17.95" size="5" class="calcAtelier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="avaleurC" value="0.00" size="6" class="calcAtelier" />
                    </td>
                    <td class="acenter"><span id="aresultC"></span>&nbsp;&euro;</td>
         		</tr>
                <tr>
                    <td>Patron ( ouvrier )</td>
					<td class="aleft">
                    	<input type="text" id="asalaireP" value="20" size="5" class="calcAtelier" />&nbsp;&euro;
                    </td>
                    <td class="aleft">
                    	<input type="text" id="avaleurP" value="0.00" size="6" class="calcAtelier" />
                    </td>
                    <td class="acenter"><span id="aresultP"></span>&nbsp;&euro;</td>
         		</tr>
                
                <!-- Et le bloc des totaux -->
                
                <tr>
                	<th colspan="2" class="aright">Nombre</th>
                    <td><span id="aTotNombre">0</span></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                	<th colspan="2" class="aright">Total salaire brut</th>
                    <td><span id="aTotSalBrut"></span>&nbsp;&euro;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                	<th colspan="2" class="aright">Salaire horaire moyen brut</th>
                    <td><span id="aTotSalMoyBrut"></span>&nbsp;&euro;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                	<th colspan="2" class="aright">Salaire horaire moyen avec charges sociales</th>
                    <td><span id="aTotSalMoyCC"></span>&nbsp;&euro;</td>
                    <td>&nbsp;</td>
                </tr>
                
   			</tbody>
		</table>
        </div> <!-- fin du div "atelier" -->	
        
        
        
        
        <table cellspacing="0">
            <tbody>
                <tr>
                	<th width="400px">&nbsp;</th>
               		<th class="acenter"  width="150px">Sans charges sociales</th>
                    <th class="acenter"  width="150px">Avec charges sociales</th>									
            	</tr>
                <tr>
                	<td class="aright">Masse salariale hors patron indépendant ( chantier + atelier )</td>
                    <td><span id="masseSalSCS">&nbsp;</span>&nbsp;&euro;</td>
                    <td><span id="masseSalACS">&nbsp;</span>&nbsp;&euro;</td>
                </tr>
                <tr>
                	<td>
                    	<input type= "radio" name="approche" id="approcheG" value="generale" class="radio" /> Approche générale -&nbsp;
                    	<input type= "radio" name="approche" id="approcheS" value="specifique" checked="checked" class="radio" /> Approche spécifique  
						</td>
                    <th>Approche générale</th>
                    <th>Approche spécifique</th>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                     <td><span id="totalGen">&nbsp;</span>&nbsp;%</td>
                      <td><span id="totalStruct">&nbsp;</span>&nbsp;%</td>
                </tr>
                <tr>
                	<td class="aleft">A. Charges sociales ONSS</td>
                    <td><input type="text" id="genA" value="38.97" size="5" class="totalComplet" />&nbsp;%</td>
                    <td><span id="speA">&nbsp;</span>&nbsp;%</td>
                </tr>
                <tr>
                	<td class="aleft">B. Charges sociales F.S.E</td>
                    <td><input type="text" id="genB" value="14.77" size="5" class="totalComplet" />&nbsp;%</td>
                    <td><span id="speB">&nbsp;</span>&nbsp;%</td>
                </tr>
                <tr>
                	<td class="aleft">C. ONSS pécule simple</td>
                    <td><input type="text" id="genC" value="3.75" size="5" class="totalComplet" />&nbsp;%</td>
                    <td><span id="speC">&nbsp;</span>&nbsp;%</td>
                </tr>
                <tr>
                	<td class="aleft">D. Cotisations forfaitaires F.S.E</td>
                    <td><input type="text" id="genD" value="12.94" size="5" class="totalComplet" />&nbsp;%</td>
                    <td><span id="speD">&nbsp;</span>&nbsp;%</td>
                </tr>
                <tr>
                	<td class="aleft">E. Réductions structurelles</td>
                    <td><input type="text" id="genE" value="-6.87" size="6" class="totalComplet" />&nbsp;%</td>
                    <td><span id="speE">&nbsp;</span>&nbsp;%</td>
                </tr>
                <tr>
                	<td class="aleft">F. Charges sociales générales</td>
                    <td><input type="text" id="genF" value="42.89" size="5" class="totalComplet" />&nbsp;%</td>
                    <td><span id="speF">&nbsp;</span>&nbsp;%</td>
                </tr>
                
                <tr>
                	<th>&nbsp;</th>
                    <th class="acenter">% annuel</th>
                    <th class="acenter">Montant annuel</th>
                </tr>
                
                <tr>
                	<td class="aright">V&ecirc;tements de travail</td>
                    <td class="aleft"><input type="text" id="F1" value="1.40" size="5" class="calcTabAS totalComplet" />&nbsp;%</td>
                    <td><span id="mF1"></span>&nbsp;&euro;</td>
                </tr>
                <tr>
                	<td class="aright">Assurances accidents du travail</td>
                    <td class="aleft"><input type="text" id="F2" value="10.20" size="5" class="calcTabAS totalComplet" />&nbsp;%</td>
                    <td><span id="mF2"></span>&nbsp;&euro;</td>
                </tr>
                <tr>
                	<td class="aright">Assurances responsabilité civile</td>
                    <td class="aleft"><input type="text" id="F3" value="0.50" size="5" class="calcTabAS totalComplet" />&nbsp;%</td>
                    <td><span id="mF3"></span>&nbsp;&euro;</td>
                </tr>
                <tr>
                	<td class="aright">Protection du travail</td>
                    <td class="aleft"><input type="text" id="F4" value="0.60" size="5" class="calcTabAS totalComplet" />&nbsp;%</td>
                    <td><span id="mF4"></span>&nbsp;&euro;</td>
                </tr>
                <tr>
                	<td class="aright">M&eacute;decine du travail</td>
                    <td class="aleft"><input type="text" id="F5" value="0.48" size="5" class="calcTabAS totalComplet" />&nbsp;%</td>
                    <td><span id="mF5"></span>&nbsp;&euro;</td>
                </tr>
                <tr>
                	<td class="aright">jours fériés</td>
                    <td class="aleft"><input type="text" id="F6" value="8.90" size="5" class="calcTabAS totalComplet" />&nbsp;%</td>
                    <td><span id="mF6"></span>&nbsp;&euro;</td>
                </tr>
                <tr>
                	<td class="aright">R&eacute;g. Intemp&eacute;ries</td>
                    <td class="aleft"><input type="text" id="F7" value="2.27" size="5" class="calcTabAS totalComplet" />&nbsp;%</td>
                    <td><span id="mF7"></span>&nbsp;&euro;</td>
                </tr>
                <tr>
                	<td class="aright">Prime de fid&eacute;lit&eacute;</td>
                    <td class="aleft"><input type="text" id="F8" value="9.85" size="5" class="calcTabAS totalComplet" />&nbsp;%</td>
                    <td><span id="mF8"></span>&nbsp;&euro;</td>
                </tr>
        		<tr>
                	<td class="aright">Mode paiement cot. ONSS</td>
                    <td class="aleft"><input type="text" id="F9" value="0.34" size="5" class="calcTabAS totalComplet" />&nbsp;%</td>
                    <td><span id="mF9"></span>&nbsp;&euro;</td>
                </tr>
                <tr>
                	<td class="aright">Divers</td>
                    <td class="aleft"><input type="text" id="F10" value="1.96" size="5" class="calcTabAS totalComplet" />&nbsp;%</td>
                    <td><span id="mF10"></span>&nbsp;&euro;</td>
                </tr>
                <tr>
                	<td class="aright">D&eacute;placements</td>
                    <td class="aleft"><input type="text" id="F11" value="2.71" size="5" class="calcTabAS totalComplet" />&nbsp;%</td>
                    <td><span id="mF11"></span>&nbsp;&euro;</td>
                </tr>
                <tr>
                	<td class="aright">Mobilit&eacute;</td>
                    <td class="aleft"><input type="text" id="F12" value="2.60" size="5" class="calcTabAS totalComplet" />&nbsp;%</td>
                    <td><span id="mF12"></span>&nbsp;&euro;</td>
                </tr>
        		<tr>
                	<td class="aright">R&eacute;ajustement cotisation ONSS</td>
                    <td class="aleft"><input type="text" id="F13" value="0.63" size="5" class="calcTabAS totalComplet" />&nbsp;%</td>
                    <td><span id="mF13"></span>&nbsp;&euro;</td>
                </tr>
          	</tbody>
      	</table>
        
        <br />
        
        <table>
        	<tbody>
            	<tr>
                	<th colspan="3">Estimation des heures productives</th>
                </tr>
                <tr>
                	<td width="50%" class="aright">Pourcentage des heures non productives</td>
                    <td width="5%" class="acenter abold"><span id="test" class="plusNbr">0</span><input id="plusNbr" type="hidden" /></td>
                    <td width="45%" class="aleft"><img src="../images/rondHaut.png" id="rondHaut" class="topcol" /></td>
                </tr>
                <tr>
                	<td class="aright">Pourcentage des heures productives</td>
                    <td class="acenter abold"><span class="moinsNbr">100</span><input id="moinsNbr" type="hidden" /></td>
                    <td class="aleft"><img src="../images/rondBas.png" id="rondBas"  class="topcol" /></td>
                </tr>
                <tr>
                	<th class="aright">Heures non-productives par an</th>
                    <th colspan="2" class="aleft"><span id="heuresNP"></span>&nbsp; sur <span class="heuresTot"></span></th>
                </tr>
                <tr>
                	<th class="aright">Heures productives par an ( patron + ouvriers )</th>
                    <th colspan="2" class="aleft"><span id="heuresP"></span>&nbsp; sur <span class="heuresTot"></span></th>
                </tr>
         	</tbody>
    	</table>
        
        
	</div>	
</div>
<?php
	include('./footer.php');
?>

</body>
<script language="javascript" type="text/javascript">
$( document ).ready(function() {
        
		calcOuvrierChantier();
		calcOuvrierAtelier();
		
		tableauCharges();
		totalComplet();
		
		calcTabAS(); // A besoin du total complet...
		calcAutres();
		
		
});
			



$("#rondHaut").click(function(e) {
	e.preventDefault();
	val = parseInt($('.plusNbr').text(),10);
	if((val < 100)&&(val >= 0)) {
		$('.plusNbr').html(parseInt($('.plusNbr').html()) + parseInt('1'));
		$('.moinsNbr').html(parseInt($('.moinsNbr').html()) - parseInt('1'));
	}
	calcAutres();
}); 
	
$("#rondBas").click(function(e) {
	e.preventDefault();
	val = parseInt($('.moinsNbr').text(),10);
	if((val < 100)&&(val >= 0)) {
		$('.moinsNbr').html(parseInt($('.moinsNbr').html()) + parseInt('1'));
		$('.plusNbr').html(parseInt($('.plusNbr').html()) - parseInt('1'));
	}
	calcAutres();
}); 
	
// Corrige à la volée toutes les virgules en points
$("input").bind("blur", function(){
	var valeur = this.value;
    this.value = valeur.replace(',','.');
});

// Si on met à jour un pourcentage, on recalcule
$(".totalComplet").blur(function(e) {
	tableauCharges();
	totalComplet(); // Recalcule les données des totaux
});

// Si on passe de méthode générale à méthode spécifique
$(".radio").click(function(e) {
	totalComplet();
});

/************************************************************************************************
		 Ci-dessous : Calcul du module "Ouvrier sur Chantier"... Je recalcule tous les champs 
************************************************************************************************/
	
	
	$(".calcChantier").blur(function(e) {
		e.preventDefault();
		calcOuvrierChantier(); // Recalcule les données du chantier
		calcAutres();
		totalComplet(); // Recalcule les données des totaux
		calcTabAS(); // Tableau en bas ( Requis pour Approche Spécifique )
		
		
	});
	
		
		
/************************************************************************************************
		 Ci-dessous : Calcul du module "Ouvrier d'Atelier"... Je recalcule tous les champs 
************************************************************************************************/
	
	
	$(".calcAtelier").blur(function(e) {
		e.preventDefault();
		calcOuvrierAtelier();
		calcAutres(); // Totaux ( nombres d'heures par exemple )
		totalComplet(); // Recalcule les données des totaux
		calcTabAS(); // Tableau en bas ( Requis pour Approche Spécifique )
		
	});	
	
	$(".calcTabAS").blur(function(e) {
		e.preventDefault();
		calcTabAS(); // Tableau en bas ( Requis pour Approche Spécifique )
		tableauCharge();
		totalComplet();
		
	});	 



/************************************************************************************************
		 FONCTION : Calcul le tableau chantier... Et met à jour les autres données 
************************************************************************************************/
function calcOuvrierChantier(){
$('#resultI').html(Math.round(parseFloat($('#salaireI').val()) * parseFloat($('#valeurI').val()) * 100 ) / 100 );
		
		$('#resultIA').html(Math.round(parseFloat($('#salaireIA').val()) * parseFloat($('#valeurIA').val()) * 100 ) / 100 );
		
		$('#resultII').html(Math.round(parseFloat($('#salaireII').val()) * parseFloat($('#valeurII').val()) * 100 ) / 100 );
		
		$('#resultIIA').html(Math.round(parseFloat($('#salaireIIA').val()) * parseFloat($('#valeurIIA').val()) * 100 ) / 100 );
		
		$('#resultIII').html(Math.round(parseFloat($('#salaireIII').val()) * parseFloat($('#valeurIII').val()) * 100 ) / 100 );
		
		$('#resultIV').html(Math.round(parseFloat($('#salaireIV').val()) * parseFloat($('#valeurIV').val()) * 100 ) / 100 );
		
		$('#resultA').html(Math.round(parseFloat($('#salaireA').val()) * parseFloat($('#valeurA').val()) * 100 ) / 100 );
		
		$('#resultB').html(Math.round(parseFloat($('#salaireB').val()) * parseFloat($('#valeurB').val()) * 100 ) / 100 );
		
		$('#resultC').html(Math.round(parseFloat($('#salaireC').val()) * parseFloat($('#valeurC').val()) * 100 ) / 100 );
		
		$('#resultP').html(Math.round(parseFloat($('#salaireP').val()) * parseFloat($('#valeurP').val()) * 100 ) / 100 );
		
		$('#cTotNombre').html(parseFloat($('#valeurI').val()) + parseFloat($('#valeurIA').val()) + parseFloat($('#valeurII').val()) + parseFloat($('#valeurIIA').val()) + parseFloat($('#valeurIII').val()) + parseFloat($('#valeurIV').val()) + parseFloat($('#valeurA').val()) + parseFloat($('#valeurB').val()) + parseFloat($('#valeurC').val()) + parseFloat($('#valeurP').val()));
		
		$('#cTotSalBrut').html(Math.round((parseFloat($('#resultI').html()) + parseFloat($('#resultIA').html()) + parseFloat($('#resultII').html()) + parseFloat($('#resultIIA').html()) + parseFloat($('#resultIII').html()) + parseFloat($('#resultIV').html()) + parseFloat($('#resultA').html()) + parseFloat($('#resultB').html()) + parseFloat($('#resultC').html()) + parseFloat($('#resultP').html())) * 100) / 100 );
		
		if($('#cTotNombre').html() != '0'){
			$('#cTotSalMoyBrut').html( Math.round (( parseFloat ( $('#cTotSalBrut').html()) / parseFloat($('#cTotNombre').html())) *100 ) / 100 );
		}
		else {
			$('#cTotSalMoyBrut').html('0');
		}

}

function calcOuvrierAtelier(){

		$('#aresultI').html(Math.round(parseFloat($('#asalaireI').val()) * parseFloat($('#avaleurI').val()) * 100 ) / 100 );
		
		$('#aresultIA').html(Math.round(parseFloat($('#asalaireIA').val()) * parseFloat($('#avaleurIA').val()) * 100 ) / 100 );
		
		$('#aresultII').html(Math.round(parseFloat($('#asalaireII').val()) * parseFloat($('#avaleurII').val()) * 100 ) / 100 );
		
		$('#aresultIIA').html(Math.round(parseFloat($('#asalaireIIA').val()) * parseFloat($('#avaleurIIA').val()) * 100 ) / 100 );
		
		$('#aresultIII').html(Math.round(parseFloat($('#asalaireIII').val()) * parseFloat($('#avaleurIII').val()) * 100 ) / 100 );
		
		$('#aresultIV').html(Math.round(parseFloat($('#asalaireIV').val()) * parseFloat($('#avaleurIV').val()) * 100 ) / 100 );
		
		$('#aresultA').html(Math.round(parseFloat($('#asalaireA').val()) * parseFloat($('#avaleurA').val()) * 100 ) / 100 );
		
		$('#aresultB').html(Math.round(parseFloat($('#asalaireB').val()) * parseFloat($('#avaleurB').val()) * 100 ) / 100 );
		
		$('#aresultC').html(Math.round(parseFloat($('#asalaireC').val()) * parseFloat($('#avaleurC').val()) * 100 ) / 100 );
		
		$('#aresultP').html(Math.round(parseFloat($('#asalaireP').val()) * parseFloat($('#avaleurP').val()) * 100 ) / 100 );
		
		$('#aTotNombre').html(parseFloat($('#avaleurI').val()) + parseFloat($('#avaleurIA').val()) + parseFloat($('#avaleurII').val()) + parseFloat($('#avaleurIIA').val()) + parseFloat($('#avaleurIII').val()) + parseFloat($('#avaleurIV').val()) + parseFloat($('#avaleurA').val()) + parseFloat($('#avaleurB').val()) + parseFloat($('#avaleurC').val()) + parseFloat($('#avaleurP').val()));
		
		$('#aTotSalBrut').html(Math.round((parseFloat($('#aresultI').html()) + parseFloat($('#aresultIA').html()) + parseFloat($('#aresultII').html()) + parseFloat($('#aresultIIA').html()) + parseFloat($('#aresultIII').html()) + parseFloat($('#aresultIV').html()) + parseFloat($('#aresultA').html()) + parseFloat($('#aresultB').html()) + parseFloat($('#aresultC').html()) + parseFloat($('#aresultP').html())) * 100) / 100 );
		
		if($('#aTotNombre').html() != '0'){
			$('#aTotSalMoyBrut').html( Math.round (( parseFloat ( $('#aTotSalBrut').html()) / parseFloat($('#aTotNombre').html())) *100 ) / 100 );
		}
		else {
			$('#aTotSalMoyBrut').html('0');
		}
		
		
	
}



	
	
/************************************************************************************************
		 FONCTION : Copie les données charges sociales à droite 
************************************************************************************************/		

function tableauCharges(){
	$('#speA').html($('#genA').val());
	$('#speB').html($('#genB').val());
	$('#speC').html($('#genC').val());
	$('#speD').html($('#genD').val());
	$('#speE').html($('#genE').val());
	$('#speF').html(Math.round ( ( +($('#F1').val()) + +($('#F2').val()) + +($('#F3').val()) + +($('#F4').val()) + +($('#F5').val()) + +($('#F6').val()) + +($('#F7').val()) + +($('#F8').val()) + +($('#F9').val()) + +($('#F10').val()) + +($('#F11').val()) + +($('#F12').val()) + +($('#F13').val()) ) * 100 )/ 100 ); 
	
	$('#totalGen').html(Math.round ( (+($('#genA').val()) + +($('#genB').val()) + +($('#genC').val()) + +($('#genD').val()) + +($('#genE').val()) + +($('#genF').val())) * 100 ) / 100);
	
	$('#totalStruct').html(Math.round ( (+($('#speA').html()) + +($('#speB').html()) + +($('#speC').html()) + +($('#speD').html()) + +($('#speE').html()) + +($('#speF').html())) * 100 ) / 100);
	
	
}
		
/************************************************************************************************
		 FONCTION : Calcul du montant global ( salaires + heures ) 
************************************************************************************************/		
		
function totalComplet(){
	$('#masseSalSCS').html( Math.round ( ( parseFloat($('#aTotSalBrut').html()) + parseFloat($('#cTotSalBrut').html()) ) * +($('#nbrHeuresOuvrier').val()) * +($('#nbrJours').val()) * 100 ) / 100 );
	
	if(document.getElementById('approcheG').checked) {
		$('#masseSalACS').html(Math.round ( parseFloat($('#masseSalSCS').html()) + ( parseFloat($('#masseSalSCS').html()) * parseFloat($('#totalGen').html()) / 100 ) ) );
	}
	else {
		$('#masseSalACS').html(Math.round ( parseFloat($('#masseSalSCS').html()) + ( parseFloat($('#masseSalSCS').html()) * parseFloat($('#totalStruct').html()) / 100 ) ) );
	}
	
// Calcul du salaire horaire moyen avec Charges sociales de Ouvriers sur CHANTIER

if(document.getElementById('approcheG').checked) {
		$('#cTotSalMoyCC').html(Math.round (( parseFloat($('#cTotSalMoyBrut').html()) + ( parseFloat($('#cTotSalMoyBrut').html()) * parseFloat($('#totalGen').html()) / 100 )) * 100 ) / 100 );
	}
	else {
		$('#cTotSalMoyCC').html(Math.round (( parseFloat($('#cTotSalMoyBrut').html()) + ( parseFloat($('#cTotSalMoyBrut').html()) * parseFloat($('#totalStruct').html()) / 100 )) * 100 ) / 100 );
	}	
	
// Calcul du salaire horaire moyen avec Charges sociales de Ouvriers sur ATELIER

if(document.getElementById('approcheG').checked) {
		$('#aTotSalMoyCC').html(Math.round (( parseFloat($('#aTotSalMoyBrut').html()) + ( parseFloat($('#aTotSalMoyBrut').html()) * parseFloat($('#totalGen').html()) / 100 )) * 100 ) / 100 );
	}
	else {
		$('#aTotSalMoyCC').html(Math.round (( parseFloat($('#aTotSalMoyBrut').html()) + ( parseFloat($('#aTotSalMoyBrut').html()) * parseFloat($('#totalStruct').html()) / 100 )) * 100 ) / 100 );
	}		
	
}


/************************************************************************************************
		 FONCTION : Calcul du tableau en bas pour approche spécifique 
************************************************************************************************/	
function calcTabAS(){
	$('#mF1').html(Math.round ( parseFloat($('#F1').val()) * parseFloat($('#masseSalSCS').html()) ) / 100 );
	$('#mF2').html(Math.round ( parseFloat($('#F2').val()) * parseFloat($('#masseSalSCS').html()) ) / 100 );
	$('#mF3').html(Math.round ( parseFloat($('#F3').val()) * parseFloat($('#masseSalSCS').html()) ) / 100 );
	$('#mF4').html(Math.round ( parseFloat($('#F4').val()) * parseFloat($('#masseSalSCS').html()) ) / 100 );
	$('#mF5').html(Math.round ( parseFloat($('#F5').val()) * parseFloat($('#masseSalSCS').html()) ) / 100 );
	$('#mF6').html(Math.round ( parseFloat($('#F6').val()) * parseFloat($('#masseSalSCS').html()) ) / 100 );
	$('#mF7').html(Math.round ( parseFloat($('#F7').val()) * parseFloat($('#masseSalSCS').html()) ) / 100 );
	$('#mF8').html(Math.round ( parseFloat($('#F8').val()) * parseFloat($('#masseSalSCS').html()) ) / 100 );
	$('#mF9').html(Math.round ( parseFloat($('#F9').val()) * parseFloat($('#masseSalSCS').html()) ) / 100 );
	$('#mF10').html(Math.round ( parseFloat($('#F10').val()) * parseFloat($('#masseSalSCS').html()) ) / 100 );
	$('#mF11').html(Math.round ( parseFloat($('#F11').val()) * parseFloat($('#masseSalSCS').html()) ) / 100 );
	$('#mF12').html(Math.round ( parseFloat($('#F12').val()) * parseFloat($('#masseSalSCS').html()) ) / 100 );
	$('#mF13').html(Math.round ( parseFloat($('#F13').val()) * parseFloat($('#masseSalSCS').html()) ) / 100 );
	
}

/************************************************************************************************
		 FONCTION : Calcul d'autres annexes
************************************************************************************************/

function calcAutres(){
	$('.heuresTotOuvrier').html(parseFloat($('#nbrJours').val()) * parseFloat($('#nbrHeuresOuvrier').val()) * ( parseFloat($('#cTotNombre').html()) + parseFloat($('#aTotNombre').html())));
	$('.heuresTotPatron').html(parseFloat($('#nbrJours').val()) * parseFloat($('#nbrHeuresPatron').val()));
	$('.heuresTot').html(parseFloat($('.heuresTotOuvrier').html()) + parseFloat($('.heuresTotPatron').html()));	
	$('#heuresNP').html(parseFloat($('.heuresTot').html()) * parseFloat($('.plusNbr').html()) / 100);
	$('#heuresP').html(parseFloat($('.heuresTot').html()) * parseFloat($('.moinsNbr').html()) / 100);
	
	
}

</script>
</html>
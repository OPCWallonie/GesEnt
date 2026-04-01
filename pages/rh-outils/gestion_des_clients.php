<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>Gestion des collaborateurs</title>
<meta name="keywords" content="" lang="fr">
<meta name="description" content="">

<meta http-equiv="Content-Language" content="fr">

<link rel="stylesheet" type="text/css" href="../css/facture_ajout.css" media="all" />


</head>
<body>

<?php
include ("./menu.php");
$action = $_GET['action'];
if ($action == "") $action = "contact_list";
?>

<div id="contenant">
		

<div class="box">
	<h2>Gestion des clients et prospects</h2>
		
			
		
<ul id="menu">
			
							
		        <li>
					<a <?php if ($action=="contact_list") { ?> class="active"<?php } ?> href="?action=contact_list#_contact_list" rel="contact_list">
						<img src="../images/user.png" alt="Liste des contacts"> 
						<span>Liste</span>
					</a>
		        </li>
				
							
				
				
				<li>
					<a <?php if ($action=="contact_add") { ?> class="active"<?php } ?> href="?action=contact_add#_contact_add" rel="contact_add">
						<img src="../images/user_add.png" alt="Ajouter un contact">
						<span>Ajouter</span>
					</a>
				</li>
				
						
		    		    
		</ul>
		 
	  
        
	
	<div id="contact_list" class="content" <?php if ($action=="contact_add") { echo 'style="display:none;"'; }; ?>>
		
				
			<form action="" class="search">
				
				                
                	                
	                		                
					<div class="fleft">
						<select name="cat_contact">
							<option value="">Catégorie de contact</option>
							<option style="background-color: rgb(227, 227, 227);" value="client">Clients</option><option value="prospect">Prospect</option						></select>
                    </div> 
				
				                
                
                <div class="fleft">
					<select name="societe_contact">
						<option value="">Société</option>
						
						<option style="background-color: rgb(227, 227, 227);" value="CCL">CCL</option>		                
					</select> 
				</div>
				
				
                <div class="fleft">
					<label for="inputString">Nom :</label> 
					<input autocomplete="off" name="interlocuteur_contact" value="" class="textfield" id="inputString" style="width: 130px;" type="text"> 
					
					<div class="suggestionsBox" id="suggestions" style="left: 40px;">
						<img src="../images/uparrow.png" alt="upArrow">
						<div class="suggestionList" id="autoSuggestionsList">&nbsp;</div>
						<div class="hide">contacts/autoComplete</div>
					</div>					
					<input name="idcont" id="id_autoComplete" value="" type="hidden">
                    
            	</div>
                
                
                <div class="fleft">
                	ou chercher dans la liste 
                	<select name="interlocuteur_contact2">
						<option value="">Interlocuteur</option>
						<option style="background-color: rgb(227, 227, 227);" value="451">Arnaud François</option>					</select>
				</div>
				 
				 
				 <a href="#" class="button submit">
				 	<span><img src="../images/magnifier.png" alt="Recherche">Rechercher</span>
				 </a>
				
			</form>
			
		
        	<table class="margin-top" cellspacing="0">
            	<tbody><tr>
            		<th colspan="2" class="aleft">
                		<a href="">
                			Société/Personne
                		</a>
                		<br />
                		<span class="small">Code client</span>
                	</th>
                	
                	<th class="aleft" width="300">
                		<a href="">
                			Groupe
                		</a>
                	</th>
                	
                	<th class="aleft" width="300">
                		<a href="">
                			Interlocuteur
                		</a>
                		<br />
                		<span class="small">Poste</span>
                	</th>         
                	
                	<th colspan="2" width="64">&nbsp;</th>
           		</tr>
            
            	                
                	
	             
	                	          
	                		                	
	                									
							<tr style="cursor: pointer;" id="lig451" class="alternate">
		                    	<td width="64">
		                    		<img src="../images/user.png" alt="Arnaud François" height="64" width="64">
		                    	</td>
		                        
		                        <td class="vtop" width="200">
		                        	CCL		                        	<br />
		                        	<span class="legende">Code : ccl001</span>
		                        </td>
		                       	
		                       	<td class="vtop">Clients</td>
		                      	
		                      	<td class="vtop">
		                      		Arnaud François		                      		<br />
		                      		<span class="legende">Responsable IT</span>
		                      	</td>
		                               
		                        <td class="vtop">
									
																			
										E-mail : <a class="underline" href="mailto:arnaud.francois@ccl.be" title="Envoyer un e-mail">arnaud.francois@ccl.be</a><br />
									
																		
																		
																		
		                    		<a href="" title="Voir la fiche contact" class="link_tr"></a>
		                    	</td>
								
								<td class="vtop">
		                               
		                    				                    		
		                    			<a href="" title="Modifier">
		                    				<img src="../images/pencil.png" alt="Modifier">
		                    			</a>
		                    			
		                    				                    
		                    	</td>
	
							</tr>
	                	
	                		                	
	                                
                                
                            
            
        	</tbody></table>
                
                
	</div>
	
	
	<div id="contact_add" class="hide content">
		
				        
		       
<form action="" method="post" id="ajout_contact" enctype="multipart/form-data">
	<h3>Informations générales</h3>
	<fieldset>
		
					                   
			                	
			<p class="gris01">
				<span class="toolTip" title="Attribuez une catégorie à votre contact (Client ou Prospect)">&nbsp;</span>
			    <label for="categorie_contact">Catégorie : </label>
			    
			    <select name="categorie_contact" id="categorie_contact">
			    
			    				    		
		<option value="client">
			Clients	
        </option>								
								    		
		<option value="2332" selected="selected">
			Prospects						
        </option>
										
			 	</select>
			                        
			</p>	    
		<p>
			<span class="toolTip" title="Renseignez dans ce champ le nom et le prénom de votre contact">&nbsp;</span>
			<label for="interlocuteur">Interlocuteur : </label>
			<input name="interlocuteur" id="interlocuteur" class="textfield" value="" type="text">
		</p>
		
		<p class="gris01">
		  	<span class="toolTip" title="Indiquez la fonction occupée par votre contact">&nbsp;</span>
			<label for="fonction">Fonction : </label>
			<input name="fonction" id="fonction" class="textfield" value="" type="text">
		</p>
		
		<p>
			<span class="toolTip" title="Renseignez dans ce champ le nom ou la société de votre contact<br />
">&nbsp;</span>
			<label for="societe">Société : </label>
			<input name="societe" id="societe" class="textfield" value="" type="text">
		</p>
			                    
	</fieldset>
		      		
		      		
	<h3>Informations complémentaires</h3>
	<fieldset>
	  	<p class="gris01">
			<span class="toolTip" title="Vous pouvez attribuer un code compta à votre contact client qui vous permettra de retrouver plus rapidement les factures et les devis attribués à ce client. Le champ code compta sera automatiquement renseigné lors de la création de factures et de devis pour ce client.">&nbsp;</span>
			<label for="code_client">Code compta/client : </label>
			<input name="code_client" id="code_client" class="textfield" value="" type="text" placeholder="cl00001">
		</p>
			                    
		<div class="ajouter_sites fleft" style="width: 938px;">
          	<p>
				<span class="toolTip" title="Renseignez l'adresse du site internet de votre contact. Vous pouvez ajouter un site internet supplémentaire en cliquant sur le bouton Ajouter">&nbsp;</span>
			    <label for="site_internet">Site internet : </label>
                                    
			  					                        
						<input name="site_internet[]" id="site_internet" class="textfield" value="http://" type="text">
						
						<select name="type_site[]">
							<option style="background-color: rgb(227, 227, 227);" value="professionnel">Professionnel</option>
							<option value="perso">Personnel</option>
							<option style="background-color: rgb(227, 227, 227);" value="autre">Autre</option>
						</select>
	                                    
	                	
               		</p>
										
					                        
		
		</div>	    
                                    
			                    
		<div class="ajouter_mails fleft gris01" style="width: 938px;">
        	<p>
            	<span class="toolTip" title="Si le contact est un client, il est important de renseigner une adresse e-mail valide car lors de la création de factures et de devis le champ e-mail sera automatiquement renseigné et permettra d'envoyer directement le document par e-mail. Précisez s'il s'agit d'une adresse e-mail professionnelle, personnelle ou autre. Vous pouvez ajouter une adresse e-mail supplémentaire en cliquant sur le bouton Ajouter">&nbsp;</span>
                <label for="mail">E-mail : </label>
			                       
			    				                        
						<input name="mail_principal" id="mail" class="textfield" value="" type="text">
						 
						 <select name="type_mail_principal">
						 	<option value="professionnel">Professionnel</option>
						 	<option style="background-color: rgb(227, 227, 227);" value="perso">Personnel</option>
						 	<option value="autre">Autre</option>
						 </select>
											
	                   	<a href="#" class="button contact_ajouter_mail fright" style="margin-right:310px">
	                   		<span><img src="../images/add.png" alt="Ajouter "> Ajouter</span>
	                   	</a>
                	</p>
					
							                        	
		</div>
			                       	 	 
			                    
		<div class="ajouter_tels fleft" style="width: 938px;">
        	<p>
				<span class="toolTip" title="Renseignez le numéro de téléphone de votre contact sans ponctuation et précisez s'il s'agit d'un numéro professionnel, personnel ou autre. Vous pouvez ajouter un numéro de téléphone supplémentaire en cliquant sur le bouton Ajouter">&nbsp;</span>
			   	<label for="tel">Téléphone : </label>
												                       
			    				                        
						<input name="telephone[]" id="tel" class="textfield" value="" type="text">
						
						<select name="type_tel[]">
							<option style="background-color: rgb(227, 227, 227);" value="domicile">Domicile</option>
							<option value="mobile">Mobile</option>
							<option style="background-color: rgb(227, 227, 227);" value="professionnel" selected="selected">Professionnel</option>
							<option value="autre">Autre</option>
						</select>
											
						<a href="#" class="button contact_ajouter_tel fright" style="margin-right:355px;">
							<span><img src="../images/add.png" alt="Ajouter "> Ajouter</span>
						</a>
                 	</p>
									
				  
			            
		</div>
			                    
			                    
		<p class="gris01">
        	<span class="toolTip" title="Renseignez le numéro de fax de votre contact sans ponctuation">&nbsp;</span>
            <label for="fax">Fax : </label>
            <input name="fax" id="fax" class="textfield" value="" type="text">
        </p>
	</fieldset>
		      		
		      		
	<p class="toggle">
  		<a href="#">Gérer l'adresse de facturation</a>
    </p>
		
			      		
	<div class="hide">
			                        
					            	
						            		      		
			<fieldset class="liste_adresses">
        		<p>
        			<span class="toolTip" title="Indiquez ici de quel type d'adresse il s'agit, par exemple siège social, adresse de livraison, adresse personnelle etc.">&nbsp;</span>
        			<label>Nom de l'adresse : </label>
        			<input value="" name="adresse_type[]" class="textfield" type="text">
        		</p>
        		
                <p class="gris01">
                	<span class="toolTip" title="S'il s'agit d'un client, l'adresse sera automatiquement renseignée lors de la création de factures et de devis.">&nbsp;</span>
                    <label>Adresse : </label>
                    <input value="" name="adresse[]" class="textfield" type="text">
                </p>
                
                <p>
                	<span class="toolTip" title="Renseignez un complément d'adresse (facultatif)">&nbsp;</span>
                    <label>Complément : </label>
                    <input value="" name="adresse_suite[]" class="textfield" type="text">
                </p>
                
                <p class="gris01">
               		<span class="toolTip" title="S'il s'agit d'un client, le code postal sera automatiquement renseigné lors de la création de factures et de devis.">&nbsp;</span>
                    <label>Code postal : </label>
                    <input value="" name="cp[]" class="textfield" type="text">
                </p>
                
                <p>
                	<span class="toolTip" title="S'il s'agit d'un client, la ville sera automatiquement renseignée lors de la création de factures et de devis.">&nbsp;</span>
                    <label>Ville : </label>
                    <input value="" name="ville[]" class="textfield" type="text">
                </p>
                
                <p class="gris01">
                	<span class="toolTip" title="S'il s'agit d'un client, le pays sera automatiquement renseigné lors de la création de factures et de devis.">&nbsp;</span>
                    <label>Pays : </label>
                    <input value="" name="pays[]" class="textfield" type="text">
                </p>						      		
			</fieldset>
					      			
						      		
		  	
	</div>
		      
		      		
	<p class="toggle">		      		
  		<a href="#">Facturation</a>
    </p>
    
    
	<div class="hide">
        <fieldset>
            <p class="gris01">
            	<span class="toolTip" title="Indiquez le régime TVA dont dépend votre contact">&nbsp;</span>
                <label for="regime_tva">Régime TVA : </label>
                
              <select name="regime_tva" id="regime_tva">
								
<option value="assujetti" selected="selected">Assujetti</option>
<option value="non_assujetti">Non assujetti</option>
<option value="Co-contractant">Co-contractant</option>
							
							
							</select>  
                
            </p>
            
            <p>
            	<span class="toolTip" title="Le numéro de TVA est composé du préfixe du pays suivi d'au maximum 12 chiffres ou caractères alphanumériques. Le champ TVA sera automatiquement renseigné lors de la création de factures et de devis">&nbsp;</span>
                <label for="numero_tva">N° TVA : </label>
                <input name="numero_tva" id="numero_tva" class="textfield" value="" type="text">
            </p>
            
            <p class="gris01">
            	<span class="toolTip" title="Indiquez le mode de règlement habituel de votre contact client celui-ci s'affichera automatiquement lors de la création de factures et de devis">&nbsp;</span>
							
<label for="mode_reglement">Mode de réglement : </label>

<?php
// On récupère les données nécessaires à la suite du traitement des moyens de payements
mysql_query('set names utf8'); // Instruction magique qui normalise les accents et permet d'avoir les mêmes caractères côté base et côté site... Ce qui ne gâche rien ;)
		$requete = 'SELECT id, nom, defaut, utilise FROM ' . $tab_modepayement ;
		$mysql_result = mysql_query($requete, $db);
?>   

 
							<select name="mode_reglement" id="mode_reglement">
								
<?php
							while($data = mysql_fetch_assoc($mysql_result))
							{

echo '<option value="'.$data['nom'].'" ';
	
if ($data['defaut'] == '1' ) { echo 'selected="selected" '; }						
							
echo '>'.$data['nom'].'</option>';
							
							}
?>
							
							
							</select>
						</p>
            
            
            
            
            
            
            <p>
            	<span class="toolTip" title="Vous pouvez attribuer à votre contact lorsqu'il s'agit d'un client une remise habituelle qui s'appliquera lors de l'édition d'une facture ou d'un devis. Il s'agit d'une remise globale qui s'appliquera sur le montant total HT de la facture ou du devis celle-ci doit être renseignée en euros HT">&nbsp;</span>
                <label for="remise">Remise globale HT : </label>
                <input name="remise" id="ristourne_globale" class="textfield" value="" type="text" size="2" />&nbsp;%
            </p>
            
  		</fieldset>	      		
	</div>
		      		

	       

	
		      		
			      			
		      		
	<p class="toggle">
		<a href="#">Annotations</a>
	</p>
                        
                        
    <div class="hide">
		<p class="em">Ici, vous pouvez par exemple saisir l'historique de vos échanges avec ce contact, afin que vos collaborateurs puissent être tenus au courant.</p>
		<textarea name="annotations" cols="129" rows="11"></textarea>
		
		<p class="acenter">
	   		<input name="adresses_id" id="adresses_id" type="hidden">
	      	
	      		      					
	      		<input name="c_id" id="c_id" value="" type="hidden">
	      				
	      	                        
      	</p>
  	</div>
                        
                        
  	<p class="acenter gris01">
      	<a href="#" class="button submit">
      		<span><img src="../images/accept.png" alt="Ajouter">Ajouter ce contact</span>
      	</a>
	            
			</p>
</form>		
	</div>	
	

	
</div></div>

<?php
include('./footer.php');
?>

</body>
</html>

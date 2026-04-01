Shadowbox.init({
    animate:     false
});



jQuery(document).ready(function($) {


url = $(location).attr('href');
url = url.substr('7');
decoupe = url.split('/');
url = decoupe[0];



/*=== VARIABLES GLOBALES ====*/
var static_url = '';
var manager_url = 'http://'+url+'/';	

/*#######################################################################
#                               LAYOUTS                                 #
#######################################################################*/

	/* date picker */
	$.datepicker.setDefaults($.extend({showMonthAfterYear: false, changeYear: true}));
	$(".datepicker").datepicker($.datepicker.regional['fr']);




	/*infobulle*/
	$('span.toolTip').toolTips();
	
	/*boutons submit*/
	$('a.submit').click(function(e){
		e.preventDefault();
		$(this).parents('form').submit();
	})

	
	/*textarea auto agrandissante*/
	$('.elastic').elastic();


	/* récuperation de l'ancre et affichage du div correspondant si necessaire */
	/*
	hash = window.location.hash;
	if (hash != '') {
		hash = hash.substr(2);
		$('#' + hash).show();
	}
	*/
	
	hash = window.location.hash;
	
	

	if(hash == "" && $('#lien_externes_liste').length == 0){
		//alert('1');
		$('#menu a:first').addClass('actif');
		$('.content:first').removeClass('hide');		
	}
	
	var state = '';
	
	$.History.bind(function(state){
		hash = window.location.hash;

		if (hash.indexOf('todolist_add_', 0) != -1) {
			$('#todolist_list').hide();
			$('#todolist_add').show();
			$('#menu a').removeClass('actif');
			$('#menu a[rel=todolist_add]').addClass('actif');
		}
		else{
			if (hash != '') {
				hash = hash.substr(2);
				$('#menu a').removeClass('actif');
				$('#menu a[rel=' + hash + ']').addClass('actif');
				$('.content').hide();
				$('#' + hash).show();
			}
			else {
				$('#menu a').removeClass('actif');
				$('#menu a:first').addClass('actif');
				$('.content').hide();
				$('.content:first').removeClass('hide').show();
			}
		}
	});
	
	/* SI je suis dans facture achats, je ne veux pas qu'il m'affiche le bouton d'archive lorsque j'édite une nouvelle
	facture*/
	
	
	

	
	/*acces rapide*/
	$('#acces_rapide').change(function(){
		url = $(this).val();
		$(location).attr('href', url);
	});
	
	
	/*menu*/
	$('ul#menu li a').click(function(e){
		if($(this).hasClass('follow')){
			
			return true;
			
		}else{
			
			//e.preventDefault();
		
			var div_id = $(this).attr('rel');
			
			disp = $('#' + div_id).css('display');
			
			if(disp == "none")
			{
				$('ul#menu li a').removeClass('actif');
				$('div.content').hide();
				$('#' + div_id).show();
				$(this).addClass('actif');
			}
			
		}
		
	});


	/*liens sur les lignes des tableaux*/
	
	$('tr').mouseover(function(){
							   
		if($(this).find('.link_tr').length != 0){
			
			$(this).css('cursor','pointer');
			
		}
		
	});
	
	$('td').click(function(){
		
		var lien = $(this).parent().find('.link_tr');

		if($(this).find('a').length == 0 && lien.length != 0){
			
			window.location.href = lien.attr('href');
			
			/* Debut de piste à creuser
			    Shadowbox.open({
			        content:    '<div id="welcome-msg">Welcome to my website!</div>',
			        player:     "html",
			        title:      "Welcome",
			        height:     350,
			        width:      350
			    });
			*/
			
		}
		
	});
	
	/*Listes explorer*/
	$('li a.root').click(function(e){
		e.preventDefault();
										  
		disp = $(this).next('ul').css('display');

		$('.explorer li ul').slideUp();
		
		if(disp == "none")
		{
			$(this).next('ul').slideDown();
		}
		else
		{
			$(this).next('ul').slideUp();
		}
		
		
	});
	
	
	$('.bouton_deroulant').click(function(e){
		e.preventDefault();
		
		disp = $(this).parent().next('div').css('display');
		
		
		if (disp == "block") {
			$(this).parent().next('div').slideUp();
		}
		else{
			$(this).parent().next('div').slideDown();
		}
	});



/*#######################################################################
#                               CONNEXION                               #
#######################################################################*/

	// on selectionne le form contenu dans le div connexion.
  	$('#connexion>form').submit(function(){
		// on récup le login
		var login = $('#login').val();
		
		// on recup le pass
		var mot_de_passe = $('#pass').val();
		
		// renverra true en cas d'erreur
		var erreur = false;
		
		// on teste si le login et pass sont pas vide, sinon on leur ajoute la class "error" et le formulaire retournera False
		if(login=="")
		{
			$('#login').addClass('error');
			erreur = true;
		}
		if(mot_de_passe=="")
		{
			$('#pass').addClass('error');
			erreur = true;
		}

		// si false, le submit n'est pas pris en compte et le form ne s'envoie donc pas
		return !erreur;
	});
  


/*#######################################################################
#                      COMMUN A DEVIS ET FACTURE                        #
#######################################################################*/

/** boutons pour envoyer la facture ou le devis...
 * 
 */

$('.fill_hidden').click(function(e)
{
	var rel = $(this).attr('rel');
	
	e.preventDefault();
	$('#submit_form').val(rel);
	$(this).parents('form').submit();	
});




var type_document = $('#type_document').val();


$('.toggle a').click(function(e){
	e.preventDefault();
	if($(this).parent().hasClass('open')){
		$(this).parent().removeClass('open');
		$(this).parent().next('div').slideUp('fast');
	}else{
		$(this).parent().addClass('open');
		$(this).parent().next('div').slideDown('fast');
	}
});



$('#popup_ajouter_prod').submit(function(e){
	e.preventDefault();
	
	var cat 	= $('#cat_prod').val();
	var nom		= $('#titre_prod').val();
	var desc	= $('#desc_prod').val();
	var prix	= $('#prix_prod').val();
	var ht_ttc	= $('#ht_ttc').val();
	var tva_prod= $('#tva_prod').val();
	
	var msg		= '';
	
	if(nom == ''){
		msg += '- Nom\r\n';
	}

	if(desc == ''){
		msg += '- Description\r\n';
	}
	
	if(prix == ''){
		msg += '- Prix\r\n';
	}
	
	if(tva_prod == ''){
		msg += '- Tva\r\n';
	}
	
	// Si erreur on affiche, sinon go Ajax()!
	if(msg != ''){
		jAlert('Merci de remplir les champs suivants:\r\n' + msg);
	}
	else{
		 $.ajax({
		   type: "post",
		   url: manager_url+"produits",
		   data: "cat_prod="+cat+"&titre_prod="+nom+"&desc_prod="+desc+"&prix_prod="+prix+"&ht_ttc="+ht_ttc+"&tva_prod="+tva_prod+"&ref_prod=&prix_revient_prod=&qte_prod=",
		   success: function(msg){
				//alert('insertion à faire');
				parent.$('#designation').val(nom);
				parent.$('#detail').val(desc);
				parent.$('#tva option[value='+tva_prod+']').attr('selected', 'selected');
				
				if (ht_ttc == 'ht')
				{
					parent.$('#prix').val(prix);
					
					montantTTC = arrondir(Nombre(prix) * (1 + (Nombre(tva_prod)/100)), 2);
					parent.$('#montant').val(montantTTC);
					
					parent.Shadowbox.close();
				}
				else
				{
					parent.$('#montant').val(prix);
					
					montantHT = arrondir(Nombre(prix) / (1 + (Nombre(tva_prod)/100)), 2);
					parent.$('#prix').val(montantHT);
					
					parent.Shadowbox.close();
				}

		   }
		 });		
	}
	
	return false;
	
});



$('.pagination_produit').live('click', function(e){
	var page = $(this).attr('rel');
	
		 $.ajax({
		   type: "post",
		   url: manager_url+"devis/choisirProduit/",
		   data: "page="+page,
		   success: function(msg){
		   		$('.inserer_produit').html(msg);
		   }
		 });
});

/***********************************************************
************** EDITER UNE LIGNE EN LIVE ********************
***********************************************************/

// Affichage des options "modifier" et "supprimer" dans la ligne de tableau
	
	// Hover
	$('#tab_document tr').live('mouseover', function(){
		$(this).find('.ligne_modifs').css('visibility','visible');
	});
	
	//Out
	$('#tab_document tr').live('mouseout', function(){
		$(this).find('.ligne_modifs').css('visibility','hidden');
	});


// Lors du clic sur "modifier la ligne"
	$('#tab_document .edit_ligne').live('click', function(e){
		e.preventDefault();
		
		Ligne = $(this).parent().parent().parent();
		idLigne = Ligne.attr('id');
		
		numLigne = Ligne.children('td:first').text();
	   	$.ajax({
		   	type: "post",
	   		url: manager_url+ type_document + "/infosLigne",
	   		data: "type_document="+type_document+"&idLigne="+idLigne+"&numLigne="+numLigne,
	   		success: function(msg){
				Ligne.html(msg);
	   		}
		});		
	});

// Clic sur "annuler" dans la modif en live d'une ligne
	$('#tab_document .cancel_ligne').live('click', function(e){
		e.preventDefault();
	
		docId = $('#documentId').val();
		type_document = $('#type_document').val();
		
		$.ajax({
			type: "post",
			url: manager_url + type_document + "/afficherLignesTableau",
			data: "type_document="+type_document+"&docId=" + docId,
			success: function(msg){
				$('#tableauLignes').html(msg);
			}
		});		
	});

	$('#tab_document .save_ligne').live('click', function(e){
		e.preventDefault();

		docId = $('#documentId').val();
		type_document = $('#type_document').val();
		
		Ligne = $(this).parent().parent().parent();
		idLigne = Ligne.attr('id');
		
		titre 	= $('[name=modif_titre['+ idLigne.substr(3) +']]').val();
		desc	= $('[name=modif_desc['+ idLigne.substr(3) +']]').val();
		prix_ht	= $('[name=modif_prix_ht['+ idLigne.substr(3) +']]').val();
		remise	= $('[name=modif_remise['+ idLigne.substr(3) +']]').val();
		intitule_remise = $('[name=modif_intitule_remise['+ idLigne.substr(3) +']]').val();
		qte		= $('[name=modif_qte['+ idLigne.substr(3) +']]').val();
		tva		= $('[name=modif_tva['+ idLigne.substr(3) +']]').val();
		prix_ttc= $('[name=modif_prix_ttc['+ idLigne.substr(3) +']]').val();
		
	   	$.ajax({
		   	type: "post",
	   		url: manager_url+ type_document + "/editerLigne",
	   		data: "type_document="+type_document+"&idLigne="+idLigne.substr(3)+"&titre="+titre+"&desc="+desc+"&prix_ht="+prix_ht+"&remise="+remise+"&intitule_remise="+intitule_remise+"&qte="+qte+"&tva="+tva+"&prix_ttc="+prix_ttc,
	   		success: function(msg){
				$.ajax({
					type: "post",
					url: manager_url + type_document + "/afficherLignesTableau",
					data: "type_document="+type_document+"&docId=" + docId,
					success: function(msg){
						$('#tableauLignes').html(msg);
				   		$.ajax({
					   		type: "post",
					   		url: manager_url+ type_document + "/majBloc",
					   		data: "type_document="+type_document+"&docId="+docId,
							success: function(msg){
								$('#blocTotaux').html(msg);
							}
						});
					}
				});	
	   		}
		});	
	});



function recalcLigneEdit()
{
	//type_taxe = $('#type_taxe').val();
	type_taxe = 'ht';
	ht_ttc_remise = 'ht';
	
	remise = 0;
	laTva = (100.00 + Nombre( $('.modif_tva').val() ) ) / 100;
	qte = Nombre($('.modif_qte').val());
	
	// Je transforme les , en .
	var tmp = $('.modif_prix_ht').val();
	tmp = tmp.replace(',','.');

	// on recup le prix qu'on convertit de string en float
	prix = Nombre(tmp);
	
	
	
	//alert('prix=s'+$('.modif_prix_ht').val()+'& remise='+remise+' & tva='+laTva+' qte='+qte);
	
	/*========= GESTION DES REMISES =========*/
		//si on applique une remise, il faut determiner si on l'applique sur le prix ht, ttc, et si on applique sous forme de % ou €.	
		//alert($('.modif_remise').val());
		
		if($('.modif_remise').val() != "" && $('.modif_remise').val() != "0"){
			montant_remise = Nombre($('.modif_remise').val()); 
		
			if (ht_ttc_remise == "ht") {
				if ( $('.modif_intitule_remise').val() == "€" ) {
					remise = montant_remise;
				}
				else {
					remise = (prix * qte) * (montant_remise / 100);
				}
			}
		}
		
		// on met à jour le champ caché remise.
		$('.modif_prix_ttc').val(remise);
	/*======= FIN GESTION DES REMISES =======*/
	
	
	// on arrondit le total TTC et on l'affiche
	// si ht on applique la tva
	if (type_taxe == "ht") {
		total = qte * (prix - remise) * laTva;
	}
	//si ttc déjà déjà comprise
	else{
		total = qte * (prix - remise);
	}
	
	//alert(total);
	
	total = arrondir(total, 2);
	
	$('.modif_prix_ttc').val(total);
}


function recalcLigneEditTTC()
{
	prix_ttc = Nombre($('.modif_prix_ttc').val());
	
	prix_ht = ((prix_ttc / (1 + Nombre($('.modif_tva').val())/100)) / Nombre($('.modif_qte').val()));
	
	
	if (!isNaN($('.modif_remise').val()) && $('.modif_remise').val() != '0') {

		if ($('.modif_intitule_remise').val() == '€') {
			prix_ht = prix_ht + Nombre($('.modif_remise').val());
		}
		else {
			prix_ht = (prix_ht * qte ) / ((Nombre($('.modif_remise').val()) / 100));
		}
	}
	$('.modif_prix_ht').val(prix_ht);
}


$('.modif_qte, .modif_remise, .modif_intitule_remise, .modif_prix_ht').live('keyup', function(){
	recalcLigneEdit();
});

$('.modif_tva, .modif_intitule_remise').live('change', function(){
	recalcLigneEdit();
})

$('.modif_prix_ttc').live('keyup', function(){
	recalcLigneEditTTC();
});




/***************************************
********* AJOUTER LIGNE DOC ************
***************************************/

$('#ajouter_ligne_doc').click(function(e){
	e.preventDefault();
	type_document = $('#type_document').val();
	
	
	designation = $('#designation').val();
	detail = $('#detail').val();
	prix = Nombre($('#prix').val());
	type_taxe = $('#type_taxe').val();
	
	qte = Nombre($('#qte').val());
	tva = Nombre($('#tva').val());
	montant = $('#montant').val();
	
	remise 	= Nombre($('#remise').val());
	montant_remise = Nombre($('#montant_remise').val());
	
	unite_remise 	= $('#unite_remise').val();
	ht_ttc_remise 	= $('#ht_ttc_remise').val();
	montant_remise 	= Nombre($('#montant_remise').val());

	/* ====== GESTION DES ERREURS AVANT ENVOI ====== */
		error = '';
		
		if(designation == ''){ 	error += 'Veuillez renseigner la designation\r\n'; 	}
		if(detail == ''){ 		error += 'Veuillez renseigner le détail'; 			}
		if(qte == ''){ 			error += 'Veuillez renseigner la quantité'; 		}
		
		if(error != '')
		{
			jAlert(error);
			return false;
		}
		else{
			$(this).children('span').html(' <img src="'+static_url+'im/manager/icons16/loading.gif" alt="" style="margin-top:9px;" />');
		}
	/* ====== FIN DES GESTIONS DES ERREURS ======= */
	
	// Récup de l'id devis pour l'enregistrement
	docId = $('#documentId').val();
	
	 $.ajax({
	   type: "post",
	   url: manager_url+ type_document + "/saveLigne",
	   data: "type_document="+type_document+"&docId="+docId+"&designation="+designation+"&detail="+detail+"&prix="+prix+"&type_taxe="+type_taxe+"&qte="+qte+"&tva="+tva+"&montant="+montant+"&remise="+remise+"&unite_remise="+unite_remise+"&ht_ttc_remise="+ht_ttc_remise,
	   success: function(msg){

			$('#ajouter_ligne_doc').children('span').html('<img alt="Ajouter ligne" src="http://www.wuro.fr/static/im/manager/icons16/disk.png"> Insérer cette ligne');

			//reinit des champs de formulaire d'ajout de ligne si tout s'est bien passé
				$('#designation, #detail, #prix, #remise, #montant').val('');
				$('#qte, #montant_remise').val('1');
				   
	   		$.ajax({
		   		type: "post",
		   		url: manager_url+ type_document + "/majBloc",
		   		data: "type_document="+type_document+"&docId="+docId,
		   		success: function(msg){
					$('#blocTotaux').html(msg);
					
					$.ajax({
						type: "post",
						url: manager_url + type_document + "/afficherLignesTableau",
						data: "type_document="+type_document+"&docId=" + docId,
						success: function(msg){
							$('#tableauLignes').html(msg);
						}
					});	
		   		}
			});
			
	   }
	 });
		 		 
	return false;
});








/****************************************************************************
******** MAJ EN LIVE DE LA LIGNE EN FONCTION DE LA MODIF DES PARAM **********
****************************************************************************/


function recalcLigne(div)
{
	
	
	type_taxe = $('#type_taxe').val();
	ht_ttc_remise = $('#ht_ttc_remise').val();
	
	remise = 0;
	laTva = (100.00 + parseFloat($('#tva').val())) / 100;
	qte = parseFloat($('#qte').val());
	

	// on recup le prix qu'on convertit de string en float
	prix = Nombre($('#prix').val());	
	
	
	/*========= GESTION DES REMISES =========*/
		//si on applique une remise, il faut determiner si on l'applique sur le prix ht, ttc, et si on applique sous forme de % ou €.	
		if($('#remise').val() != "" && $('#remise').val() != "0"){
			montant_remise = Nombre($('#remise').val()); 
		
			if (ht_ttc_remise == "ht") {
				if ($('#unite_remise').val() == "€") {
					remise = montant_remise;
				}
				else {
					remise = ( prix * qte ) * (montant_remise / 100);
				}
			}
		}
		
		// on met à jour le champ caché remise.
		$('#montant_remise').val(remise);
	/*======= FIN GESTION DES REMISES =======*/
	
	
	// on arrondit le total TTC et on l'affiche
	// si ht on applique la tva
	if (type_taxe == "ht") {
		total = ((qte * prix) * laTva) - remise;
	}
	//si ttc déjà déjà comprise
	else{
		total = (qte * prix) - remise;
	}
	total = arrondir(total, 2);
	
	$('#montant').val(total);
}


function recalcLigneTTC()
{
	prix_ttc = Nombre($('#montant').val());
	
	prix_ht = ((prix_ttc / (1 + Nombre($('#tva').val())/100)) / Nombre($('#qte').val()));
	
	
	if (!isNaN($('#remise').val()) && $('#remise').val() != '0') {

		if ($('#unite_remise').val() == '€') {
			prix_ht = prix_ht + Nombre($('#remise').val());
		}
		else {
			prix_ht = prix_ht / ((Nombre($('#remise').val()) / 100));
		}
	}
	$('#prix').val(prix_ht);
}


$('#qte, #remise, #unite_remise, #prix').keyup(function(){
	recalcLigne('');
});

$('#tva, #unite_remise').change(function(){
	recalcLigne('');
})

$('#montant').keyup(function(){
	recalcLigneTTC();
});









/****************************************************
**** MAJ DE LA REMISE ET DES FRAIS DE PORT **********
*****************************************************/

// fonction appelée lors de la modif des frais de port et de la remise...
function EditerInfosDoc(champ, val)
{
	type_document = $('#type_document').val();
	docId = $('#documentId').val();
	
	$.ajax({
   		type: "post",
   		url: manager_url + type_document + "/editerInfos",
   		data: "type_document="+type_document+"&docId="+docId+"&champ="+champ+"&val="+val,
   		success: function(msg){

			$.ajax({
		   		type: "post",
		   		url: manager_url+ type_document + "/majBloc",
		   		data: "type_document="+type_document+"&docId="+docId,
		   		success: function(msg){
					$('#blocTotaux').html(msg);
		   		}
			});

   		}
	});	
}

$('#frais_de_port').change(function(){
	EditerInfosDoc('port', $(this).val());	
});

$('#tva_port').change(function(){
	EditerInfosDoc('tva_port', $(this).val());	
});

$('#remise_globale').change(function(){
	EditerInfosDoc('remise', $(this).val());	
});









/*************************************************
*************** SUPPRIMER LIGNE ******************
*************************************************/

$('.delete_ligne').live('click', function(e){
	e.preventDefault();
	
	type_document = $('#type_document').val();
	idLigne = ($(this).parent().parent().parent().attr('id')).substr(3);
	
	jConfirm('Etes vous sûr de vouloir supprimer cette ligne?\r\n\r\nUne fois la ligne supprimée, il ne sera plus possible de la récupérer ultérieurement.', 'Suppresion d\'une ligne du devis', function(r) {
	    if(r)
		{
				$.ajax({
					type: "post",
					url: manager_url + type_document + "/deleteLigne",
					data: "type_document="+type_document+"&idLigne=" + idLigne,
					success: function(msg){
					
	
					   	 $.ajax({
						   type: "post",
						   url: manager_url+ type_document + "/majBloc",
						   data: "type_document="+type_document+"&docId="+$('#documentId').val(),
						   success: function(msg){
								$('#blocTotaux').html(msg);
						
								$.ajax({
									type: "post",
									url: manager_url + type_document + "/afficherLignesTableau",
									data: "type_document="+type_document+"&docId="+$('#documentId').val(),
									success: function(msg){
										$('#tableauLignes').html(msg);
									}
								});	
						   }
						 });
	
						if (msg != '') {
							jAlert('Une erreur est survenue dans la suppresion de la ligne de ce devis.','');
						}
					}
				});			
		}
	}); // fin jconfirm

});






// Petit + et petit - pour incrémenter de décrémenter la quantité dans l'ajout de ligne
$('.qte_plus').click(function(e){
	e.preventDefault();
	$('#qte').val( Nombre($('#qte').val()) + 1);
	recalcLigne('');
});

$('.qte_moins').click(function(e){
	e.preventDefault();
	if( Nombre($('#qte').val()) > 1){
		$('#qte').val( Nombre($('#qte').val()) - 1);
		recalcLigne('');
	}
});

// ======================================================= //











/*#######################################################################
#                                DEVIS                                  #
#######################################################################*/

/*****************************
*********** GLOBAL ***********
*****************************/

$('.recherche_avancee').click(function(){
	$('#bouton_recherche').hide();
});





/**************************************************
*********** CHANGER L'ETAT D'UN DEVIS *************
**************************************************/

function saveEtat(doc_id, etat, type_document)
{
	if(etat == 'Payée'){
			var compat = 'Payee';
		}
		else if (etat == 'Validé'){
			var compat = 'Valide';
		}
		else if (etat == 'Impayée'){
			var compat = 'Impayee';
		}
		else if (etat == 'Refusé'){
			var compat = 'Refuse';
		}
		else if (etat == 'Archivé'){
			var compat = 'Archive';
		}
		else if (etat == 'Archivée'){
			var compat = 'Archivee';
		}
		else {
			var compat = etat;
		}
	
	 
	if(etat == 'Payée' || etat == "Validé"){
		
	    Shadowbox.open({
	        content:    '../comptabilite/change_etat.html?origine=' + type_document + '&etat=' + compat + '&doc_id=' + doc_id,
	        player:     "iframe",
	        title:      "Changer l\'état",
	        height:     250,
	        width:      250
	    });
		
	}
	else{
		$.ajax({
			type: "post",
			url: '../comptabilite/change_etat.html?origine=' + type_document + '&etat=' + compat + '&doc_id=' + doc_id,
			data: "iddoc=" + doc_id + "&etat="+ compat + "$origine="+type_document
		});	
		
	}
}

$('.select_etat').live('blur', function(){
	valeur = $(this).val();
	
	if(valeur == 'Brouillon' || valeur == 'En attente'){
		var diode = 'diode_orange.png';
	}else if(valeur == 'Impayée' || valeur == 'Retard' || valeur == 'Refusé'){
		var diode = 'diode_rouge.png';
	}else if(valeur == 'Archivé' || valeur == 'Archivée'){
		var diode = 'diode_grise.png';
	}else{
		var diode = 'diode_verte.png';
	}
						
	$(this).parent().html('&nbsp;<img src="../images/' + diode + '" alt="' + valeur + '" class="inline" />&nbsp;<a href="#" class="changer_etat underline">'+valeur+'</a>');
   
});

$('.select_etat').live('change', function(){
	type_doc = $('#type_doc').text();
	
	valeur = $(this).val();

var	dev_id = $(this).parent().siblings('#noeud').find('input').val(); // Important, ça m'a pris une plombe pour trouver ça !!!!!
	
	
	
	
	if(valeur == 'Brouillon' || valeur == 'En attente'){
		var diode = 'diode_orange.png';
	}else if(valeur == 'Impayée' || valeur == 'Retard' || valeur == 'Refusé'){
		var diode = 'diode_rouge.png';
	}else if(valeur == 'Archivé' || valeur == 'Archivée'){
		var diode = 'diode_grise.png';
	}else{
		var diode = 'diode_verte.png';
	}
	
	
	
	$(this).parent().html('&nbsp;<img src="../images/' + diode + '" alt="' + valeur + '" class="inline" />&nbsp;<a href="#" class="changer_etat underline">'+valeur+'</a>');
	
	saveEtat(dev_id, valeur, type_doc);	
	
/*	
	Ca ne marche malheureusement, firefox ne met plus à jour les etats si on applique ça...

	if (diode != 'diode_verte.png'){
	parent.location.reload(); 
	}
*/	
});


$('.changer_etat').live('click', function(e){
	e.preventDefault();
	type_doc = $('#type_doc').text();
	
	
	// On va tester si le quota de factures autorisées est dépassé (error_changement)
	// Si la facture qui va être modifiée est un brouillon
	// Si la facture n'a bien aucun numéro
	if ($('#error_changement').length > 0 && $(this).text() == "Brouillon" && $(this).parent().parent().children('.numero').html() == ' - ') {
		 Shadowbox.open({
	        content:    manager_url + type_doc + '/popupErrorChangeEtat/'+$('#error_changement').val(),
	        player:     "iframe",
	        title:      "Limite atteinte pour votre version",
	        height:     180,
	        width:      400
	    });
	} else {
		
		if (type_doc == 'devis') {
			$(this).parent().html('<select name="" class="select_etat"><option value="Brouillon">Brouillon</option><option value="En attente">En attente</option><option value="Validé">Validé</option><option value="Refusé">Refusé</option><option value="Archivé">Archivé</option></select>');
		}
		else 
			if (type_doc == 'facture') {
				$(this).parent().html('<select name="" class="select_etat"><option value="Pro Forma">Pro Forma</option><option value="En attente">En attente</option><option value="Impayée">Impayée</option><option value="Payée">Payée</option><option value="Retard">Retard</option><option value="Archivée">Archivée</option></select>');
			}
		else
			if (type_doc == 'bdc') {
			$(this).parent().html('<select name="" class="select_etat"><option value="En attente">En attente</option><option value="Validé">Validé</option><option value="Refusé">Refusé</option><option value="Archivé">Archivé</option></select>');
		}
		
		$('.select_etat option[value=' + $(this).text() + ']').attr('selected', 'selected');
		$('.select_etat').focus();
	}
	
});



$('#popup_changer_etat_facture').submit(function(){
	error = false;
	
	type_doc = parent.$('#type_doc').text();
	
	if($('.popup_maj_date_reglement').val() == ''){
		jAlert('Veuillez choisir la date du réglement');
		error = true;
	}
	
	if(!error){
		iddoc = $('#iddoc').val();	
		
		$.ajax({
			type: "post",
			url: manager_url + type_doc + "/popupSaveChangements",
			data: "iddoc=" + iddoc + "&reglement="+ $('#choix_mode_paiement_facture').val() + "&date=" + $('.popup_maj_date_reglement').val(),
			success : function(){
				parent.Shadowbox.close();
			}
		});	
	
	}
	
	return false;
});


$('#popup_changer_etat_devis').submit(function(){
	error = false;
	
	type_doc = parent.$('#type_doc').text();
	
	if($('.popup_maj_date_validation').val() == ''){
		jAlert('Veuillez choisir la date du réglement');
		error = true;
	}
	
	if(!error){
		iddoc = $('#iddoc').val();	
		
		$.ajax({
			type: "post",
			url: manager_url + type_doc + "/popupSaveChangements",
			data: "iddoc=" + iddoc + "&date=" + $('.popup_maj_date_validation').val(),
			success : function(){
				parent.Shadowbox.close();
			}
		});	
	
	}
	
	return false;
});



/*#######################################################################
#                               FACTURES                                #
#######################################################################*/



$('.facture_etat_paiement').click(function(){
	if($(this).val() == "oui"){
		$(this).parent().next('div').removeClass('hide');
	}
	else{
		$(this).parent().next('div').addClass('hide');
	}
});



/*######################################################################
#						GESTION DES CONTACTS 						   #
######################################################################*/

$('#ajouter_categorie_contact').submit(function(){
	categorie_name = $('#categorie_name').val();

	if (categorie_name != "") {
		$.ajax({
			type: "post",
			url: manager_url + "contacts/add_gp_ajax",
			data: "groupe=" + categorie_name,
			success: function(msg){
				if (msg != "") {
					parent.$(".explorer").append('<li><a class="root" href="#">' + categorie_name + '<ul><li>Aucun contact dans ce groupe</li></ul></li>');
					parent.Shadowbox.close();
				}
				else {
					jAlert('La catégorie `' + categorie_name + '` existe déjà');
				}
			}
		});
	}
	
	return false;
});


$('#ajout_contact').submit(function(){
	prefixe_msg_erreur = 'Merci de vérifier de bien avoir correctement renseigné ';
	msg_erreur = '';
	nb_erreur = 0;
	
	categorie_contact 	= $('#categorie_contact').val();
	interlocuteur		= $('#interlocuteur').val();
	fonction 			= $('#fonction').val();
	societe 			= $('#societe').val();
	photo				= $('#photo').val();
	
	if(interlocuteur=='' && societe==''){
		msg_erreur += 'Merci de renseigner au moins l\'un des deux champs suivants:\r\n- Nom de l\'interlocuteur\r\n- Nom de la société';
		nb_erreur++;
	}
	//if(categorie_contact==""){ msg_erreur += '- Catégorie\r\n';nb_erreur++; }
	//if(interlocuteur==""){ msg_erreur += '- Interlocuteur\r\n';nb_erreur++; }
	//if(fonction==""){ msg_erreur += '- Fonction \r\n';nb_erreur++; }
	//if(societe==""){ msg_erreur += '- Société \r\n';nb_erreur++; }
	//if(photo==""){ msg_erreur += '- Photo \r\n';nb_erreur++; }
	
	//if(nb_erreur>1){ prefixe_msg_erreur += 'les champs suivants';}
	//else{prefixe_msg_erreur += 'le champ suivant';}
	
	if(msg_erreur!=""){
		jAlert(msg_erreur);
		return false;
	}

});

$('.ajouter_champ_sup').toggle(function() {
	$(this).parent().next('div').slideDown('fast');
}, function() {
	$(this).parent().next('div').slideUp('fast');
});


$('#type_champ').change(function(e){
	val = $(this).val();
	
	if(val != '')
	{
		switch(val)
		{
			case 'text':
				lechamp = '<input type="text" name="valeur_champ" class="textfield" id="valeur_champ" />';
			break;
			case 'textarea':
				lechamp = '<textarea name="valeur_champ" id="valeur_champ" cols="40" rows="3"></textarea>';
			break;
			case 'checkbox':
				lechamp = '<input type="checkbox" name="valeur_champ" id="valeur_champ" />&nbsp;';
			break;
			default :
				lechamp = '';
			break;
		}
		
		$('.valeurchamp').html(lechamp);
	}	
});


$('.save_champ').click(function(e){
	e.preventDefault();
	
	nom = $('#nom_champ').val();
	type = $('#type_champ').val();
	valeur = '';
	
	if (type == "text") {
		valeur = $('#valeur_champ').val();
	}
	else if(type == "textarea"){
		valeur = $('#valeur_champ').val();
	}
	else if(type == "checkbox"){
		if($('#valeur_champ:checked').length > 0){
			valeur = 'on';
		}
		else{
			valeur = '';
		}
	}

	$.ajax({
		type: "post",
		url: manager_url + $('#table_champ').text() + "/add_champ_sup",
		data: "nom="+nom+"&type="+type+"&valeur="+valeur,
		success: function(msg){

			if (type == "text") {
				champHtml = '<input type="text" name="champ'+msg+'" id="champ'+msg+'" value="'+valeur+'" class="textfield" />';
			}
			else if(type == "textarea"){
				champHtml = '<textarea name="champ'+msg+'" id="champ'+msg+'" cols="40" rows="3">'+valeur+'</textarea>';
			}
			else if(type == "checkbox"){
				if($('#valeur_champ:checked').length > 0){
					champHtml = '<input type="checkbox" name="champ'+msg+'" id="champ'+msg+'" checked="checked" />&nbsp;';
				}
				else{
					champHtml = '<input type="checkbox" name="champ'+msg+'" id="champ'+msg+'" />&nbsp;';
				}
			}
			
			var bg = $('#champs_sup p:last').hasClass('gris01') ? '' : ' class="gris01"';
			
			$('#champs_sup').append('<p'+bg+'><label for="champ'+msg+'">'+nom+' :</label>'+champHtml+'</p>');
			$('#form_ajout_champ').addClass('hide').next('div').show();
			
			$('#id_champs_sup').val( $('#id_champs_sup').val() + msg + ';' );

		}
	});
		
});
/*Champs supplémentaires dans le form de modification d'un user*/
$('.champ_sup').mouseover(function(e){
	e.preventDefault();
	$(this).find('a').removeClass('hide');
		
});
$('.champ_sup').mouseout(function(e){
	e.preventDefault();
	$(this).find('a').addClass('hide');
});

$('.champ_sup a').click(function(e){
	e.preventDefault();
	
	var node = $(this).parent();
	var rel = $(this).attr('rel');
	
	jConfirm('Etes vous sûr de vouloir supprimer ce champ?\r\n\r\nUne fois supprimé, il ne sera plus possible de le récupérer ultérieurement.', 'Suppresion d\'un champ', function(r) {
	    if(r)
		{
			$.ajax({
				type: "post",
				url: manager_url + "/users/sup_champ",
				data: "id="+rel,
				success: function(msg){
					node.remove();
				}
			});				
		}
	});	
});




/* Ajouter des champs supplémentaires dans le form contact*/
$('.contact_ajouter_site').click(function(e){
	e.preventDefault();
	$('.ajouter_sites').append('<p style="padding: 0 0 5px 5px;margin-left:189px"><input type="text" name="site_internet[]" class="textfield" value="http://" /> <select name="type_site[]"><option value="professionnel">Professionnel</option><option value="perso">Personnel</option><option value="autre">Autre</option></select> <a href="#" class="supprimer_champ" title="Supprimer"><img class="inline" src="../images/delete.png" alt="Supprimer" /></a></p>');
});

$('.contact_ajouter_mail').click(function(e){
	e.preventDefault();
	$('.ajouter_mails').append('<p style="padding: 0 0 5px 5px;margin-left:189px"><input type="text" name="mail[]" class="textfield" /> <select name="type_mail[]"><option value="professionnel">Professionnel</option><option value="perso">Personnel</option><option value="autre">Autre</option></select> <a href="#" class="supprimer_champ" title="Supprimer"><img class="inline" src="../images/delete.png" alt="Supprimer" /></a></p>');
});

$('.contact_ajouter_tel').click(function(e){
	e.preventDefault();
	$('.ajouter_tels').append('<p style="padding: 0 0 5px 5px;margin-left:189px"><input type="text" name="telephone[]" class="textfield" /> <select name="type_tel[]"><option value="domicile">Domicile</option><option value="mobile">Mobile</option><option value="professionnel" selected="selected">Professionnel</option><option value="autre">Autre</option></select> <a href="#" class="supprimer_champ" title="Supprimer"><img class="inline" src="../images/delete.png" alt="Supprimer" /></a></p>');
});

$('.contact_ajouter_adresse').click(function(e){
	e.preventDefault();
	var node_parent = $(this).parent().prev('.liste_adresses');
	
	node_parent.after('<fieldset><p style="padding-left:30px"><label>Nom de l\'adresse : </label><input name="adresse_type[]" type="text" class="textfield" /><a href="#" class="button supprimer_adresse" style="position: absolute;right:3px;top:3px"><span><img src="../images/delete.png" alt="Supprimer" />Supprimer cette adresse</span></a></p><p class="gris01" style="padding-left:30px"><label>Adresse : </label><input type="text" name="adresse[]" class="textfield" />'+
				                    '</p>'+
				                    '<p style="padding-left:30px">'+
				                        '<label>Complément : </label>'+
				                        '<input type="text" name="adresse_suite[]" class="textfield" />'+
				                    '</p>'+
				                    '<p class="gris01" style="padding-left:30px">'+
				                        '<label>Code postal : </label>'+
				                        '<input type="text" name="cp[]" class="textfield" />'+
				                    '</p>'+
				                    '<p style="padding-left:30px">'+
				                        '<label>Ville : </label>'+
				                        '<input type="text" name="ville[]" class="textfield" />'+
				                    '</p>'+
				                    '<p class="gris01" style="padding-left:30px">'+
				                        '<label>Pays : </label>'+
				                        '<input type="text" name="pays[]" class="textfield"  />'+
				                    '</p></fieldset>');
});
/*supprimer une adresse web, un tel, un mail*/
$('.supprimer_champ').live('click', function(e){
	e.preventDefault();
	$(this).parent('p').remove();	
});
/*supprimer une adresse*/
$('.supprimer_adresse').live('click', function(e){
	e.preventDefault();
	$(this).parents('fieldset').remove();	
});

/* Supprimer un champ déjà up "mail" */

$('.supprimer_champ_mail').click(function(e){
	e.preventDefault();
	
	var rel = $(this).attr('rel');
	var cid = $('#c_id').val();
	var node= $(this).parent();
	
	jConfirm('Etes vous sûr de vouloir supprimer ce champ?\r\n\r\nUne fois supprimé, il ne sera plus possible de le récupérer ultérieurement.', 'Suppresion d\'un champ', function(r) {
	    if(r)
		{
			$.ajax({
				type: "post",
				url: manager_url + "/contacts/supchamp/mail",
				data: "id="+rel+"&cid="+cid,
				success: function(msg){
					node.remove();
				}
			});				
		}
	});		
});



/* Supprimer un champ déjà up "site" */

$('.supprimer_champ_site').click(function(e){
	e.preventDefault();
	
	var rel = $(this).attr('rel');
	var cid = $('#c_id').val();
	var node= $(this).parent();
	
	jConfirm('Etes vous sûr de vouloir supprimer ce champ?\r\n\r\nUne fois supprimé, il ne sera plus possible de le récupérer ultérieurement.', 'Suppresion d\'un champ', function(r) {
	    if(r)
		{
			$.ajax({
				type: "post",
				url: manager_url + "/contacts/supchamp/site",
				data: "id="+rel+"&cid="+cid,
				success: function(msg){
					node.remove();
				}
			});				
		}
	});		
});





/* Supprimer un champ déjà up "tel" */

$('.supprimer_champ_tel').click(function(e){
	e.preventDefault();
	
	var rel = $(this).attr('rel');
	var cid = $('#c_id').val();
	var node= $(this).parent();
	
	jConfirm('Etes vous sûr de vouloir supprimer ce champ?\r\n\r\nUne fois supprimé, il ne sera plus possible de le récupérer ultérieurement.', 'Suppresion d\'un champ', function(r) {
	    if(r)
		{
			$.ajax({
				type: "post",
				url: manager_url + "/contacts/supchamp/tel",
				data: "id="+rel+"&cid="+cid,
				success: function(msg){
					node.remove();
				}
			});				
		}
	});		
});



$('.supprimer_champ_adresse').click(function(e){
	e.preventDefault();
	
	var rel = $(this).attr('rel');
	var cid = $('#c_id').val();
	var node= $(this).parent();
	
	jConfirm('Etes vous sûr de vouloir supprimer cette adresse?\r\n\r\nUne fois supprimée, il ne sera plus possible de la récupérer ultérieurement.', 'Suppresion d\'un champ', function(r) {
	    if(r)
		{
			$.ajax({
				type: "post",
				url: manager_url + "/contacts/supchamp/adresse",
				data: "id="+rel+"&cid="+cid,
				success: function(msg){
					node.remove();
				}
			});				
		}
	});		
});
/*VOIR CONTACT*/
$('.infos_contact dl:odd').addClass('gris01');








/*********************************************
****************** VERSIONS ******************
*********************************************/


if($('#table_version').length != 0){
	$('.description_td').click(function(e){
		e.preventDefault();		
		$(this).parent().next('tr').toggle();
	});
}



if ($('#form_commande').length != 0) {
	$('#commande_submit').click(function(e){
		e.preventDefault();
		var error_form = 0;
		
		$('#error_societe').css('display', 'none');
		$('#error_prenom').css('display', 'none');
		$('#error_nom').css('display', 'none');
		$('#error_adresse').css('display', 'none');
		$('#error_code_postal_exist').css('display', 'none');
		$('#error_code_postal_format').css('display', 'none');
		$('#error_ville').css('display', 'none');
		$('#error_mail_exist').css('display', 'none');
		$('#error_mail_format').css('display', 'none');
		$('#error_mobile_format').css('display', 'none');
		$('#error_cgv').css('display', 'none');
		
		if ($('#accept_cgv').is(':checked')) {
			error_form = 0;
		} else {
			$('#error_cgv').css('display', 'block');
			error_form = 1;
		}
		
		if ($('#societe').val() == '') {
			error_form = 1;
			$('#error_societe').css('display', 'block');
		}
		
		if ($('#prenom').val() == '') {
			error_form = 1;
			$('#error_prenom').css('display', 'block');
		}
		
		if ($('#nom').val() == '') {
			error_form = 1;
			$('#error_nom').css('display', 'block');
		}
		
		if ($('#adresse').val() == '') {
			error_form = 1;
			$('#error_adresse').css('display', 'block');
		}
		
		if ($('#code_postal').val() == '') {
			error_form = 1;
			$('#error_code_postal_exist').css('display', 'block');
		} else {
			
			if($('#code_postal').val().length != 5 || isNaN($('#code_postal').val())){
				error_form = 1;
				$('#error_code_postal_format').css('display', 'block');				
			}
		}
		
		if ($('#ville').val() == '') {
			error_form = 1;
			$('#error_ville').css('display', 'block');
		}
		
		if ($('#email').val() == '') {
			error_form = 1;
			$('#error_mail_exist').css('display', 'block');
		} else {
			
			if(VerifEmail('email') == false){
				error_form = 1;
				$('#error_mail_format').css('display', 'block');				
			}
		}
		
		if($('#portable').val().length != 10 || isNaN($('#portable').val())){
			error_form = 1;
			$('#error_mobile_format').css('display', 'block');				
		}
		
		if(error_form == 0){
			$('#form_commande').submit();
		}
	});
}



if ($('#form_retour_coordonnees').length != 0) {
	$('#submit_form_retour').click(function(e){
		e.preventDefault();
		$('#form_retour_coordonnees').submit();
	});	
}


if($('#paiement_cheque').length != 0){
	var ref = $('#ref_bon').val();
	$('#paiement_cheque').click(function(){
		alert('Veuillez contacter le responsable informatique : Err.l.1541'); 
	});
}

if($('#paiement_virement').length != 0){
	var ref = $('#ref_bon').val();
	var mail = $('input[name=user_mail]').val();
	$('#paiement_virement').click(function(){
		alert('Veuillez contacter le responsable informatique : Err.l.1549');
			
		
	});
}

if($('#ok_confirm_modif').length > 0){
	$('#ok_confirm_modif').click(function(e){
		e.preventDefault();
		window.parent.location.href = manager_url + 'preferences/relancesAuto';
	});
}

// Popup de confirmation de l'envoi du mail au support
if($('#ok_confirm_mail').length > 0){
	$('#ok_confirm_mail').click(function(e){
		e.preventDefault();
		window.parent.location.href = manager_url;
	});
}

// Popup de confirmation de la cloture de la discussion
if($('#ok_confirm_cloture').length > 0){
	$('#ok_confirm_cloture').click(function(e){
		e.preventDefault();
		window.parent.location.href = manager_url;
	});
}


//fin du document.ready(); 
});







// init du calendrier affiché sur la page "Congés"
function calendar_init(){
	$(document).ready(function(){

		$('#calendar').fullCalendar({
			editable: false,
			disableDragging : false,
			events: "/manager/conges/dispCalendarJson",
			firstDay:1,		
			
			loading: function(bool) {
				if (bool) {
					$('#loading').show();
					//$('#calendar').hide();
				}
				else {
					$('#loading').fadeOut();
					//$('#calendar').show();
				}
			}
			
		});

	});
}










var date_deb = '';
var date_fin = '';

// fonction pour comparer deux dates saisies afin de voir si la date de début < date de fin
// Il faut manipuler un peu la date pour pouvoir faire la comparaison..
function compareDate(date_deb, date_fin)
{
	elem1 = date_deb.split('/');
	elem2 = date_fin.split('/');
	
	new_date_deb = elem1[2] + '/' + elem1[1] + '/' + elem1[0];
	new_date_fin = elem2[2] + '/' + elem2[1] + '/' + elem2[0];
	
	if(new_date_deb <= new_date_fin){
		return true;
	}
	else{
		return false;
	}
} 








/******************************************************************
*********** FONCTIONS MATHEMATIQUES DE BASE ***********************
******************************************************************/

function Nombre(nb)
{
	if(nb == '' || isNaN(nb))
	{
		return 0.00;
	}
	else
	{
		return parseFloat(nb);
	}
}

function arrondir(nb)
{
	return Math.round(Nombre(nb) * 10000)/10000;	
}

function arrondir(nb, precision)
{
	puissance = Math.pow(10,Nombre(precision));
	return Math.round(nb * puissance)/puissance;	
}


/*********************************************************************
****************** FONCTIONS RELATIVES AUX VERSIONS ******************
*********************************************************************/

function quantiteMoins()
{
	if($('#span_quantite').html() <= 1)
	{
		return false;
	}
	else
	{
		$('#span_quantite').html(parseInt($('#span_quantite').html()) - parseInt('1'));
		$('input[name="version_duree"]').val(parseInt($('input[name="version_duree"]').val()) - parseInt('1'));
		$('#annee_fin').html(parseInt($('#annee_fin').html()) - parseInt('1'));
		$('input[name="annee_fin"]').val(parseInt($('input[name="annee_fin"]').val()) - parseInt('1'));
		$('.prix_total').html($('#prix_unitaire').html() * $('#span_quantite').html());
		return true;
	}
}

function quantitePlus()
{
	if($('#span_quantite').html() >= 3)
	{
		return false;
	}
	else
	{
		$('#span_quantite').html(parseInt($('#span_quantite').html()) + parseInt('1'));
		$('input[name="version_duree"]').val(parseInt($('input[name="version_duree"]').val()) + parseInt('1'));
		$('#annee_fin').html(parseInt($('#annee_fin').html()) + parseInt('1'));
		$('input[name="annee_fin"]').val(parseInt($('input[name="annee_fin"]').val()) + parseInt('1'));
		$('.prix_total').html($('#prix_unitaire').html() * $('#span_quantite').html());
		return true;
	}
}


/**
 * Permet de v�rifier qu'une adresse email est valide
 * @param	string		Identifiant du champ du formulaire � tester
 */


function VerifEmail(email)
{
	adresse = $('#'+email).val();
	var place = adresse.indexOf("@",1);
	var point = adresse.indexOf(".",place+1);
	
	if ((place > -1)&&(adresse.length >2)&&(point > 1))
	{
		return(true);
	}
	return(false);
}

/*#######################################################################
#                               VERIF FORM ( A.F )                      #
#######################################################################*/

$(document).ready(function(){
    // Ajout de notre méthode
    $.validator.addClassRules({
        checkinput:{
            required: true
        }
    });
    // Initialisation du plugin
    $("#devis_form").validate();
});


/*#######################################################################
#                       Changement TVA - Facturation                   #
#######################################################################*/

$('.tvaElem').live('click', function(e){
	e.preventDefault();
	type_doc = $('#type_doc').text();
	
	$(this).html('<select name="" class="tvaElem"><option value="0">0</option><option value="6">6</option><option value="12">12</option><option value="21">21</option></select>');
	
	$('.tvaElem option[value=' + $(this).text() + ']').attr('selected', 'selected');
		$('.tvaElem2').focus();
		
	
	
	
});

$('.tvaElem').live('blur', function(){
	valeur = $(this).val();
						
	$(this).parent().html('<a href="#" class="tvaElem underline">'+valeur+'</a>');
	
});


$('.tvaElem').live('change', function(){
	type_doc = $('#type_doc').text();
	
	valeur = $(this).val();

var	id_ligne = $(this).parent().parent().find('input').val(); 
var	numDoc = $(this).parent().parent().find('#doc_id').val(); 
	
	$(this).parent().html('<a href="#" class="tvaElem underline">'+valeur+'</a>');
	/*
	$(this).parent().parent().parent().parent().parent().parent().children('#blocTVA').html('Veuillez réactualiser la page pour mettre à jour vos données...');
	*/
	saveTVAfact(valeur,id_ligne, numDoc);
});



function saveTVAfact(tva, id_ligne, numDoc)
{ 
		/*$('div#blocTVA').html("<h2>Veuillez recharger la page afin d'actualiser les informations de facturation</h2>");*/
		window.location.reload();
		/*location.assign(location.href);*/
		$.ajax({
			type: "post",
			url: 'modifier_une_facture.html?action=majTVA&tva=' + tva + '&id=' + id_ligne  + '&numDoc=' + numDoc,
			data: "tva=" + tva + "$id=" + id_ligne + "$numDoc=" + numDoc
		});
		
		
		/*
		data: "tva=" + tva + "&id=" + id_ligne;
			xhr.open("POST", "modifier_une_facture.html", true);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");                  
			xhr.send(data);	
			
			
			obj = new Image();
			obj.src = 'modifier_une_facture.html?tva='+escape(tva)+'&id='+escape(id_ligne); 
		*/		
}



/*

				MODULE CHANTIER... ACTIVATION ET ARCHIVATION D'UN CHANTIER

*/


$('.select_status').live('blur', function(){
	valeur = $(this).val();
	
	if(valeur == 'Archivé' || valeur == 'Archivée'){
		var diode = 'diode_grise.png';
	}else{
		var diode = 'diode_verte.png';
	}
						
	$(this).parent().html('&nbsp;<img src="../images/' + diode + '" alt="' + valeur + '" class="inline" />&nbsp;<a href="#" class="status underline">'+valeur+'</a>');
   
});

$('.select_status').live('change', function(){
	type_doc = 'chantiers';
	
	valeur = $(this).val();


var	dev_id = $(this).parent().siblings('#noeud').find('input').val(); // Important, ça m'a pris une plombe pour trouver ça !!!!!
	
	
	if(valeur == 'Archivé' || valeur == 'Archivée'){
		var diode = 'diode_grise.png';
		var quest = confirm('Attention, si vous archivez ce chantier, tous les documents en rapport avec celui-ci le seront également. Etes-vous sur de vouloir archiver ce chantier ?');
	}else{
		var diode = 'diode_verte.png';
		var quest = 'actif';
	}
	
	if(quest == true || quest == 'actif')
	{
		$(this).parent().html('&nbsp;<img src="../images/' + diode + '" alt="' + valeur + '" class="inline" />&nbsp;<a href="#" class="status underline">'+valeur+'</a>');
		saveEtat(dev_id, valeur, type_doc);
	}
	else {
		$(this).parent().html('&nbsp;<img src="../images/diode_verte.png" alt="Actif" class="inline" />&nbsp;<a href="#" class="status underline">Actif</a>');
	}
	
		
		
});


$('.status').live('click', function(e){
	e.preventDefault();
	type_doc = 'chantiers';
	$(this).parent().html('<select name="" class="select_status"><option value="Actif">Actif</option><option value="Archivé">Archivé</option></select>');
		
		
		$('.select_status option[value=' + $(this).text() + ']').attr('selected', 'selected');
		$('.select_status').focus();

	
});






$("input[class^=textfield]").bind("blur", function(){
    var valeur = this.value;
    this.value = valeur.replace(',','.');
  });
  
function pointVirgule(texte) {
   
   while(texte.indexOf(',')>-1){
    texte=texte.replace(",",".");
	}
	this.value = texte;
     return texte
	 
}



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
var manager_url = 'http://'+url+'/manager/';	

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
#                       OUBLI DE MOT DE PASSE                           #
#######################################################################*/

	// on selectionne le form contenu dans le div connexion.
  	$('#form-mot-de-passe-oublie').submit(function(){
		// on récup le login
		var login = $('#login').val();
		var entreprise = parent.$('#entreprise').val();
		
		// on teste si le login et pass sont pas vide, sinon on leur ajoute la class "error" et le formulaire retournera False
		if(login=="")
		{
			$('#login').removeClass('hide');
			return false;
		}
		else
		{
			// si l'email est rempli, on vérifie que c'est une bonne adresse
			/*
			var rel_email = /^([a-zA-Z0-9_-])+([.]?[a-zA-Z0-9_-]{1,})*@([a-zA-Z0-9-_]{2,}[.])+[a-zA-Z]{2,4}$/;
			if(!mail.match(rel_email)){jAlert('Votre adresse mail n\'est pas valide');return false;}
  		 	*/
		  
				 $.ajax({
				   type: "post",
				   url: manager_url+"log/oubliMail",
				   data: "login="+login,
				   success: function(msg){
				   	
						jAlert("Un mail vous a été envoyé à l\'adresse mail : " + msg, '', function(){
							parent.Shadowbox.close();
						});
					
					
					return false;
				   }
				 });	
				 
				  return false;		
		}
	});
   
  
/*#######################################################################
#                       LISTING DES USERS                          		#
#######################################################################*/
	var chp_nom = $('#nom').val();
	var chp_prenom = $('#prenom').val();
	var liste_val_defaut = new Array('Nom', 'Prénom');
	
 $('.user').click(function(){
 	// on ferme tous les li ouverts pour ne ré ouvrir que celui sur lequel on a cliqué
  	$('ul li.user').removeClass('ouvert').addClass('ferme').next('li').hide();;
	
  	if ($(this).hasClass('ferme')) {
		$(this).removeClass('ferme').addClass('ouvert');
	}
	else {
		$(this).removeClass('ouvert').addClass('ferme');
	}

	$(this).next('li').slideToggle('fast');
	//$(this).children('li.masque').addClass('plop');
});
 
/*****************************
***** SUPPRIMER USER *********
*****************************/
$('.delete_collaborateur').click(function(){
	id_user = $(this).attr('rel');
	
	jConfirm('Voulez vous vraiment supprimer ce collaborateur?\r\n\r\nUne fois supprimé, il ne sera plus possible de le récupérer ultérieurement.', 'Suppression d\'une fiche de paie', function(r){		
		if (r) 
		{
			 $.ajax({
			   	type: "post",
		   		url: manager_url+"users/del",
		  	 	data: "id_user="+id_user,
			   	success: function(msg){
					$(location).attr('href',manager_url+'utilisateurs');
			   }
			 });		
		}
	});

});


$('.delete_contact').click(function(){
	id_contact = $(this).attr('rel');
	
	jConfirm('Voulez vous vraiment supprimer ce contact?\r\n\r\nUne fois supprimé, il ne sera plus possible de le récupérer ultérieurement.', 'Suppression d\'un contact', function(r){		
		if (r) 
		{
			 $.ajax({
			   	type: "post",
		   		url: manager_url+"contacts/del",
		  	 	data: "id_contact="+id_contact,
			   	success: function(msg){
					$(location).attr('href',manager_url+'contacts');
			   }
			 });		
		}
	});

});

/****************************
***** AJOUT NOUVEL USER *****
*****************************/ 

if ($('#error_limit').length > 0) {
	$('#lien_user_add').click(function(){
		Shadowbox.open({
	        content:    manager_url + 'users/popupErrorCreation',
	        player:     "iframe",
	        title:      "Limite atteinte pour votre version",
	        height:     180,
	        width:      400
	    });
	});
}


$('#ajout_user input[type=text]').focus(function(){
	if (jQuery.inArray($(this).val(), liste_val_defaut) != -1) {
		$(this).val('');
	}
});
 
 
$('#ajout_user input[type=text]').blur(function(){
	if($(this).val()==""){
		$(this).val($(this).attr("title"));
	}
});
   
$('#login').keyup(function(){
	 $.ajax({
	   type: "post",
	   url: manager_url+"users/verif_pseudo",
	   data: "pseudo="+$(this).val(),
	   success: function(msg){
	     if(msg==1){
		 	$('.login_error').show();	
		 }
		 else{
		 	$('.login_error').hide();	
		 }
	   }
	 });  	
});

$('#mail').blur(function(){
		var email = $(this).val();
		
		var rel = /^([a-zA-Z0-9_-])+([.]?[a-zA-Z0-9_-]{1,})*@([a-zA-Z0-9-_]{2,}[.])+[a-zA-Z]{2,3}$/;
		if(email.match(rel)){	
			$('.check_email').css('display','none').text('');
		}else{
			$('.check_email').css('display','inline').text('Email incorrect');
		}	
});
  
/*== Ajout d'un nouveau groupe dans le formulaire d'ajout d'user ==*/
$('.ajouter_groupe').click(function(){
	nom_groupe = prompt('Entrez le nom du groupe:');
	
	if (nom_groupe != "" && nom_groupe!=null) {
		$.ajax({
			type: "post",
			url: manager_url + "groupes/add_ajax",
			data: "groupe=" + nom_groupe,
			success: function(msg){
				if (msg != "") {
					$("#groupe").append('<option value="' + msg + '" selected="selected">' + nom_groupe + '</option>');
				}
				else {
					jAlert('Le groupe `' + nom_groupe + '` existe déjà');
				}
			}
		});
	} 
});

$('#ajout_user').submit(function(){
	prefixe_msg_erreur = 'Merci de vérifier de bien avoir correctement renseigné ';
	msg_erreur = '';
	nb_erreur = 0;
	
	login = $('#login').val();
	mail = $('#mail').val();
	password = $('#password').val();
	groupe = $('#groupe').val();
	
	if(login==""){ msg_erreur += '- Login\r\n';nb_erreur++; }
	
	//seulement dans le cadre d'un ajout
	if ($('input[type=submit]').attr('name') == "ajouter") {
		if (password == "") {
			msg_erreur += '- Mot de passe\r\n';
			nb_erreur++;
		}
	}
	
	if(mail==""){ msg_erreur += '- Adresse mail \r\n';nb_erreur++; }
	if(groupe==""){ msg_erreur += '- Groupe \r\n';nb_erreur++; }
	
	if(nb_erreur>1){ prefixe_msg_erreur += 'les champs suivants';}
	else{prefixe_msg_erreur += 'le champ suivant';}
	
	if(msg_erreur!=""){
		jAlert(prefixe_msg_erreur + ' :\r\n' + msg_erreur);
		return false;
	}
	

	
});

/****************************
**** MODIFIER UN USER *******
****************************/

$('#supprimer_avatar').click(function(){
	id = $('#img_avatar img').attr('rel');
	
	 $.ajax({
	   type: "post",
	   url: manager_url+"users/sup_avatar",
	   data: "user="+id,
	   success: function(msg){
		 	$('#img_avatar').html('<input type="file" name="avatar" id="avatar" value="" />');
	   }
	 });
	 
});

/*#######################################################################
#                       LISTING DES GROUPES                          #
#######################################################################*/

/*****************************
***** AJOUTER GROUPE *********
****************************/
$('#ajouter_groupe2').submit(function(){
	nom_groupe = $('#groupe_name').val();

	if (nom_groupe != "") {
		$.ajax({
			type: "post",
			url: manager_url + "groupes/add_ajax",
			data: "groupe=" + nom_groupe,
			success: function(msg){
				if (msg != "") {
					//parent.$('#groups').children('table').append('<tr id="lig_gp'+msg+'"><td>' + nom_groupe + '</td><td class="acenter">0</td><td><a href="' + manager_url + 'groupes/droits/' + msg + '"><img src="../images/key.png" alt="Gerer les droits" /></td><td><a href="' + manager_url + 'groupes/users_groupe/' + msg + '" class="popup"><img src="../images/magnifier.png" alt="Voir le groupe" /></td><td><a href="' + manager_url + 'groupes/edit/' + msg + '"><img src="../images/pencil.png" alt="Modifier le groupe" /></a></td></tr>');
					//parent.Shadowbox.close();
					//alert(manager_url+'groupes/droits/'+msg);
					//parent.Shadowbox.close();
					$(parent.location).attr('href',manager_url+'groupes/droits/'+msg);
				}
				else {
					jAlert('Le groupe `' + nom_groupe + '` existe déjà');
				}
			}
		});
	}
	
	return false;
});

$('#ajouter_groupe').submit(function(){
	nom_groupe = $('#groupe_name').val();

	if (nom_groupe != "") {
		$.ajax({
			type: "post",
			url: manager_url + "groupes/add_ajax",
			data: "groupe=" + nom_groupe,
			success: function(msg){
				if (msg != "") {
					parent.$("#groupe").append('<option value="' + msg + '" selected="selected">' + nom_groupe + '</option>');
					parent.Shadowbox.close();
				}
				else {
					jAlert('Le groupe `' + nom_groupe + '` existe déjà');
				}
			}
		});
	}
	
	return false;
});
	 
/*****************************
**** SUPPRIMER GROUPE*********
*****************************/

$('.sup_groupe').click(function(){
	groupe_id = $(this).attr('rel');

	if(confirm('Voulez vous vraiment supprimer ce groupe?')){
		 $.ajax({
		   type: "post",
		   url: manager_url+"groupes/del",
		   data: "groupe_id="+groupe_id,
		   success: function(msg){
		   		parent.$('#lig_gp' + groupe_id).remove();
				parent.Shadowbox.close();
		   }
		 });
	 }	
});  


/*******************************
****** MODIFIER GROUPE *********
*******************************/

$('#modifier_groupe_utilisateur').submit(function(){

	
	nom_groupe = $('#groupe_name').val();
	groupe_id = $('#groupe_id').val();
	
	if (nom_groupe != "") {
		$.ajax({
			type: "post",
			url: manager_url + "groupes/edit",
			data: "groupe_name=" + nom_groupe + "&groupe_id=" + groupe_id,
			success: function(msg){
				if (msg == "") {
					parent.$('#lig_gp' + groupe_id).children('td:first').html(nom_groupe);
					parent.Shadowbox.close();
				}
			}
		});
	}
	
	return false;
		
});


/**********************************
******* GESTION DES DROITS ********
**********************************/

$('.selectionner_tout').click(function(){
	if($(this).is(':checked'))
	{
		attribut = $(this).attr('name');
		$('input[name^=' + attribut + ']').attr('checked','checked');
	}
	else
	{
		attribut = $(this).attr('name');
		$('input[name^=' + attribut + ']').removeAttr('checked');
	}
});

/*#######################################################################
#                      GESTION FICHES DE PAIES                          #
#######################################################################*/

/*****************************
****** SUPPRIMER FICHE *******
*****************************/

$('.sup_fiche_paie').click(function(e){
	e.preventDefault;

	 val = $(this).attr('rel');
	 ligne = $(this).parent('td').parent('tr');
	 
			 
	jConfirm('Etes vous sûr de vouloir supprimer la fiche de paie?\r\n\r\nUne fois supprimée, il ne sera plus possible de la récupérer ultérieurement.', 'Suppression d\'une fiche de paie', function(r){		
		if (r) 
		{
			 $.ajax({
			   type: "post",
			   url: manager_url+"fiches_paies/del",
			   data: "fiche="+val,
			   success: function(msg){
			   		if(msg==1){
						ligne.remove();
					}
			   }
			 });		
		}
	});
});

$('.fiches_paies').click(function(){
	$(this).next('div').slideToggle();
});

/********************************
********* AJOUT FICHE ***********
********************************/

$('#utilisateur_fiche_paie').change(function(){
	val = $(this).val();

		 $.ajax({
		   type: "post",
		   url: manager_url+"fiches_paies/recupSoldeAjax",
		   data: "idUser="+val,
		   success: function(msg){
		   			$('#solde_conges').parent().removeClass('hide');
					$('#solde_conges').html(msg);
		   }
		 });	
		 	
});

$('#ajout_fiche_paie').submit(function(){
	user = $('#utilisateur_fiche_paie').val();
	fichier = $("#fichier").val();
	
	error = '';
	if(user == ''){ error += '- Veuillez renseigner la personne a qui est destinée la fiche de paie\r\n'; }
	if(fichier == ''){ error += '- Veuillez ajouter un fichier\r\n'; }
	
	if(error != ''){
		jAlert('Merci de vérifier les champs suivants:\r\n'+error);
		return false;
	}
});



/*#######################################################################
#                     GESTION DES CONGES ET RTT                         #
#######################################################################*/




/******************************************
***** ANNULER UNE DEMANDE DE CONGES *******
******************************************/

$('.sup_demande_conges').click(function(e){
	
	e.preventDefault();
	var rel = $(this).attr('rel');
	
	jConfirm('Êtes vous sur de vouloir annuler cette demande de congé?','Annulation d\'une demande de congé', function(r){	
		if (r) 
		{
				
			$.ajax({
				type: "post",
				url: manager_url + "conges/annulerdemande",
				data: "id=" + rel,
				success: function(msg){
					$(location).attr('href', manager_url + 'conges');
				}
				
			});
			
		}
	});	

});



$('#date_fin, #date_deb').change(function(){
	deb = $('#date_deb').val();
	fin = $('#date_fin').val();
	
	if(!compareDate( deb , fin ) ){
		$("#date_deb").css('border','1px solid red');
		$("#date_deb").next('span').html('La date de début ne doit pas dépasser la date de fin').show();
	}
	else{
		$("#date_deb").css('border','1px solid #BFBFBF');
		$("#date_deb").next('span').empty().hide();
	}
});

$('#ajout_conge').submit(function(){
	error = '';


	// si on a choisi une demi journée... (onglet demi actif)
	if(!$('#demi_journee').hasClass('tabs-hide'))
	{
		choix = 1;
		date_deb = $('input[name=date_demi]').val();
		demi_deb = $('input[type=radio][name=choix_demi]:checked').attr('value');
	}
	// Si on a choisi une journée entière (onglet journée actif)
	else if(!$('#journee').hasClass('tabs-hide'))
	{	
		choix = 2;
		date_deb = $('input[name=date_journee]').val();
	}
	// Si on a choisi une période (onglet période actif)
	else if(!$('#periode').hasClass('tabs-hide'))
	{
		choix = 3;
		date_deb = $('input[name=date_periode_deb]').val();
		date_fin = $('input[name=date_periode_fin]').val();
		
		demi_deb = $('input[type=radio][name=choix_periode_deb]:checked').attr('value');
		demi_fin = $('input[type=radio][name=choix_periode_fin]:checked').attr('value');
	}
	
	// la date du jour
	dateToday = $('#dateToday').val();
	
	
	if(choix == 1 || choix == 2)
	{
		if(date_deb == '')
		{
			error += 'Veuillez spécifier la date à laquelle vous souhaitez prendre votre congé\r\n';
		}
		else if(!compareDate(dateToday, date_deb))
		{
			error += 'La date que vous avez spécifiée est antérieure à la date d\'aujourd\'hui\r\n';
		}	
	}
	else
	{
		if (date_deb == "" || date_fin == "") {
			error = 'Veuillez renseigner la date de début et de fin\r\n';
		}
		else if(date_deb!= '' && !compareDate(dateToday, date_deb))
		{
			error += 'La date que vous avez spécifiée est antérieure à la date d\'aujourd\'hui\r\n';
		}	
		else if (!compareDate(date_deb, date_fin)){
			error = 'La date de début ne doit pas dépasser la date de fin\r\n';
		}	
		
	}
	
	if(choix == 1)
	{
		if(demi_deb == '')
		{
			error = 'Veuillez indiquer si vous souhaitez prendre votre matinée ou votre après-midi\r\n';
		}
	}
	
	
	if($('#type_conge').val()==""){
		error += 'Veuillez sélectionner le type de congé\r\n';	
	}
	
	if(error!=""){
		jAlert(error);
		return false;
	}

});



$('#date_deb').change(function(){
	deb = $(this).val();
	$('#date_fin_periode').val(deb);
});


//Refuser un congé, et donc ajouter un commentaire de refus.
$('#refuser_conges').submit(function(e){
	e.preventDefault();
	
	com = $('textarea[name=commentaire_refus]').val();
	conge_id = $('#cid').html();
	
		 $.ajax({
		   type: "post",
		   url: manager_url+"conges/moderer/2/"+conge_id,
		   data: "com="+com,
		   success: function(msg){
		   		parent.document.location.href= manager_url+"conges/voir/"+conge_id;
				parent.Shadowbox.close();
		   }
		 });

});



$('#cal_mois').change(function(){
	mois = $(this).val();
	annee = $('#cal_annee').val();
	$('#calendar').html('<div id="loading"></div>');
	$('#calendar').fullCalendar({
		editable: false,
		disableDragging : false,
		events: "/manager/conges/dispCalendarJson",
		firstDay:1,		
		year:annee,
		month:mois,
		
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
	
	//$('#calendar').fullCalendar('gotoDate', annee, mois, 1); 	
});

 
$('#cal_annee').change(function(){
	annee = $(this).val();
	mois = $('#cal_mois').val();
	$('#calendar').html('<div id="loading"></div>');
	$('#calendar').fullCalendar({
		editable: false,
		disableDragging : false,
		events: "/manager/conges/dispCalendarJson",
		firstDay:1,		
		year:annee,
		month:mois,
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
	
	//$('#calendar').fullCalendar('gotoDate', annee, mois, 1); 	
});
  

$('.fc-button-next').live('click', function(e){
	annee = $('#cal_annee').val();
	mois = $('#cal_mois').val();
	
	if (mois == 11) {
		mois = 0;
		annee++;
	}
	else {
		mois++;
	}	
	
	$('#calendar').html('<div id="loading"></div>');
	$('#calendar').fullCalendar({
		editable: false,
		disableDragging : false,
		events: "/manager/conges/dispCalendarJson",
		firstDay:1,		
		year:annee,
		month:mois,
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
	
	$('#cal_annee option[value='+annee+']').attr('selected', 'selected');
	$('#cal_mois option[value='+mois+']').attr('selected', 'selected');
});
  

$('.fc-button-prev').live('click', function(e){
	annee = $('#cal_annee').val();
	mois = $('#cal_mois').val();
	
	if (mois == 0) {
		mois = 11;
		annee--;
	}
	else {
		mois--;
	}	
	
	$('#calendar').html('<div id="loading"></div>');
	$('#calendar').fullCalendar({
		editable: false,
		disableDragging : false,
		events: "/manager/conges/dispCalendarJson",
		firstDay:1,		
		year:annee,
		month:mois,
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
	
	$('#cal_annee option[value='+annee+']').attr('selected', 'selected');
	$('#cal_mois option[value='+mois+']').attr('selected', 'selected');
});
  
  
  

//désactivation de l'autocomplétion des navigateurs
$('#demandeur_demi, #demandeur_journee, #demandeur_periode').attr('autocomplete','off');

$('#autoSuggestionsList_demandeur_demi li, #autoSuggestionsList_demandeur_journee li, #autoSuggestionsList_demandeur_periode li').live('click', function(){
	$('#demandeur_demi, #demandeur_journee, #demandeur_periode').val($(this).attr('rel'));
	$('#dem').val($(this).children('span').text());
});


$('#demandeur_demi').blur(function(){
		setTimeout("$('#suggestions_demandeur_demi').hide();", 200);	
});
$('#demandeur_journee').blur(function(){
		setTimeout("$('#suggestions_demandeur_journee').hide();", 200);	
});
$('#demandeur_periode').blur(function(){
		setTimeout("$('#suggestions_demandeur_periode').hide();", 200);	
});


$('#demandeur_demi').keyup(function(){
		inputString = $(this).val();
	
		if(inputString.length == 0) {
			$('#suggestions_demandeur_demi').hide();
		} else {
			page = $('#autoSuggestionsList_demandeur_demi').next('div').text();
			
			$.post(manager_url+page, {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions_demandeur_demi').show();
					$('#autoSuggestionsList_demandeur_demi').html(data);
				}
			});
		}	
});
 
$('#demandeur_journee').keyup(function(){
		inputString = $(this).val();
	
		if(inputString.length == 0) {
			$('#suggestions_demandeur_journee').hide();
		} else {
			page = $('#autoSuggestionsList_demandeur_journee').next('div').text();
			
			$.post(manager_url+page, {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions_demandeur_journee').show();
					$('#autoSuggestionsList_demandeur_journee').html(data);
				}
			});
		}	
});

$('#demandeur_periode').keyup(function(){
		inputString = $(this).val();
	
		if(inputString.length == 0) {
			$('#suggestions_demandeur_periode').hide();
		} else {
			page = $('#autoSuggestionsList_demandeur_periode').next('div').text();
			
			$.post(manager_url+page, {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions_demandeur_periode').show();
					$('#autoSuggestionsList_demandeur_periode').html(data);
				}
			});
		}	
});



/******************************************
************ DEMANDE DE CONGES ************
******************************************/

$('input[name=duree_conge]').click(function(){
	valeur = $(this).val();
	
	if(valeur == "demi")
	{
		$('#demi_journee_conge').removeClass('hide');
		$('#journee_conge').addClass('hide');
		$('#periode_conge').addClass('hide');
	}
	else if(valeur == "journee")
	{
		$('#journee_conge').removeClass('hide');
		$('#demi_journee_conge').addClass('hide');
		$('#periode_conge').addClass('hide');		
	}
	else if(valeur == "periode")
	{
		$('#periode_conge').removeClass('hide');
		$('#journee_conge').addClass('hide');
		$('#demi_journee_conge').addClass('hide');			
	}
});



$('.onglet_conge').click(function(){
	$('.date_conges').val('');
});




/*******************************************************
************* GESTION DES TYPES DE CONGES **************
*******************************************************/

 $('.sup_type_conges').live('click' , function(e){
 	e.preventDefault();
	
	id = $(this).attr('rel');
	ligne = $(this).parents('tr');
	
	jConfirm('Etes vous sûr de vouloir supprimer ce type de congé?\r\n\r\nUne fois supprimé, il ne sera plus possible de le récupérer ultérieurement.', 'Suppresion d\'un type de congé', function(r){
		if (r) {
			$.ajax({
				type: "post",
				url: manager_url + "preferences/supTypeConge",
				data: "id=" + id,
				success: function(msg){
					$(location).attr('href', manager_url + 'preferences/type_conges');
					//ligne.remove();
				}
				
			});
		}
	});
		
 });
 
 
 
$('#ajouter_type_conge').submit(function(){
	if($('#type_conge').val() == ''){
		jAlert('Vous devez spécifier le type de congé que vous souhaitez créer');
		return false;
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

/*******************************
***** AJOUTER GROUPE ***********
*******************************/

$('#ajouter_groupe_contact2').submit(function(){
	nom_groupe = $('#groupe_name').val();

	if (nom_groupe != "") {
		$.ajax({
			type: "post",
			url: manager_url + "contacts/add_gp_ajax",
			data: "groupe=" + nom_groupe,
			success: function(msg){
				if (msg != "") {
					parent.$('#contact_gp').children('table').append('<tr id="lig_gp'+msg+'"><td>' + nom_groupe + '</td><td class="acenter">0</td><td><a href="' + manager_url + 'contacts/users_groupe/' + msg + '" class="popup"><img src="../images/magnifier.png" alt="Voir le groupe" /></td><td><a href="' + manager_url + 'contacts/gp_edit/' + msg + '"><img src="../images/pencil.png" alt="Modifier le groupe" /></a></td></tr>');
					parent.Shadowbox.close();
				}
				else {
					jAlert('Le groupe `' + nom_groupe + '` existe déjà');
				}
			}
		});
	}
	
	return false;
});

$('#ajouter_groupe_contact').submit(function(){
	nom_groupe = $('#groupe_name').val();

	if (nom_groupe != "") {
		$.ajax({
			type: "post",
			url: manager_url + "contacts/add_gp_ajax",
			data: "groupe=" + nom_groupe,
			success: function(msg){
				if (msg != "") {
					parent.$("#categorie_contact").append('<option value="' + msg + '" selected="selected">' + nom_groupe + '</option>');
					parent.Shadowbox.close();
				}
				else {
					jAlert('Le groupe `' + nom_groupe + '` existe déjà');
				}
			}
		});
	}
	
	return false;
});

/************************************************
******* MODIFIER GROUPE DE CONTACT **************
************************************************/

$('#modifier_groupe_contact').submit(function(){

	
	nom_groupe = $('#groupe_name').val();
	groupe_id = $('#groupe_id').val();
	
	if (nom_groupe != "") {
		$.ajax({
			type: "post",
			url: manager_url + "contacts/gp_edit",
			data: "groupe_name=" + nom_groupe + "&groupe_id=" + groupe_id,
			success: function(msg){
				if (msg == "") {
					parent.$('#lig_gp' + groupe_id).children('td:first').html(nom_groupe);
					parent.Shadowbox.close();
				}
			}
		});
	}
	
	return false;
		
});



/*****************************
**** SUPPRIMER GROUPE*********
*****************************/

$('.sup_groupe_contact').click(function(){
	groupe_id = $(this).attr('rel');

	if(confirm('Voulez vous vraiment supprimer ce groupe?')){
		 $.ajax({
		   type: "post",
		   url: manager_url+"contacts/gp_del",
		   data: "groupe_id="+groupe_id,
		   success: function(msg){
		   		parent.$('#lig_gp' + groupe_id).remove();
				parent.Shadowbox.close();
		   }
		 });
	 }	
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
	

	// on recup le prix qu'on convertit de string en float
	prix = Nombre($('.modif_prix_ht').val());	
	
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
					remise = prix * (montant_remise / 100);
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
			prix_ht = prix_ht / ((Nombre($('.modif_remise').val()) / 100));
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
					remise = prix * (montant_remise / 100);
				}
			}
		}
		
		// on met à jour le champ caché remise.
		$('#montant_remise').val(remise);
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
	 
	if(etat == 'Payée' || etat == "Validé"){
	    Shadowbox.open({
	        content:    'change_etat.html?origine=' + type_document + '&etat=' + etat + '&doc_id=' + doc_id,
	        player:     "iframe",
	        title:      "Changer l\'état",
	        height:     100,
	        width:      300
	    });
	}
	else{
		$.ajax({
			type: "post",
			url: 'change_etat.html?origine=' + type_document + '&etat=' + etat + '&doc_id=' + doc_id,
			data: "iddoc=" + doc_id + "&etat="+ etat + "$origine="+type_document
		});		
	}
}

$('.select_etat').live('blur', function(){
	valeur = $(this).val();
	
	if(valeur == 'Brouillon' || valeur == 'En attente'){
		var diode = 'diode_orange.png';
	}else if(valeur == 'Impayée' || valeur == 'Retard' || valeur == 'Refusé'){
		var diode = 'diode_rouge.png';
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
	}else{
		var diode = 'diode_verte.png';
	}
	
	
	
	$(this).parent().html('&nbsp;<img src="../images/' + diode + '" alt="' + valeur + '" class="inline" />&nbsp;<a href="#" class="changer_etat underline">'+valeur+'</a>');
	saveEtat(dev_id, valeur, type_doc);	
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
			$(this).parent().html('<select name="" class="select_etat"><option value="Brouillon">Brouillon</option><option value="En attente">En attente</option><option value="Validé">Validé</option><option value="Refusé">Refusé</option></select>');
		}
		else 
			if (type_doc == 'factures') {
				$(this).parent().html('<select name="" class="select_etat"><option value="Brouillon">Brouillon</option><option value="En attente">En attente</option><option value="Impayée">Impayée</option><option value="Payée">Payée</option><option>Retard</option></select>');
			}
		else
			if (type_doc == 'bdc') {
			$(this).parent().html('<select name="" class="select_etat"><option value="En attente">En attente</option><option value="Validé">Validé</option><option value="Refusé">Refusé</option></select>');
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

/***********************************************************************
************ AUTOCOMPLETE POUR RECUP LA REFERENCE DEVIS ****************
***********************************************************************/

//désactivation de l'autocomplétion des navigateurs
$('#src_reference_devis').attr('autocomplete','off');

$('#autoSuggestionsList_reference li').live('click', function(){
	$('#src_reference_devis').val($(this).attr('rel'));
});

$('#src_reference_devis').blur(function(){
		setTimeout("$('#suggestions_reference').hide();", 200);	
});

$('#src_reference_devis').keyup(function(){
		inputString = $(this).val();
	
		if(inputString.length == 0) {
			// Hide the suggestion box.
			$('#suggestions_reference').hide();
		} else {
			page = $('#autoSuggestionsList_reference').next('div').text();
			
			$.post(manager_url+page, {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions_reference').show();
					$('#autoSuggestionsList_reference').html(data);
				}
			});
		}	
});



/***********************************************************************
********** AUTOCOMPLETE SUR LE CHOIX DU CLIENT DANS LE DEVIS ***********
***********************************************************************/

//désactivation de l'autocomplétion des navigateurs
$('#src_client').attr('autocomplete','off');

$('#autoSuggestionsList_client_devis li').live('click', function(){
	$('#src_client').val($(this).attr('rel'));
});

$('#src_client').blur(function(){
		setTimeout("$('#suggestions_client_devis').hide();", 200);	
});

$('#src_client').keyup(function(){
		inputString = $(this).val();
	
		if(inputString.length == 0) {
			// Hide the suggestion box.
			$('#suggestions_client_devis').hide();
		} else {
			page = $('#autoSuggestionsList_client_devis').next('div').text();
			
			$.post(manager_url+page, {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions_client_devis').show();
					$('#autoSuggestionsList_client_devis').html(data);
				}
			});
		}	
});





//désactivation de l'autocomplétion des navigateurs
$('#src_code_client').attr('autocomplete','off');

$('#autoSuggestionsList_code_client_devis li').live('click', function(){
	$('#src_code_client').val($(this).attr('rel'));
});

$('#src_code_client').blur(function(){
		setTimeout("$('#suggestions_code_client_devis').hide();", 200);	
});

$('#src_code_client').keyup(function(){
		inputString = $(this).val();
	
		if(inputString.length == 0) {
			// Hide the suggestion box.
			$('#suggestions_code_client_devis').hide();
		} else {
			page = $('#autoSuggestionsList_code_client_devis').next('div').text();
			
			$.post(manager_url+page, {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions_code_client_devis').show();
					$('#autoSuggestionsList_code_client_devis').html(data);
				}
			});
		}	
});


/***********************************************************************
**************** AUTOCOMPLETE SUR LE CHOIX DU CLIENT *******************
***********************************************************************/

$('#choix_client #autoSuggestionsList li').live('click', function(){

	client = $(this).children('span').text();
	
	$.ajax({
		type: "post",
		url: manager_url + "devis/ajax_client",
		data: "client=" + client,
		success: function(msg){
		
			decoupe = msg.split('###');
			$('#adresse').val(decoupe[1]);
			$('#codePostal').val(decoupe[2]);
			$('#ville').val(decoupe[3]);
			$('#code_client').val(decoupe[14]);
		}
	});
			
});

/************************************************************************
***** AUTOCOMPLETE SUR LE CHOIX DE L'USER A QUI ATTRIBUER LE DEVIS ******
************************************************************************/

//désactivation de l'autocomplétion des navigateurs
$('#createur').attr('autocomplete','off');

$('#autoSuggestionsList_createur li').live('click', function(){
	$('#createur').val($(this).attr('rel'));
	$('#id_createur').val($(this).children('span').text());
	$('#visible option[value=me]').text($(this).attr('rel'));
});

$('#createur').blur(function(){
		setTimeout("$('#suggestions_createur').hide();", 200);	
});

$('#createur').keyup(function(){
		inputString = $(this).val();
	
		if(inputString.length == 0) {
			// Hide the suggestion box.
			$('#suggestions_createur').hide();
		} else {
			page = $('#autoSuggestionsList_createur').next('div').text();
			
			$.post(manager_url+page, {queryString: ""+inputString+""}, function(data){
				if (data == '<i>Aucun utilisateur trouvé</i>') {
					$('#id_createur').val('');
				}
				else if(data.length >0) {
					$('#suggestions_createur').show();
					$('#autoSuggestionsList_createur').html(data);
				}
			});
		}	
});




/*************************************************************
******* AUTOCOMPLETE POUR LA DESIGNATION DES PRODUITS ********
*************************************************************/

		$('#ajouter_devis #designation').attr('autocomplete','off');
		
		$('#autoSuggestionsList_designation li').live('click', function(){
			$('#designation').val($(this).attr('rel'));
			$('#id_autoCompleteDesignation').val($(this).children('span').text());

			$.ajax({
					type: "post",
					url: manager_url + "devis/recupProduit",
					data: "prod=" + $('#id_autoCompleteDesignation').val(),
					success: function(msg){
						decoupe = msg.split('#!#');
						//$('#adresse').val(decoupe[1]);
						//echo $query->produit_texte . '#!#' . $query->produit_prix_ht . '#!#' . $query->produit_tva;
						$('#detail').val(decoupe[0]);
						$('#prix').val(decoupe[1]);
						
						$('#tva option[value='+decoupe[2]+']').attr('selected', 'selected');
						
						recalcLigne('');
					}
				});
					
		});
		
		$('#designation').blur(function(){
				setTimeout("$('#suggestions_designation').hide();", 200);	
		});
		
		$('#designation').keyup(function(){
				inputString = $(this).val();
			
				if(inputString.length == 0) {
					// Hide the suggestion box.
					$('#suggestions_designation').hide();
				} else {
					page = $('#autoSuggestionsList_designation').next('div').text();
					
					$.post(manager_url+page, {queryString: ""+inputString+""}, function(data){
						if(data.length >0) {
							$('#suggestions_designation').show();
							$('#autoSuggestionsList_designation').html(data);
						}
					});
				}	
		});


		$('.inserer_produit li').live('click' , function(){
			valeur = $(this).children('span').text();
			
			parent.$('#designation').val($(this).attr('rel'));
			parent.$('#id_autoCompleteDesignation').val($(this).children('span').text());
			
			$.ajax({
					type: "post",
					url: manager_url + "devis/recupProduit",
					data: "prod=" + valeur,
					success: function(msg){
						//alert(msg);
						
						decoupe = msg.split('#!#');
						//alert(decoupe[0]);
						//$('#adresse').val(decoupe[1]);
						//echo $query->produit_texte . '#!#' . $query->produit_prix_ht . '#!#' . $query->produit_tva;
						parent.$('#detail').val(decoupe[0]);
						parent.$('#prix').val(decoupe[1]);
						
						parent.$('#tva option[value='+decoupe[2]+']').attr('selected', 'selected');
						
						//alert('');
						
						montantTTC = arrondir(Nombre(decoupe[1]) * (1 + (Nombre(decoupe[2])/100)), 2);
						//alert(montantTTC);
						
						parent.$('#montant').val(montantTTC);
						//parent.recalcLigne('');
						
						parent.Shadowbox.close();
					}
				});
		});
		
	


/**************************************
********* SUPPRIMER UN DEVIS **********
**************************************/

$('#devis_add .delete').click(function(e){
	e.preventDefault();

	idDevis = $('#documentId').val();
	
	jConfirm('Etes vous sûr de vouloir supprimer ce devis?\r\n\r\nUne fois le devis supprimé, il ne sera plus possible de la récupérer ultérieurement.', 'Suppresion d\'un devis', function(r) {
	    if(r)
		{
				$.ajax({
					type: "post",
					url: manager_url + "devis/deleteDevis",
					data: "iddevis=" + idDevis,
					success: function(msg){
					
						window.location = manager_url+'devis';
						if (msg != '') {
							jAlert('Une erreur est survenue dans la suppresion du devis.','');
						}
					}
				});			
		}
	});
	
});






/****************************************************
********** AJOUTER ET ENREGISTRER DEVIS *************
****************************************************/

$('#ajouter_devis, #ajouter_facture').submit(function(){
	societe = $('#inputString').val();
	
	error = '';
	
	if($('#type_document').val() == "factures")
	{
		var doc = 'cette facture';
	}
	else
	{
		var doc = 'ce devis';
	}
	
	if($('#netTTC').text() == ''){ error += '- Il vous faut ajouter au moins une ligne à '+doc+'\r\n'; };
	if(societe == '' || societe == 'Société'){ error += '- Société\r\n'; }

	if(error != '')
	{
		jAlert('Merci de vérifier le(s) champ(s) suivant(s):\r\n'+error);
		return false;
	}

});








/*****************************************************
************* CATEGORIES DES DEVIS ******************* 
*****************************************************/

$('#ajouter_categorie_devis').submit(function(){
	if($('#categorie_devis').val() == '')
	{
		jAlert('Veuillez renseigner le nom de la catégorie')
		return false;
	}
});



$('.sup_categorie_devis').click(function(e){
	var id_cat	= $(this).attr('rel');
	
	jConfirm('Etes vous sûr de vouloir supprimer cette catégorie?\r\n\r\L\'ensemble des devis rattachés à cette catégorie seront automatiquement réattribués à une catégorie existante.', 'Suppresion d\'une facture d\'achat', function(r) {
		if(r)
		{
			$.ajax({
				type: "post",
				url: manager_url + "preferences/supCategorieDevis",
				data: "id=" + id_cat,
				success: function(msg){
					window.location = manager_url + 'preferences/categoriesDevis';
				}
			});
		}
	
	});
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







/*****************************************************
************ CATEGORIES DES FACTURES ***************** 
*****************************************************/

$('#ajouter_categorie_factures').submit(function(){
	if($('#categorie_factures').val() == '')
	{
		jAlert('Veuillez renseigner le nom de la catégorie')
		return false;
	}
});





$('.sup_categorie_facture').click(function(e){
	var id_cat	= $(this).attr('rel');
	
	jConfirm('Etes vous sûr de vouloir supprimer cette catégorie?\r\n\r\L\'ensemble des factures rattachées à cette catégorie seront automatiquement réattribuées à une catégorie existante.', 'Suppresion d\'une facture d\'achat', function(r) {
		if(r)
		{
			$.ajax({
				type: "post",
				url: manager_url + "preferences/supCategorieFactures",
				data: "id=" + id_cat,
				success: function(msg){
					window.location = manager_url + 'preferences/categoriesFactures';
				}
			});
		}
	
	});
});




$('#ajouter_facture_externe').submit(function(){
	error = '';
	
	if($('#date_vente').val() == ''){
		error += '- Date de vente\r\n';
	}
	
	if($('#designation').val() == ''){
		error += '- Désignation de la vente\r\n';
	}
	
	if(error != ''){
		jAlert('Merci de renseigner les champs suivants:\r\n'+error);
		return false;
	}
	
});	


$('.sup_facture_externe').click(function(e){
	e.preventDefault();
	
	lien = $(this);
	id_facture = lien.attr('rel');

	jConfirm('Etes vous sûr de vouloir supprimer cette facture externe?\r\n\r\nUne fois la facture supprimée, il ne sera plus possible de la récupérer ultérieurement.', 'Suppresion d\'une facture externe', function(r) {
		if(r)
		{
			$.ajax({
				type: "post",
				url: manager_url + "factures/deleteFactureExterne",
				data: "id_ext=" + id_facture,
				success: function(msg){
				
					window.location = manager_url + 'factures';
					if (msg != '') {
						jAlert('Une erreur est survenue dans la suppresion de la facture.', '');
					}
				}
			});
		}
	
	});
	
});



/*######################################################################
#							FACTURES D'ACHAT						   #
######################################################################*/

$('#ajouter_type_achat').submit(function(){
	var nom = $('#type_name').val();
	
	$.ajax({
		type: "post",
		url: manager_url + "factures_achat/add_type",
		data: "nom=" + nom,
		success: function(msg){
			if (msg != 'existedeja') {
				parent.$('#type_facture').html(msg);
				parent.Shadowbox.close();
			}
			else{
				jAlert('Ce type d\'achat existe déjà');
			}
		}
	});	
	
	return false;	
});

$('#ajouter_facture_achat').submit(function(){
	error = '';
	
	if($('#date_achat').val() == ''){
		error += '- Date d\'achat\r\n';
	}
	
	if($('#achat_nom').val() == ''){
		error += '- Désignation de l\'achat\r\n';
	}
	
	if(error != ''){
		jAlert('Merci de renseigner les champs suivants:\r\n'+error);
		return false;
	}
	
});			


$('#modifier_date_export').click(function(e){
	e.preventDefault();
	Shadowbox.open({
        content:    manager_url + 'factures_achat/popupChangeDateExport/' + $('#date_export_mois').val() + '/' + $('#date_export_annee').val(),
        player:     "iframe",
        title:      "Modifier",
        height:     180,
        width:      400
    });
});


$('#popup_changer_date_export').submit(function(e){
	e.preventDefault();

	var mois = $('#changer_mois').val();
	var annee = $('#changer_annee').val();

	parent.$('#span_date_export_mois').html(mois);
	parent.$('#span_date_export_annee').html(annee);
	parent.$('#date_export_mois').val(mois);
	parent.$('#date_export_annee').val(annee);
	
	parent.Shadowbox.close();
});


$('.sup_facture_achat').click(function(e){
	e.preventDefault();
	
	lien = $(this);
	id_facture = lien.attr('rel');

	jConfirm('Etes vous sûr de vouloir supprimer cette facture d\'achat?\r\n\r\nUne fois la facture supprimée, il ne sera plus possible de la récupérer ultérieurement.', 'Suppresion d\'une facture d\'achat', function(r) {
		if(r)
		{
			$.ajax({
				type: "post",
				url: manager_url + "factures_achat/delete",
				data: "id=" + id_facture,
				success: function(msg){
				
					window.location = manager_url + 'factures_achat';
					if (msg != '') {
						jAlert('Une erreur est survenue dans la suppresion de la facture.', '');
					}
				}
			});
		}
	
	});
	
});





//désactivation de l'autocomplétion des navigateurs
$('#achat_code_client').attr('autocomplete','off');

$('#autoSuggestionsList_achat_code_client li').live('click', function(){
	$('#achat_code_client').val($(this).attr('rel'));
	$('#fournisseur').val($(this).children('span').text());
});

$('#achat_code_client').blur(function(){
		setTimeout("$('#suggestions_achat_code_client').hide();", 200);	
});

$('#achat_code_client').keyup(function(){
		inputString = $(this).val();
	
		if(inputString.length == 0) {
			// Hide the suggestion box.
			$('#suggestions_achat_code_client').hide();
		} else {
			page = $('#autoSuggestionsList_achat_code_client').next('div').text();
			
			$.post(manager_url+page, {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions_achat_code_client').show();
					$('#autoSuggestionsList_achat_code_client').html(data);
				}
			});
		}	
});





$('.sup_type_achats').click(function(e){
	var id_type	= $(this).attr('rel');
	
	jConfirm('Etes vous sûr de vouloir supprimer ce type?\r\n\r\L\'ensemble des factures d\'achats rattachées à ce type seront automatiquement réattribuées à un nouveau type "Non classées".', 'Suppression d\'une facture d\'achat', function(r) {
		if(r)
		{
			$.ajax({
				type: "post",
				url: manager_url + "preferences/supTypeAchats",
				data: "id=" + id_type,
				success: function(msg){
					window.location = manager_url + 'preferences/typesAchats';
				}
			});
		}
	
	});
});




/*#######################################################################
#                              PRODUITS                                 #
#######################################################################*/

$('#ajouter_categorie_produit').submit(function(){
	var nom_cat = $('#categorie_produit_nom').val();
	
	 $.ajax({
	   type: "post",
	   url: manager_url+"produits/ajouter_categorie",
	   data: "nom_cat="+nom_cat,
	   success: function(msg){
			if (msg.substring(0,1) != 'C') {
				var dernier_li = parent.$('.fieldlist li:last');	
				var text_dernier_li = dernier_li.html();
				
				dernier_li.removeClass('add').html('<a href="?cat='+msg+'">'+nom_cat+'</a>');
				parent.$('.fieldlist').append('<li class="add">Ajouter une nouvelle catégorie</li>');
				parent.$('#cat_prod').append('<option value="'+msg+'">'+nom_cat+'</option>');
				parent.Shadowbox.close();
			}
			else {
				jAlert(msg);
			}
	   }
	 });
	
	return false;		 	
});

$('#ht_ttc').change(function(){
	val = $(this).val();
	$('#prix_revient_ht_ttc').text(val.toUpperCase());
})



 $('.sup_produit').click(function(e){
 	e.preventDefault();
	
	id = $(this).attr('rel');

	jConfirm('Etes vous sûr de vouloir supprimer ce produit?\r\n\r\nUne fois supprimé, il ne sera plus possible de le récupérer ultérieurement.', 'Suppresion d\'un produit', function(r){
		if (r) {
			$.ajax({
				type: "post",
				url: manager_url + "produits/sup",
				data: "id=" + id,
				success: function(msg){
					$(location).attr('href', manager_url + 'produits');
				}
				
			});
		}
	});
		
 });
 
 
$('ul.fieldlist li').mouseover(function(){
	$(this).children('span').removeClass('hide');
});

$('ul.fieldlist li').mouseout(function(){
	$(this).children('span').addClass('hide');
});


$('.sup_cat_produits').click(function(e){
 	e.preventDefault();
	
	id = $(this).attr('rel');
	
	jConfirm('Etes vous sûr de vouloir supprimer cette catégorie?\r\n\r\nUne fois supprimée, il ne sera plus possible de la récupérer ultérieurement. Les produits rattachés à cette catégorie seront perdus.', 'Suppresion d\'une catégorie produit', function(r){
		if (r) {
			$.ajax({
				type: "post",
				url: manager_url + "produits/sup_cat",
				data: "id=" + id,
				success: function(msg){
					$(location).attr('href', manager_url + 'produits');
				}
				
			});
		}
	});		
});

/*######################################################################
#					GESTION DES JOURS FERIES						   #
######################################################################*/

$('.sup_jour_ferie').click(function(){
	id_sup = $(this).parent().parent().attr('id');

		jConfirm('Etes vous sûr de vouloir supprimer ce jour férié?', 'Suppresion d\'un jour férié', function(r) {
			if(r)
			{
			
			 $.ajax({
			   type: "post",
			   url: manager_url+"preferences/supJourFerie",
			   data: "jour_id="+id_sup.substr(3),
			   success: function(msg){
					$('#'+id_sup).remove();
					jAlert('Suppresion effectuée avec succès!');
			   }
			 });
			 
			}
		}); 
});

$('#ajouter_jour_ferie').submit(function(){
	date = $('#date_ajout').val();

		 $.ajax({
		   type: "post",
		   url: manager_url+"preferences/ajouterJourFerie",
		   data: "jour="+date,
		   success: function(msg){
		   		if(msg==1)
				{
		   			$('#jours_feries > tbody').append('<tr><td>'+date+'</td><td><img src="'+static_url+'im/manager/icons16/delete.png" alt="Supprimer le jour férié" class="sup_jour_ferie" /></td></tr>');
				}
				else
				{
					('Cette date existe déjà');
				}
		   }
		 });	
		 
		 return false;	
});

/*######################################################################
#						GESTION DES AUTOCOMPLETE					   #
######################################################################*/

//désactivation de l'autocomplétion des navigateurs
$('#inputString').attr('autocomplete','off');

$('#autoSuggestionsList li').live('click', function(){
	$('#inputString').val($(this).attr('rel'));
	$('#id_autoComplete').val($(this).children('span').text());
});

$('#inputString').blur(function(){
		//alert($(this).val());
		//$('#inputString').val($(this).val());
		setTimeout("$('#suggestions').hide();", 200);	
});

$('#inputString').keyup(function(){
		inputString = $(this).val();
	
		if(inputString.length == 0) {
			// Hide the suggestion box.
			$('#suggestions').hide();
		} else {
			page = $('#autoSuggestionsList').next('div').text();
			
			$.post(manager_url+page, {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions').show();
					$('#autoSuggestionsList').html(data);
				}
			});
		}	
});


/********* AutoComplete sur les Villes *************/

$('#codePostal').attr('autocomplete','off');

$('#autoSuggestionsList_villes li').live('click', function(){
	$('#ville').val($(this).attr('rel'));
	$('#codePostal').val($(this).children('span').text());
	$('#pays').val('France');
});

$('#codePostal').keyup(function(){
		inputString = $(this).val();
	
		if(inputString.length == 0) {
			// Hide the suggestion box.
			$('#suggestions_villes').hide();
		} else {
			page = $('#autoSuggestionsList_villes').next('div').text();
			
			$.post(manager_url+'contacts/autoCompleteVille', {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions_villes').show();
					$('#autoSuggestionsList_villes').html(data);
				}
			});
		}	
});

$('#codePostal').blur(function(){
		setTimeout("$('#suggestions_villes').hide();", 200);	
});
/*#######################################################################
#                              RECHERCHE                                #
#######################################################################*/
/*apparition du formulaire*/
$('div#search a.search').click(function (e) {
	e.preventDefault();
	if($('div#search form#search_form').is(':visible')){
		$('div#search form#search_form').fadeOut('fast');
	}else{
		$('div#search form#search_form').fadeIn('fast');
	}
});
/*vider les champs*/
$('form#search_form input').click(function () {
	var valeur = $(this).val();
	if(valeur == 'Client' || valeur == 'Date' || valeur == 'Prix HT' ){
		$(this).val('');
	}
});

 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
/*#######################################################################
#                         GESTION DES EXPORTS                           #
#######################################################################*/
 		
		$('#categories_export').change(function(){
			val = $(this).val();
			actualiser_champs_export(val);			
		});
		
		
		function actualiser_champs_export(cat)
		{
			type_export = $('#type_export').val();
			
			 $.ajax({
			   type: "post",
			   url: manager_url+"export/recupPreferences",
			   data: "val="+cat+"&type_export="+type_export,
			   success: function(msg){
			   		if (msg != '') 
					{
						decoupe = msg.split('#!#');
						$('#sortable1').html(decoupe[1]);
						$('#sortable2').html(decoupe[0]);
					}
			   }
			 });			
		}
		
		function maj_champs_export()
		{
			categorie = $('#categories_export').val();
			type_export = $('#type_export').val();
			
			liste_id = '';
			
			$('ul.droptrue').find('li').each(function(){
				liste_id += $(this).text() + '##' + $(this).attr('rel') + ';';
			});

			 $.ajax({
			   type: "post",
			   url: manager_url+"export/savePreferences",
			   data: "type_export="+type_export+"&cat="+categorie+"&liste_id="+liste_id
			 });
			 					
		}
		
		if ($("ul.droptrue").length > 0) {
			$("ul.droptrue").sortable({
				connectWith: 'ul',
				forcePlaceholderSize: true,
				placeholder: 'surbrillance',
				distance: 30,
				update: function(){
				
					var cat = $('#categories_export').val();
					var liste_id = '';
					
					$('ul.droptrue').find('li').each(function(){
					
						if ($(this).hasClass('ui-state-highlight')) {
							$(this).removeClass('ui-state-highlight').addClass('ui-state-default');
						}
						
						liste_id += $(this).attr('rel') + ', ';
					});
					
					maj_champs_export();
					actualiser_champs_export(cat);
				}
			});
		}
		
		if ($("ul.dropfalse").length > 0) {
			$("ul.dropfalse").sortable({
				connectWith: 'ul',
				forcePlaceholderSize: true,
				placeholder: 'surbrillance',
				distance: 30
			});
		}

		$("#sortable1, #sortable2").disableSelection();


		$('#cocherToutesCategoriesExport').click(function(e){
 			e.preventDefault();
			$('.check_cat_export').attr('checked', true);
		});


		$('#decocherToutesCategoriesExport').click(function(e){
 			e.preventDefault();
			$('.check_cat_export').attr('checked', false);
		});

 
 
 
/*#######################################################################
#                         GESTION DES STATS                             #
#######################################################################*/




		function maj_formatsDispos(i)
		{
			liste_id = '';
			
			if(i==1){
				var type = $('ul.droptruee');
			}
			else{
				var type = $('ul.droptruee2');
			}
			
			
			
				type.find('li').each(function(){
					
					if ($(this).attr('rel') == "prefixe_doc") {
						liste_id += $(this).children('.prefixe_doc').val();
					}
					else {
						liste_id += $(this).attr('rel');
					}
					
				});
			
			
			
			
			$('#formatsDispo').html(	'<li class="ui-state-highlight" rel="prefixe_doc">Texte <input type="text" name="prefixe_doc" class="prefixe_doc textfield" style="width:50px;" /></li>' +
										'<li class="ui-state-highlight" rel="-">Séparateur &quot;-&quot;</li>' 					+
										'<li class="ui-state-highlight" rel="[JJ]">Jour au format JJ</li>' 				+
										'<li class="ui-state-highlight" rel="[MM]">Mois au format MM</li>' 				+
										'<li class="ui-state-highlight" rel="[AAAA]">Année au format AAAA</li>' 		+
										'<li class="ui-state-highlight" rel="[AA]">Année au format AA</li>' 			+
										'<li class="ui-state-highlight" rel="{3}#">Numéro Document à 3chiffres</li>' 	+
										'<li class="ui-state-highlight" rel="{4}#">Numéro Document à 4chiffres</li>' 	+
										'<li class="ui-state-highlight" rel="{5}#">Numéro Document à 5chiffres</li>');
										
			if(i == 1){
				var type 	= 'Facture';
				var id		= 'ex_num_facture';
			}
			else{
				var type 	= 'Devis';
				var id		= 'ex_num_devis';
			}

			 $.ajax({
			   type: "post",
			   url: manager_url+"preferences/saveFormats",
			   data: "type="+type+"&val="+liste_id,
			   success : function(msg){
			   		$('#'+id).text(msg).css('color', 'red').delay(800).fadeOut(300, function(){
						$('#'+id).css('color', 'black').fadeIn(300);
					});
			   }
			 });
			 		
			
		}

		
		$("ul.droptruee").sortable({
			connectWith: 'ul',
			forcePlaceholderSize: true,
			placeholder: 'surbrillance',
			distance: 30,
			update: function (){

				var liste_id = '';
				
				$('ul.droptruee').find('li').each(function(){
					
					if($(this).hasClass('ui-state-highlight')){
						$(this).removeClass('ui-state-highlight').addClass('ui-state-default');	
					}
					
					liste_id += $(this).attr('rel') + ', ';
				});

				//alert('d');
				maj_formatsDispos('1');
				//actualier_champs_export(cat);
				
			}
		});
	

		$("ul.droptruee2").sortable({
			connectWith: 'ul',
			forcePlaceholderSize: true,
			placeholder: 'surbrillance',
			distance: 30,
			update: function (){

				var liste_id = '';
				
				$('ul.droptruee2').find('li').each(function(){
					
					if($(this).hasClass('ui-state-highlight')){
						$(this).removeClass('ui-state-highlight').addClass('ui-state-default');	
					}
					
					liste_id += $(this).attr('rel') + ', ';
				});

				//alert('d');
				maj_formatsDispos('2');
				//actualier_champs_export(cat);
				
			}
		});
		$("ul.dropfalsee").sortable({
			connectWith: 'ul',
			forcePlaceholderSize: true,
			placeholder: 'surbrillance',
			distance: 30
		});

		$("#formatsDispo, #formatsChoisis, #formatsChoisis2").disableSelection();


		

		$('#formatsChoisis .prefixe_doc, #formatsChoisis2 .prefixe_doc').live('change' , function(){
			if($(this).parents('#formatsChoisis').attr('id') == 'formatsChoisis'){
				maj_formatsDispos('1');
			}
			else{
				maj_formatsDispos('2');
			}
		});








 
/*#######################################################################
#                         GESTION DES MODELES                           #
#######################################################################*/
 	
 $('.sup_modele').click(function(e){
 	e.preventDefault();
	
	id = $(this).attr('rel');
	
	jConfirm('Etes vous sûr de vouloir supprimer ce modèle?\r\n\r\nUne fois ce modèle supprimé, il ne sera plus possible de le récupérer ultérieurement.', 'Suppresion d\'un modèle', function(r){
		if (r) {
			$.ajax({
				type: "post",
				url: manager_url + "preferences/supModele",
				data: "id=" + id,
				success: function(msg){
					$(location).attr('href', manager_url + 'preferences/modelesDocuments');
				}
				
			});
		}
	});
		
 });
 
 
 
 
 





/*#######################################################################
#                       GESTION DES TAUX DE TVA                         #
#######################################################################*/
 	
 $('.sup_tva').live('click' , function(e){
 	e.preventDefault();
	
	id = $(this).attr('rel');
	ligne = $(this).parents('tr');
	
	jConfirm('Etes vous sûr de vouloir supprimer ce taux de tva?\r\n\r\nUne fois supprimé, il ne sera plus possible de le récupérer ultérieurement.', 'Suppresion d\'un taux de tva', function(r){
		if (r) {
			$.ajax({
				type: "post",
				url: manager_url + "preferences/supTva",
				data: "id=" + id,
				success: function(msg){
					//$(location).attr('href', manager_url + 'preferences/tva');
					ligne.remove();
				}
				
			});
		}
	});
		
 });
 
 
 $('#ajouter_taux_tva').submit(function(){
 	var taux = $('#taux_tva').val();
	
	if (!isNaN(taux)) {
		$.ajax({
			type: "post",
			url: manager_url + "preferences/tva",
			data: "taux=" + taux,
			success: function(msg){
				//$('#tableau_taux > tbody').append('<tr><td>' + taux + '%</td><td><a href="" class="sup_tva" rel="' + msg + '"><img src="../images/delete.png" alt="supprimer" /></a></td></tr>');
				$('.fieldlist').append('<li><span class="hide"><a href="#" title="Supprimer" rel="'+msg+'"><img src="'+static_url+'" alt="Supprimer"></span><a href="#" onclick="return false;">'+taux+'%</a></li>');
			}
			
		});
	}
	else{
		jAlert('Le taux de tva doit être au format numérique.')
	}
	return false;
				
 });
 






/********************************************
**************** NOTIFICATIONS **************
********************************************/

$('.icon-alertes').parent().click(function(e){
	e.preventDefault();
	
	var notifications = $('#notifications');
	
	if (notifications.hasClass('hide')) {
		notifications.show();
		notifications.removeClass('hide');
		
		if (!notifications.hasClass('maj')) {
			$.ajax({
				type: "post",
				url: manager_url + "notifications/maj_bulle",
				data: "action=maj",
				success: function(msg){
					//$('#tableau_taux > tbody').append('<tr><td>'+taux+'%</td><td><a href="" class="sup_tva" rel="'+msg+'"><img src="'+static_url+'im/manager/icons16/cancel.png" alt="supprimer" /></a></td></tr>');
					notifications.addClass('maj');
				}
			});
		}
		
	}
	else{
		notifications.hide();
		notifications.addClass('hide');
	}
});

$('.fermer_notifications').click(function(e){
	$('#notifications').addClass('hide');
	$('#notifications').hide();
})
	

	
/*Page notifications*/	
$('.notification').mouseover(function(){
	var dom = $(this);
	var lien_sup = $(this).find('p.user_name a.fright');
	
	if(lien_sup.hasClass('hide'))
	{
		lien_sup.removeClass('hide');
	}
});

$('.notification').mouseout(function(){
	var dom = $(this);
	var lien_sup = $(this).find('p.user_name a.fright');
	
	if(!lien_sup.hasClass('hide'))
	{
		lien_sup.addClass('hide');
	}
});

$('.notification:even').css('background-color', '#E3E3E3');
$('option:even').css('background-color', '#E3E3E3');

$('.notification a.fright').click(function(e){
	e.preventDefault();
	var li = $(this).parent().parent();
	var rel = $(this).attr('rel');

	jConfirm('Etes vous sûr de vouloir supprimer cette notification?\r\n\r\nUne fois la notification supprimée, il ne sera plus possible de la récupérer ultérieurement.', 'Suppresion d\'une notification', function(r) {
	    if(r)
		{
			
			$.ajax({
				type: "post",
				url: manager_url + "notifications/sup",
				data: "id="+rel,
				success: function(msg){
					li.fadeOut(1000).remove();
				}
			});	
			
		}
	});
});


$('#ajouter_notification').submit(function(){
	var desti 	= $('#id_autoComplete').val();
	var message	= $('textarea[name=notification_message]').val();

	if (desti != '' && message != '') {
		$.ajax({
			type: "post",
			url: manager_url + "notifications/add",
			data: "desti=" + desti + "&message=" + message,
			success: function(msg){
				jAlert('Votre notification a été ajoutée avec succès!');
				$('p.toggle').next('div').addClass('hide').slideUp('slow');
			}
		});
	}
		
	return false;
});

$('#voir_plus_notif').click(function(){
	var limit 	= $('#limit').val();
	$.ajax({
		type: "post",
		url: manager_url + "notifications/charger_notifications",
		data: "limit=" + limit,
		success: function(msg){
			$('#limit').val(parseInt(limit) + 1);
			$('#liste_notif').html($('#liste_notif').html() + msg);
			$('.notification:even').css('background-color', '#E3E3E3');
			$('option:even').css('background-color', '#E3E3E3');
		}
	});
});


/********************************************
*********** GESTION DES RACCOURCIS **********
********************************************/

$('.supprimer_raccourci').click(function(e){
	e.preventDefault();
	
	var rel = $(this).attr('rel');
	
	jConfirm('Etes vous sûr de vouloir supprimer ce raccourci?\r\n\r\nUne fois le raccourci supprimé, il ne sera plus possible de le récupérer ultérieurement.', 'Suppresion d\'un raccourci', function(r) {
	    if(r)
		{
			$.ajax({
				type: "post",
				url: manager_url + "raccourcis/sup",
				data: "id="+rel,
				success: function(msg){
					$(location).attr('href', manager_url + 'raccourcis');
				}
			});	
			
		}
	});
		
});






/********************************************
*********** GESTION DES DOCUMENTS  **********
********************************************/

$('#add_dossier').click(function(e)
{
	e.preventDefault();
	$('#add_fichier_form').slideUp('500');
	$('#add_dossier_form').slideToggle('500');
});


$('#add_fichier').click(function(e)
{
	e.preventDefault();
	$('#add_dossier_form').slideUp('500');
	$('#add_fichier_form').slideToggle('500');
});


$('#ajouter_dossier_document').submit(function(e){
	e.preventDefault();
	var erreur = false;
	
	if ($('#dir_id').length == 0) {
		var id_dir = '';
	}
	else{
		var id_dir = $('#dir_id').val();
	}
	

	var nom = $('#dossier_nom').val();
	if(nom == ''){
		$('#dossier_nom').css('border', '1px solid #EF171A');
		erreur = true;
	}
	else{
		$('.msg_error_nom').text('');
		$('#dossier_nom').css('border', '');		
	}
	
	if ($('input[name=dossier_visible]:checked').length == 0) {
		erreur = true;
	}
	else{
		var droit = $('input[name=dossier_visible]:checked').attr('value');
		$('.msg_error_droit').text('');
		$('.liste_radio').css('border', '');
	}
	
	proprio = $('#dossier_prorio').val();
	if(!erreur)
	{
			$.ajax({
				type: "post",
				url: manager_url + "documents/add_dir",
				data: "nom="+nom+"&visible="+droit+"&proprio="+proprio+"&id_dir="+id_dir,
				success: function(msg){
					if (msg != '') {
						$('#fichier_dossier').append('<option value="' + msg + '">' + nom + '</option>');
						$('#documents_arbo ul').append('<li><a href="' + manager_url + 'documents?d=' + msg + '">' + nom + '</a></li>')
					}
					else{
						$(location).attr('href', manager_url+'documents?d='+id_dir);
					}
				}
			});
	}
	
	return false;
})



$('.explorer_file').mouseover(function(){
	$(this).children('.sup_file').removeClass('hide');
})

$('.explorer_file').mouseout(function(){
	$(this).children('.sup_file').addClass('hide');
})

$('.sup_file').click(function(e){
		var dom = $(this).parent();
		e.preventDefault();
		var rel = $(this).attr('rel');
		
		jConfirm('Etes vous sûr de vouloir supprimer ce fichier?\r\n\r\nUne fois le fichier supprimé, il ne sera plus possible de le récupérer ultérieurement.', 'Suppresion d\'un fichier', function(r) {
	    	if(r)
			{
				
				$.ajax({
					type: "post",
					url: manager_url + "documents/sup_file",
					data: "id="+rel,
					success: function(msg){
						//$('#fichier_dossier').append('<option value="'+msg+'">'+nom+'</option>');
						//$('#documents_arbo ul').append('<li><a href="'+manager_url+'documents?d='+msg+'">'+nom+'</a></li>')
						dom.fadeOut(1200, function(){
							dom.remove();
						});
					}
				});	
								
			}		
		});
});



$('#documents_arbo>ul>li').mouseover(function(){

	$(this).children('span').removeClass('hide');
});

$('#exploratateur_fichier #documents_arbo li').mouseout(function(){
	$(this).children('span').addClass('hide');
});





/*********************************************
****************** TODOLIST ******************
*********************************************/

$('#ajouter_todolist').submit(function(e){
	if($('#titre').val().length == 0){
		e.preventDefault();
		alert('Le titre de la liste est obligatoire.');
	}
});

$('#todolist_ajouter_point').live('click', function(e){
	e.preventDefault();
	
	if ($('#todolist_point_titre').val() == '') {
		alert('Le titre de la tâche est obligatoire.');
	} else {
		if ($('#todolist_id').length > 0) {
			id_todolist = $('#todolist_id').val();
		}
		else {
			id_todolist = 'nouvelle_todolist';
		}
		
		nom_user = $('#todolist_point_user').val();
		
		if (nom_user != '') {
			if (id_todolist == 'nouvelle_todolist') {
				alert('Cet utilisateur ne peut être associé à une tâche.');
			}
			else {
				$.ajax({
					type: "post",
					url: manager_url + "todolist/testerUser",
					data: "nom_user=" + nom_user + "&id_todolist=" + id_todolist,
					success: function(msg){
						if (msg == 'error') {
							alert('Cet utilisateur ne peut être associé à une tâche.');
						}
						else {
							$.ajax({
								type: "post",
								url: manager_url + "todolist/ajouterPoint",
								data: "titre=" + $('#todolist_point_titre').val() + "&description=" + $('#todolist_point_description').val() + "&id_todolist=" + id_todolist + "&id_user=" + msg + "&date_butoir=" + $('#todolist_point_date_butoir').val() + "&categorie=" + $('#todolist_point_categorie').val(),
								success: function(msg){
									$('#todolist_point_titre').val('');
									$('#todolist_point_description').val('');
									$('#todolist_point_categorie').val('');
									$('#todolist_point_user').val('');
									$('#todolist_point_date_butoir').val('');
									$('#todolist_liste_points').html($('#todolist_liste_points').html() + msg);
								}
							});
						}
					}
				});
			}
		}
		else {
		
			$.ajax({
				type: "post",
				url: manager_url + "todolist/ajouterPoint",
				data: "titre=" + $('#todolist_point_titre').val() + "&description=" + $('#todolist_point_description').val() + "&id_todolist=" + id_todolist + "&id_user=0&categorie=" + $('#todolist_point_categorie').val(),
				success: function(msg){
					$('#todolist_point_titre').val('');
					$('#todolist_point_description').val('');
					$('#todolist_point_categorie').val('');
					$('#todolist_point_user option[value=0]').attr('selected', 'selected');
					$('#todolist_liste_points').html($('#todolist_liste_points').html() + msg);
				}
			});
		}
	}	
});



$('.todolist_tr').click(function(){
	id_todolist = $(this).attr('id').substring(9, $(this).attr('id').length);
	window.location.replace(manager_url + "todolist/index/" + id_todolist + "#_todolist_add");
});



$('.todolist_changer_etat').live('click', function(e){
	e.preventDefault();
	
	$(this).parent().html('<select name="todolist_select_etat" class="todolist_select_etat"><option value="À faire">À faire</option><option value="Commencée">Commencée</option><option value="Terminée">Terminée</option><option value="À préciser">À préciser</option></select>');
			
	$('.todolist_select_etat option[value=' + $(this).text() + ']').attr('selected', 'selected');
	$('.todolist_select_etat').focus();
	
});



$('.todolist_select_etat').live('blur', function(){
	valeur = $(this).val();
	
	if(valeur == 'À préciser')
	{
		var diode = 'diode_orange.png';
	}
	else if(valeur == 'À faire')
	{
		var diode = 'diode_rouge.png';
	}
	else if(valeur == 'Commencée')
	{
		var diode = 'arrow_right.png';
	}
	else
	{
		var diode = 'diode_verte.png';
	}
						
	$(this).parent().html('&nbsp;<img src="../images/' + diode + '" alt="' + valeur + '" class="inline" />&nbsp;<a href="#" class="todolist_changer_etat underline">'+valeur+'</a>');
});



$('.todolist_select_etat').live('change', function(){	
	valeur = $(this).val();
	
	if(valeur == 'À préciser')
	{
		var diode = 'diode_orange.png';
	}
	else if(valeur == 'À faire')
	{
		var diode = 'diode_rouge.png';
	}
	else if(valeur == 'Commencée')
	{
		var diode = 'arrow_right.png';
	}
	else
	{
		var diode = 'diode_verte.png';
	}
	
	var id_point = $(this).parent().parent().parent().attr('id').substring(15, $(this).parent().parent().parent().attr('id').length);
	
	$(this).parent().html('&nbsp;<img src="../images/' + diode + '" alt="' + valeur + '" class="inline" />&nbsp;<a href="#" class="todolist_changer_etat underline">'+valeur+'</a>');
	
	$.ajax({
		type: "post",
		url: manager_url + "todolist/changeEtat",
		data: "id=" + id_point + "&etat="+ valeur, 
		success: function(){
			if(valeur == 'Commencée'){
				Shadowbox.open({
			        content:    manager_url + 'todolist/popupEstimationDate/' + id_point,
			        player:     "iframe",
			        title:      "Estimation de la date de fin",
			        height:     300,
			        width:      400
			    });
			}else if(valeur == 'À préciser'){
				Shadowbox.open({
			        content:    manager_url + 'todolist/popupMessageTodolist/' + id_point,
			        player:     "iframe",
			        title:      "Demander des précisions",
			        height:     250,
			        width:      400
			    });
			}
		}
	});	
});


$('#form_estimation_date').submit(function(){		
	$.ajax({
		type: "post",
		url: manager_url + "todolist/saveEstimation",
		data: "id_point=" + $('#estimation_id_point').val() + "&date_fin=" + $('#estimation_date_fin').val(),
		success : function(){
			parent.Shadowbox.close();
		}
	});		
	
	return false;
});


$('#form_demande_precision').submit(function(){		
	$.ajax({
		type: "post",
		url: manager_url + "todolist/envoiDemandePrecision",
		data: "id_point=" + $('#precision_id_point').val() + "&message=" + $('#precision_msg').val(),
		success : function(){
			parent.Shadowbox.close();
		}
	});		
	
	return false;
});


$('.todolist_edit_point').live('click', function(e){
	e.preventDefault();
	
	var id_point = $(this).parent().parent().parent().attr('id').substring(15, $(this).parent().parent().parent().attr('id').length);
	
   	$.ajax({
	   	type: "post",
   		url: manager_url + "todolist/infosPoint",
   		data: "id=" + id_point,
   		success: function(msg){
			$('#todolist_point_bloc_gauche_' + id_point).html(msg);

			//désactivation de l'autocomplétion des navigateurs
			$('#todolist_point_categorie_modif_' + id_point).attr('autocomplete','off');
			
			$('#autoSuggestionsList_todolist_categorie_modif li').live('click', function(){
				var tag = $('#todolist_point_categorie_modif_' + id_point).val();
				var derniere_virgule = tag.lastIndexOf(',');
				tag = tag.substring(0, derniere_virgule + 1);
				$('#todolist_point_categorie_modif_' + id_point).val(tag + ' ' + $(this).attr('rel'));
			});
			
			$('#todolist_point_categorie_modif_' + id_point).blur(function(){
				setTimeout("$('#suggestions_todolist_categorie_modif').hide();", 200);	
			});
			
			$('#todolist_point_categorie_modif_' + id_point).keyup(function(){
				inputString = $(this).val();
			
				
				if(inputString.length == 0) 
				{
					// Hide the suggestion box.
					$('#suggestions_todolist_categorie').hide();
				} 
				else 
				{		
					var derniere_virgule = inputString.lastIndexOf(',');
					inputString = inputString.substring(derniere_virgule + 1, inputString.length)
				
					$.post(manager_url+'todolist/autoCompleteCategorie', {queryString: ""+inputString+""}, function(data){
						if(data.length >0) {
						$('#suggestions_todolist_categorie_modif').show();
						$('#autoSuggestionsList_todolist_categorie_modif').html(data);
						}
					});
				}		
			});
   		}
	});
});



$('.todolist_save_point').live('click', function(e){
	e.preventDefault();
	
	var id_point = $(this).parent().parent().parent().attr('id').substring(15, $(this).parent().parent().parent().attr('id').length);
	var id_user = $('#user_assoc_' + id_point + ' option:selected').val();
	var id_todolist = $('#todolist_id').val();
	
	nom_user = $('#user_assoc_' + id_point).val();
		
	if (nom_user != '') {
		$.ajax({
			type: "post",
			url: manager_url + "todolist/testerUser",
			data: "nom_user=" + nom_user + "&id_todolist=" + id_todolist,
			success: function(msg){
				if (msg == 'error') {
					alert('Cet utilisateur ne peut être associé à une tâche.');
				}
				else {
					$.ajax({
					   	type: "post",
				   		url: manager_url + "todolist/savePoint",
				   		data: "id=" + id_point + "&titre=" + $('#todolist_point_titre_modif_' + id_point).val() + "&description=" + $('#todolist_point_description_modif_' + id_point).val() + "&id_user=" + msg + "&categorie=" + $('#todolist_point_categorie_modif_' + id_point).val(),
				   		success: function(msg2){
							$('#todolist_point_bloc_gauche_' + id_point).html(msg2);
				   		}
					});
				}
			}
		});
	}
	else {
	
		$.ajax({
		   	type: "post",
	   		url: manager_url + "todolist/savePoint",
	   		data: "id=" + id_point + "&titre=" + $('#todolist_point_titre_modif_' + id_point).val() + "&description=" + $('#todolist_point_description_modif_' + id_point).val() + "&id_user=0&categorie=" + $('#todolist_point_categorie_modif_' + id_point).val(),
	   		success: function(msg){
				$('#todolist_point_bloc_gauche_' + id_point).html(msg);
	   		}
		});
	}
});



$('.todolist_cancel_point').live('click', function(e){
	e.preventDefault();
	
	var id_point = $(this).parent().parent().parent().attr('id').substring(15, $(this).parent().parent().parent().attr('id').length);
	
	$.ajax({
	   	type: "post",
   		url: manager_url + "todolist/afficherPoint",
   		data: "id=" + id_point,
   		success: function(msg){
			$('#todolist_point_bloc_gauche_' + id_point).html(msg);
   		}
	});
});



$('.todolist_delete_point').live('click', function(e){
	e.preventDefault();
	
	var id_point = $(this).parent().parent().parent().attr('id').substring(15, $(this).parent().parent().parent().attr('id').length);
	
	jConfirm('Etes vous sûr de vouloir supprimer cette tâche?\r\n\r\nUne fois la tâche supprimée, il ne sera plus possible de la récupérer ultérieurement.', 'Suppresion d\'une tâche', function(r) {
	    if(r) {
			$.ajax({
				type: "post",
				url: manager_url + "todolist/deletePoint",
				data: "id=" + id_point,
				success: function(){
				   	$('#todolist_point_' + id_point).remove();
				}
			});			
		}
	}); // fin jconfirm
});



$('#todolist_ajouter_admin').click(function(e){
	e.preventDefault();
	
	var nom_admin = $("#todolist_ajout_admin").val();
	
	if (nom_admin == '') {
		alert('Cet utilisateur ne peut être lié à cette liste ou l\'est déjà.');
	}
	else {
		if ($('#todolist_id').length > 0) {
			id_todolist = $('#todolist_id').val();
		}
		else {
			id_todolist = 'nouvelle_todolist';
		}
		
		$.ajax({
			type: "post",
			url: manager_url + "todolist/testerUser",
			data: "nom_user=" + nom_admin + "&type=61&id_todolist=" + id_todolist,
			success: function(msg){
				if (msg == 'error') {
					alert('Cet utilisateur ne peut être administrateur ou est déjà lié à cette liste.');
				}
				else {
					$('#todolist_liste_admins > hr').remove();
					$.ajax({
						type: "post",
						url: manager_url + "todolist/ajouterUser",
						data: "id=" + msg + "&id_todolist=" + id_todolist + "&type=ADMIN",
						success: function(msg2){
							$('#todolist_liste_admins').html($('#todolist_liste_admins').html() + '<p class="todolist_user"><span>' + nom_admin + '</span><a href="#" class="todolist_delete_admin simple_button small" id="todolist_delete_admin_' + msg + '"><img src="../images/delete.png" alt="Supprimer" />Supprimer</a></p><hr />');
							$('#todolist_point_user').append('<option value="' + msg + '">' + nom_admin + '</option>');
							$("#todolist_ajout_admin").val('');
							$('#todolist_id').val(msg2);
						}
					});
				}
			}
		});
	}
});



$('#todolist_ajouter_user').click(function(e){
	e.preventDefault();
	
	var nom_user = $("#todolist_ajout_user").val();
	
	if (nom_user == '') {
		alert('Cet utilisateur ne peut être lié à cette liste ou l\'est déjà.');
	} else {
		if ($('#todolist_id').length > 0) {
			id_todolist = $('#todolist_id').val();
		} else {
			id_todolist = 'nouvelle_todolist';
		}
		
		$.ajax({
			type: "post",
			url: manager_url + "todolist/testerUser",
			data: "nom_user=" + nom_user + "&type=62&id_todolist=" + id_todolist,
			success: function(msg){
				if (msg == 'error') {
					alert('Cet utilisateur ne peut être lié à cette liste ou l\'est déjà.');
				}
				else {
					$('#todolist_liste_users > hr').remove();
					$.ajax({
						type: "post",
						url: manager_url + "todolist/ajouterUser",
						data: "id=" + msg + "&id_todolist=" + id_todolist + "&type=CONSULT",
						success: function(msg2){
							$('#todolist_liste_users').html($('#todolist_liste_users').html() + '<p class="todolist_user"><span>' + nom_user + '</span><a href="#" class="todolist_delete_user simple_button small" id="todolist_delete_user_' + msg + '"><img src="../images/delete.png" alt="Supprimer" />Supprimer</a></p><hr />');
							$('#todolist_point_user').append('<option value="' + msg + '">' + nom_user + '</option>');
							$("#todolist_ajout_user").val('');
							$('#todolist_id').val(msg2);
						}
					});
				}
			}
		});
	}	
});


$('#todolist_file_delete').click(function(e){
	e.preventDefault();

	id_todolist = $('#todolist_id').val();
	file1 = $(this).parent().find('.todolist_icon_file_delete');
	file2 = $(this).parent().find('.todolist_icon_dl');
	
	jConfirm('Etes vous sûr de vouloir supprimer ce fichier?', 'Suppression du fichier', function(r) {
	    if(r) {
			$.ajax({
				type: "post",
				url: manager_url + "todolist/deleteFile",
				data: "id_todolist=" + id_todolist,
				success: function(){
					file1.remove();
					file2.remove();
				}		
			});
		}
	});
});


// Hover
$('.todolist_user').live('mouseover', function(){
	if ($(this).find('.todolist_delete_admin').length > 0) 
	{
		$(this).find('.todolist_delete_admin').css('visibility', 'visible');
	}
	else
	{
		$(this).find('.todolist_delete_user').css('visibility', 'visible');
	}
});

//Out
$('.todolist_user').live('mouseout', function(){
	if ($(this).find('.todolist_delete_admin').length > 0) 
	{
		$(this).find('.todolist_delete_admin').css('visibility', 'hidden');
	}
	else
	{
		$(this).find('.todolist_delete_user').css('visibility', 'hidden');
	}
});


$('.todolist_delete_admin').live('click', function(e){
	e.preventDefault();
	
	var id_admin = $(this).attr('id').substring(22, $(this).attr('id').length);
	var id_todolist = $('#todolist_id').val();
	var nom_admin = $(this).parent().find('span').text();
	
	$.ajax({
		type: "post",
		url: manager_url + "todolist/deleteUser",
		data: "id=" + id_admin + "&id_todolist=" + id_todolist,
		success: function(msg){
			$('#todolist_delete_admin_' + id_admin).parent().remove();
			$("#todolist_point_user > option[value=" + id_admin + "]").remove();
			
			if(msg == 'admin')
			{
				$('#todolist_ajout_admin').append('<option value="' + id_admin + '">' + nom_admin + '</option>');
			}
			else if(msg == 'user')
			{
				$('#todolist_ajout_user').append('<option value="' + id_admin + '">' + nom_admin + '</option>');
			}
			else
			{
				$('#todolist_ajout_admin').append('<option value="' + id_admin + '">' + nom_admin + '</option>');
				$('#todolist_ajout_user').append('<option value="' + id_admin + '">' + nom_admin + '</option>');
			}
		}		
	});
});


$('.todolist_delete_user').live('click', function(e){
	e.preventDefault();
	
	var id_user = $(this).attr('id').substring(21, $(this).attr('id').length);
	var id_todolist = $('#todolist_id').val();
	var nom_user = $(this).parent().find('span').text();
	
	$.ajax({
		type: "post",
		url: manager_url + "todolist/deleteUser",
		data: "id=" + id_user + "&id_todolist=" + id_todolist,
		success: function(msg){
			$('#todolist_delete_user_' + id_user).parent().remove();
			$("#todolist_point_user > option[value=" + id_user + "]").remove();
			
			if(msg == 'admin')
			{
				$('#todolist_ajout_admin').append('<option value="' + id_user + '">' + nom_user + '</option>');
			}
			else if(msg == 'user')
			{
				$('#todolist_ajout_user').append('<option value="' + id_user + '">' + nom_user + '</option>');
			}
			else
			{
				$('#todolist_ajout_admin').append('<option value="' + id_user + '">' + nom_user + '</option>');
				$('#todolist_ajout_user').append('<option value="' + id_user + '">' + nom_user + '</option>');
			}
		}		
	});
});



$('.todolist_changer_urgent').live('click', function(e){
	e.preventDefault();
	
	$(this).parent().html('<select name="todolist_select_urgent" class="todolist_select_urgent"><option value="Oui">Oui</option><option value="Non">Non</option></select>');
			
	$('.todolist_select_urgent option[value=' + $(this).text() + ']').attr('selected', 'selected');
	$('.todolist_select_urgent').focus();
	
});



$('.todolist_select_urgent').live('blur', function(){
	valeur = $(this).val();
						
	$(this).parent().html('<a href="#" class="todolist_changer_urgent underline">'+valeur+'</a>');
});



$('.todolist_select_urgent').live('change', function(){	
	valeur = $(this).val();
	
	var id_point = $(this).parent().parent().parent().parent().attr('id').substring(15, $(this).parent().parent().parent().parent().attr('id').length);
	
	$(this).parent().html('<a href="#" class="todolist_changer_urgent underline">'+valeur+'</a>');
	
	$.ajax({
		type: "post",
		url: manager_url + "todolist/changeUrgent",
		data: "id=" + id_point + "&urgent="+ valeur,
		success: function(msg){
			if (msg == 'Oui') 
			{
				$('#todolist_point_' + id_point).addClass('todolist_point_urgent');
				$('#todolist_point_' + id_point).removeClass('todolist_point');
			}
			else
			{
				$('#todolist_point_' + id_point).removeClass('todolist_point_urgent');
				$('#todolist_point_' + id_point).addClass('todolist_point');
			}
		}
	});	
});

// AUTO COMPLETE CAT

//désactivation de l'autocomplétion des navigateurs
$('#todolist_point_categorie').attr('autocomplete','off');

$('#autoSuggestionsList_todolist_categorie li').live('click', function(){
	var tag = $('#todolist_point_categorie').val();
	var derniere_virgule = tag.lastIndexOf(',');
	tag = tag.substring(0, derniere_virgule + 1);
	$('#todolist_point_categorie').val(tag + ' ' + $(this).attr('rel'));
});

$('#todolist_point_categorie').blur(function(){
	setTimeout("$('#suggestions_todolist_categorie').hide();", 200);	
});

$('#todolist_point_categorie').keyup(function(){
	inputString = $(this).val();

	if(inputString.length == 0) 
	{
		// Hide the suggestion box.
		$('#suggestions_todolist_categorie').hide();
	} 
	else 
	{		
		var derniere_virgule = inputString.lastIndexOf(',');
		inputString = inputString.substring(derniere_virgule + 1, inputString.length)
	
		$.post(manager_url+'todolist/autoCompleteCategorie', {queryString: ""+inputString+""}, function(data){
			if(data.length >0) {
				$('#suggestions_todolist_categorie').show();
				$('#autoSuggestionsList_todolist_categorie').html(data);
			}
		});
	}	
});

// AUTO COMPLETE USER

//désactivation de l'autocomplétion des navigateurs
$('#todolist_ajout_admin').attr('autocomplete','off');

$('#autoSuggestionsList_todolist_admin li').live('click', function(){
	$('#todolist_ajout_admin').val($(this).attr('rel'));
});

$('#todolist_ajout_admin').blur(function(){
	setTimeout("$('#suggestions_todolist_admin').hide();", 200);	
});

$('#todolist_ajout_admin').keyup(function(){
	inputString = $(this).val();

	if(inputString.length == 0) 
	{
		// Hide the suggestion box.
		$('#suggestions_todolist_admin').hide();
	} 
	else 
	{		
		if($('#todolist_id').length > 0) 
		{
			id_todolist = $('#todolist_id').val();
		} 
		else 
		{
			id_todolist = 'nouvelle_todolist';
		}
	
		$.post(manager_url+'todolist/autoCompleteAdmin', {queryString: ""+inputString+"", id_todolist: ""+id_todolist+""}, function(data){
			if(data.length >0) {
				$('#suggestions_todolist_admin').show();
				$('#autoSuggestionsList_todolist_admin').html(data);
			}
		});
	}	
});


//désactivation de l'autocomplétion des navigateurs
$('#todolist_ajout_user').attr('autocomplete','off');

$('#autoSuggestionsList_todolist_user li').live('click', function(){
	$('#todolist_ajout_user').val($(this).attr('rel'));
});

$('#todolist_ajout_user').blur(function(){
	setTimeout("$('#suggestions_todolist_user').hide();", 200);	
});

$('#todolist_ajout_user').keyup(function(){
	inputString = $(this).val();

	if(inputString.length == 0) 
	{
		// Hide the suggestion box.
		$('#suggestions_todolist_user').hide();
	} 
	else 
	{		
		if($('#todolist_id').length > 0) 
		{
			id_todolist = $('#todolist_id').val();
		} 
		else 
		{
			id_todolist = 'nouvelle_todolist';
		}
	
		$.post(manager_url+'todolist/autoCompleteUser', {queryString: ""+inputString+"", id_todolist: ""+id_todolist+""}, function(data){
			if(data.length >0) {
				$('#suggestions_todolist_user').show();
				$('#autoSuggestionsList_todolist_user').html(data);
			}
		});
	}	
});


//désactivation de l'autocomplétion des navigateurs
$('#todolist_point_user').attr('autocomplete','off');

$('#autoSuggestionsList_todolist_point_user li').live('click', function(){
	$('#todolist_point_user').val($(this).attr('rel'));
});

$('#todolist_point_user').blur(function(){
	setTimeout("$('#suggestions_todolist_point_user').hide();", 200);	
});

$('#todolist_point_user').keyup(function(){
	inputString = $(this).val();

	if(inputString.length == 0) 
	{
		// Hide the suggestion box.
		$('#suggestions_todolist_point_user').hide();
	} 
	else 
	{		
		if($('#todolist_id').length > 0) 
		{
			id_todolist = $('#todolist_id').val();
		} 
		else 
		{
			id_todolist = 'nouvelle_todolist';
		}
	
		$.post(manager_url+'todolist/autoCompleteUserPoint', {queryString: ""+inputString+"", id_todolist: ""+id_todolist+""}, function(data){
			if(data.length >0) {
				$('#suggestions_todolist_point_user').show();
				$('#autoSuggestionsList_todolist_point_user').html(data);
			}
		});
	}	
});


//désactivation de l'autocomplétion des navigateurs
$('.user_assoc').attr('autocomplete','off');

$('#autoSuggestionsList_todolist_point_user_edit li').live('click', function(){
	$('.user_assoc').val($(this).attr('rel'));
});

$('.user_assoc').live('blur', function(){
	setTimeout("$('#suggestions_todolist_point_user_edit').hide();", 200);	
});

$('.user_assoc').live('keyup', function(){
	inputString = $(this).val();

	if(inputString.length == 0) 
	{
		// Hide the suggestion box.
		$('#suggestions_todolist_point_user_edit').hide();
	} 
	else 
	{		
		if($('#todolist_id').length > 0) 
		{
			id_todolist = $('#todolist_id').val();
		} 
		else 
		{
			id_todolist = 'nouvelle_todolist';
		}
	
		$.post(manager_url+'todolist/autoCompleteUserPoint', {queryString: ""+inputString+"", id_todolist: ""+id_todolist+""}, function(data){
			if(data.length >0) {
				$('#suggestions_todolist_point_user_edit').show();
				$('#autoSuggestionsList_todolist_point_user_edit').html(data);
			}
		});
	}	
});


//désactivation de l'autocomplétion des navigateurs
$('#t_tag').attr('autocomplete','off');

$('#autoSuggestionsList_t_tag li').live('click', function(){
	$('#t_tag').val($(this).attr('rel'));
});

$('#t_tag').live('blur', function(){
	setTimeout("$('#suggestions_t_tag').hide();", 200);	
});

$('#t_tag').live('keyup', function(){
	inputString = $(this).val();

	if(inputString.length == 0) 
	{
		// Hide the suggestion box.
		$('#suggestions_t_tag').hide();
	} 
	else 
	{			
		$.post(manager_url+'todolist/autoCompleteCategorie', {queryString: ""+inputString+""}, function(data){
			if(data.length >0) {
				$('#suggestions_t_tag').show();
				$('#autoSuggestionsList_t_tag').html(data);
			}
		});
	}	
});


//désactivation de l'autocomplétion des navigateurs
$('#t_user').attr('autocomplete','off');

$('#autoSuggestionsList_t_user li').live('click', function(){
	$('#t_user').val($(this).attr('rel'));
});

$('#t_user').blur(function(){
	setTimeout("$('#suggestions_t_user').hide();", 200);	
});

$('#t_user').keyup(function(){
	inputString = $(this).val();

	if(inputString.length == 0) 
	{
		// Hide the suggestion box.
		$('#suggestions_t_user').hide();
	} 
	else 
	{			
		id_todolist = 'nouvelle_todolist';
	
		$.post(manager_url+'todolist/autoCompleteUser', {queryString: ""+inputString+"", id_todolist: ""+id_todolist+""}, function(data){
			if(data.length >0) {
				$('#suggestions_t_user').show();
				$('#autoSuggestionsList_t_user').html(data);
			}
		});
	}	
});


/*********************************************
************* MODES DE PAIEMENT **************
*********************************************/
$('.sup_mode_paiement').click(function(e){
	e.preventDefault();
	
	id = $(this).attr('rel');
	ligne = $(this).parents('tr');
	
	jConfirm('Etes vous sûr de vouloir supprimer ce mode de paiement?\r\n\r\nUne fois supprimé, il ne sera plus possible de le récupérer ultérieurement.', 'Suppresion d\'un type de congé', function(r){
		if (r) {
			$.ajax({
				type: "post",
				url: manager_url + "preferences/supModePaiement",
				data: "id=" + id,
				success: function(msg){
					$(location).attr('href', manager_url + 'preferences/modesPaiement');
					//ligne.remove();
				}
				
			});
		}
	});
});


/*********************************************
************* RELANCES AUTOMATIQUES **********
*********************************************/
$('#submit_relance').click(function(e){
	e.preventDefault();
	$('#form_relances').submit();
});




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
		$.ajax({
			type: "post",
			url: manager_url + "versions/confirmPaiement",
			data: "type=cheque&reference=" + ref + "&mail=" + mail,
			success: function(msg){
				$(location).attr('href', manager_url + 'versions/confirmCommande');
			}
			
		});
	});
}

if($('#paiement_virement').length != 0){
	var ref = $('#ref_bon').val();
	var mail = $('input[name=user_mail]').val();
	$('#paiement_virement').click(function(){
		$.ajax({
			type: "post",
			url: manager_url + "versions/confirmPaiement",
			data: "type=virement&reference=" + ref + "&mail=" + mail,
			success: function(msg){
				$(location).attr('href', manager_url + 'versions/confirmCommande');
			}
			
		});
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


function selectPaiement(type){
	$('#paiement_'+paiementActuel).hide();
	$('#paiement_'+type).show();
	$('#li_'+paiementActuel).removeClass('active');
	$('#li_'+type).addClass('active');
	paiementActuel = type;
	
	if(type != "cb"){
		$('.important').show();
	}else{
		$('.important').hide();
	}
}

function selectCard(type)
{
   document.getElementById('PBX_TYPECARTE').value = type;
   document.getElementById('PBX_FORM').submit();
}
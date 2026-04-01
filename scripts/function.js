/* ##################################### */
/*  Site réalisé par Cedric Denoel       */
/*  URL : http://www.uppc.be		     */
/*  Info : http://www.ccl.be		     */
/* ##################################### */

/* ########################################################################################################## */
/* ################################       Manipuler evenements       ######################################## */
/* ########################################################################################################## */

// Pour class "_blank" équivalent à target = "_blank"

// Fonction d'abstraction pour enregistrer un gestionnaire d'evenement comprend le DOM standard, la syntaxe prorietaire MSIE, l'ancien modele HTML 
// source : objet sur lequel ajouter le gestionnaire d'evenement 
// type : type d'evenement 
// callback : fonction qui traitera l'evenement
function addEvent(source, type, callback) {

	if (source.addEventListener) {							// code standard DOM
		source.addEventListener(type, callback, false);
		return true;
	}
	else if (source.attachEvent) {							// code propriétaire MSIE
		var r = source.attachEvent("on"+type, callback);
		return r;
	} 
	else {													// code navigateur sans support DOM-event
		eval('source.on' + type + '= callback');
	}
	
}

// Abstraction pour recuperer un objet standard pour l'evenement en cours comprend le modele DOM standard et le modele proprietaire de MSIE 
// e : parametre recu lors de l'appel du gestionnaire d'evenement 
// retour : objet d'evenement standard
function getStandardEvent(e) {
	
	if (e == null && window.event) {						// cas particulier de MSIE pour recuperer l'evenement en cours
	e = window.event;
	}
	
	if (e.target == null && e.srcElement) {					// cas particulier de MSIE pour recuperer la balise DOM cible
	e.target = e.srcElement;
	}
	
	if (! e.preventDefault ) { 								// cas particulier de MSIE pour empecher l'action par defaut du navigateur
	e.preventDefault = function () { this.returnValue = false; };
	}

	return e;
}

// Gestionnaire d'evenement actif lors d'un clic sur les liens 
// ouvre le lien dans une popup et pas dans une page normale e : evenement de clic
function openLinkInPopupWhenClick(e) {
	e = getStandardEvent(e);
	var link =  e.target;
	var addr = link.getAttribute('href');

	// si aucune adress en'est obtenue, on regarde si l'objet parent à un lien afin d'obtenir son adresse à la place.
	if(addr == null && link.parentNode.nodeName == 'A')
	link = link.parentNode;
	var addr = link.getAttribute('href') ;

	// avec cette solution on peut soit :
	// - préciser class="_blank_300_200" afin d'ouvrir une popup de 300px sur 200px
	// - préciser class="_blank" pour ouvrir dans un nouvel onglet ou nouvelle fenêtre

	var reg = /(_blank|)_(d+)/;
	
	if(reg.test(link.className))							// dimensions trouvées dans le nom de la classe
		{
		reg = /(_blank|)_(d+)/;
		var result = link.className.split(reg);
		window.open(addr, '', 'width='+result[2]+',height='+ result[5]);
		}
	else													// sinon popup standard
		{
		window.open(addr);
		}

	e.preventDefault();
	return false;
}

// Explore le document pour rechercher les liens d'aide à chaque lien, on verifie s'il a "_blank" dans la liste de ses classes 
// si oui, on enregistre un gestionnaire d'evenement pour le clic de ce lien
function prepareTargetBlankLinks() {
	var link, list, i;
	list = document.getElementsByTagName('a');
	
	for(i=0; i<list.length; i++) {
		link = list.item(i);
		if (link.getAttribute('href') && link.className) {
			if (link.className.indexOf('_blank') != -1) { addEvent(link, 'click', openLinkInPopupWhenClick); }
		}
	}
}

addEvent(window, 'load', prepareTargetBlankLinks);

/* ########################################################################################################## */
/* ################################            Textarea              ######################################## */
/* ########################################################################################################## */

// Allonge la taille du textarea au fur et à mesure que l'on tape du texte

function textareaSize(zoneTexte) {
	if (zoneTexte) {
		
		nbrLignes 			= 2;
		longueurDeLigne 	= 2; 	// Taille minimal de la zone de texte.
		nbrLignesMax 		= 18;
		longueurDeLigneMax 	= 9; 	// Taille maximale de la zone de texte.
		
		lesLignes = escape(zoneTexte.value).split("%0D%0A");
		
		if (lesLignes) { nbrLignes = lesLignes.length; }
		
		if (nbrLignes>document.body.clientHeight / nbrLignesMax) { nbrLignes = document.body.clientHeight / nbrLignesMax; }
		
		if (lesLignes) {
			for(n=0; n<(lesLignes.length); n++) {
				if (longueurDeLigne<unescape(lesLignes[n]).length) { longueurDeLigne = unescape(lesLignes[n]).length; }
				
				if (longueurDeLigne > document.body.clientWidth / longueurDeLigneMax)
					{
					longueurDeLigne = document.body.clientWidth / longueurDeLigneMax;
					nbrLignes+=unescape(lesLignes[n]).length / (document.body.clientWidth / longueurDeLigneMax);
					}
			}
		}
	
	else { longueurDeLigne = zoneTexte.value.length }
	
	if (nbrLignes > document.body.clientHeight / nbrLignesMax) {nbrLignes = document.body.clientHeight / nbrLignesMax;}
	zoneTexte.cols = (longueurDeLigne + 1); // Charge le nombre de colonnes utile, plus une colonne pour la clarté
	zoneTexte.rows = (nbrLignes + 1); 		// Charge le nombre de lignes utile, plus une ligne pour la clarté
	}
}

function textareaSizeLimites(zoneTexte,colMin,colMax,rowMin,rowMax) {
	if (zoneTexte) {
		
		nbrLignesMin 		= rowMin;
		longueurDeLigneMin 	= colMin; // Taille minimal de la zone de texte.
		nbrLignesMax 		= rowMax;
		longueurDeLigneMax 	= colMax; // Taille maximale de la zone de texte.
		
		nbrLignes = nbrLignesMin;
		longueurDeLigne = longueurDeLigneMin;
		lesLignes = escape(zoneTexte.value).split("%0D%0A");
		
		if (lesLignes) { nbrLignes = lesLignes.length; }
		if (nbrLignes > nbrLignesMax) { nbrLignes = nbrLignesMax; }
		else if (nbrLignes < nbrLignesMin) { nbrLignes = nbrLignesMin; }
		
		if (lesLignes) {
			for(n=0; n<(lesLignes.length); n++) {
				if (longueurDeLigneMin < unescape(lesLignes[n]).length) { longueurDeLigne = unescape(lesLignes[n]).length; }
				
				if (longueurDeLigne > longueurDeLigneMax)
					{
					longueurDeLigne = longueurDeLigneMax;
					nbrLignes+=unescape(lesLignes[n]).length / longueurDeLigneMax;
					}
			}
		}
		else {longueurDeLigne = zoneTexte.value.length}
		
		if (nbrLignes > nbrLignesMax) { nbrLignes = nbrLignesMax; }
		else if (nbrLignes < nbrLignesMin) { nbrLignes = nbrLignesMin; }
		
		zoneTexte.cols = (longueurDeLigne + 1); // Charge le nombre de colonnes utile, plus une colonne pour la clarté
		zoneTexte.rows = (nbrLignes + 1); 		// Charge le nombre de lignes utile, plus une ligne pour la clarté
	}
}

/* ########################################################################################################## */
/* ################################        Afficher / Masquer        ######################################## */
/* ########################################################################################################## */

// Afficher ou masquer un champ suivant la selection d'une option d'une liste deroulante (utiliser avec OnChange)

function optionDisplay(idchamps, idblockdisplay, idchampsfocus) {
	
	var liste_element	= document.getElementById(idchamps);
	var id_element 		= liste_element.options[liste_element.selectedIndex].index;

	if (id_element == 1) {
		document.getElementById(idblockdisplay).style.display = 'table-row';
		document.getElementById(idchampsfocus).focus();
	}
	else {
		document.getElementById(idblockdisplay).style.display = 'none';
	}
	
}

/* ########################################################################################################## */

// Afficher ou masquer un block <div> (utiliser avec OnClick ou autre)
// Accepte un paramètre optionnel true ou false pour forcer ou non l'affichage, sinon automatique (vérifie l'attribut style.display)

function blockDisplay(thingId, displayValue) { // ID du block DIV ou P a afficher/masquer
	
	var targetElement = document.getElementById(thingId);
	
	if (displayValue == false) { targetElement.style.display = "none" ; }
	else if (displayValue == true) { targetElement.style.display = "block" ; }
	else if (targetElement.style.display == "none" || targetElement.style.display == "") { targetElement.style.display = "block" ; }
	else { targetElement.style.display = "none" ; }
}

/* ########################################################################################################## */

// Afficher ou masquer une ligne <tr> d'un tableau (utiliser avec OnClick)
// Remarque : Ce script permet d'éviter un bug avec le colspan des balises <td> sous-jacentes que provoque la fonction pour les blocks <div>
// Accepte un paramètre optionnel true ou false pour forcer ou non l'affichage, sinon automatique (vérifie l'attribut style.display)

function rowDisplay(thingId, displayValue) { // ID de la ligne <TR> du tableau a afficher/masquer
		
	var targetElement = document.getElementById(thingId);
	
	if (displayValue == false) { targetElement.style.display = "none" ; }
	else if (displayValue == true) { targetElement.style.display = "table-row" ; }
	else if (targetElement.style.display == "none" || targetElement.style.display == "") { targetElement.style.display = "table-row"; }
	else { targetElement.style.display = "none" ; }
}

/* ########################################################################################################## */

// Remet l'option selectionnee par defaut d'une liste deroulante, et masque une zone (utiliser avec OnChange)
// Remarque : on masque le champs supplementaire manuellement car le script ne provoque pas un evenement "onChange"

function optionReinit(idSelect, idHidden) { // ID de la liste déroulante, ID de la zone a masquer
	
	document.getElementById(idSelect).options[0].selected 	= true;
	document.getElementById(idHidden).style.display 		= "none";
	
}

/* ########################################################################################################## */
/* ################################        Copie de valeurs          ######################################## */
/* ########################################################################################################## */

// Copie la valeur "optgroupt" (groupe d'option) d'une liste deroulante dans un champs "input"

function optgroupCopy(selectId, inputID) { // ID de la liste deroulante, ID du champs vers ou copier
	
	var from 	= document.getElementById(selectID);
	var to 		= document.getElementById(inputID);
	
	to.value = from.options[from.selectedIndex].parentNode.id ;
}

/* ########################################################################################################## */

// Copie les elements selectionnes d'une liste deroulante multiple vers un autre champs (utilise avec onChange)

function selectCopy(selectID, inputID) { // ID de la liste deroulante, ID du champs vers ou copier
	
	var liste_destinataires = document.getElementById(selectID);

	liste_destinataires_selected = new Array();
	for (var i = 0; i < liste_destinataires.options.length; i++) 
		{
		if (liste_destinataires.options[i].selected) 
			{ liste_destinataires_selected.push(liste_destinataires.options[i].value); }
		}
			
	// Insertion dans les champs input text
	document.getElementById(inputID).value = liste_destinataires_selected.join(", "); 
}

/* ########################################################################################################## */
/* ################################              Utils               ######################################## */
/* ########################################################################################################## */

// Vérifie e-mail
function isMail(email) {
	var reg = new RegExp('^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$', 'i');
	return(reg.test(email));
}

// Vérifie numéro de téléphone
// 042333756 ou +32 (0) 4 233 37 56 --> entre 9 et 19
function isTel(tel) {
	var reg = new RegExp('^[0-9 ()-+\./]{9,19}$', 'gi'); // chiffre 0 à 9 et les caractères ()-+. et espace
	return reg.test(tel);
}

// Vérifie numéro de GSM
// 0497937340 ou +32 (0) 497 93 73 40 --> entre 10 et 20
function isMobile(tel) {
	var reg = new RegExp('^[0-9 ()-+\./]{10,20}$', 'gi');
	return reg.test(tel);
}

function isMP(mp) {
	if (mp.length < 4) { return false; }
	else { return true; }
}

// Vérifie login
// Min 2 caractères, et commence par une lettre (uniquement lettres et chiffres et les caractères ._-
function isLogin(login) {
	var reg = new RegExp('^[A-Zéèêçàâî]{1}[A-Zéèêçàâî0-9._@-]{1,31}$', 'i');
	return reg.test(login);
}

/* ########################################################################################################## */
//Pour manipuler les classes

// Vérifie si la classe existe
function hasClass(elementID, hasClass) {
	var element = document.getElementById(elementID);
	return element.className.match(new RegExp('(\\s|^)'+hasClass+'(\\s|$)'));
}

// Ajoute une classe
function addClass(elementID,addClass) {
	var element = document.getElementById(elementID);
	if (!this.hasClass(elementID, addClass)) element.className += " "+addClass;
}

// Enlève la classe si elle existe
function removeClass(elementID, removeClass) {
	var element = document.getElementById(elementID);

	if (hasClass(elementID, removeClass)) {
		var reg = new RegExp('(\\s|^)'+removeClass+'(\\s|$)');
		element.className = element.className.replace(reg, ' ');
	}
}

// Idem mais par TagName
function hasClassByTagName(elementName, i, hasClass) {
	var element = document.getElementsByTagName('tr')[i];
	return element.className.match(new RegExp('(\\s|^)'+hasClass+'(\\s|$)'));
}

function addClassByTagName(elementName,addClass) {
	var name = document.getElementsByTagName('tr');
	for (var i=0; i<name.length; i++) {
		if (!this.hasClassByTagName(elementName, i, addClass)) name[i].className += " "+addClass;
	} 
}

function removeClassByTagName(elementName,removeClass) {
	var name = document.getElementsByTagName('tr');
	var reg = new RegExp('(\\s|^)'+removeClass+'(\\s|$)');
	for (var i=0; i<name.length; i++) { 
		name[i].className = name[i].className.replace(reg, ' ');
		name[i].className = name[i].className.replace(reg, ' ');
	} 
}

/* ########################################################################################################## */

function SetAllCheckBoxes(FormName, FieldName, CheckValue)
{
	if(!document.forms[FormName])
		return;
	var objCheckBoxes = document.forms[FormName].elements[FieldName];
	if(!objCheckBoxes)
		return;
	var countCheckBoxes = objCheckBoxes.length;
	if(!countCheckBoxes)
		objCheckBoxes.checked = CheckValue;
	else
		// set the check value for all check boxes
		for(var i = 0; i < countCheckBoxes; i++)
			objCheckBoxes[i].checked = CheckValue;
}
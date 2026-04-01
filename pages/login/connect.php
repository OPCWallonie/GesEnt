<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="fr" xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>Connexion</title>
<meta name="keywords" content="" lang="fr">
<meta name="description" content="">

<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js">
</script>

	
<style type="text/css">
/* ::::: http://www.wuro.fr/static/css/shadowbox.css ::::: */

#sb-title-inner, #sb-info-inner, #sb-loading-inner, div.sb-message { font-family: "HelveticaNeue-Light","Helvetica Neue",Helvetica,Arial,sans-serif; font-weight: 200; color: rgb(255, 255, 255); }
#sb-container { position: fixed; margin: 0px; padding: 0px; top: 0px; left: 0px; z-index: 999; text-align: left; visibility: hidden; display: none; }
#sb-overlay { position: relative; height: 100%; width: 100%; }
#sb-wrapper { position: absolute; visibility: hidden; width: 100px; }
#sb-wrapper-inner { position: relative; overflow: hidden; height: 100px; }
#sb-body { position: relative; height: 100%; }
#sb-body-inner { position: absolute; height: 100%; width: 100%; }
#sb-loading { position: relative; height: 100%; }
#sb-loading-inner { position: absolute; font-size: 14px; line-height: 24px; height: 24px; top: 50%; margin-top: -12px; width: 100%; text-align: center; }
#sb-loading-inner span { background: url('ajax.gif') no-repeat scroll 0% 0% transparent; padding-left: 34px; display: inline-block; }
#sb-body, #sb-loading { background-color: rgb(255, 255, 255); }
#sb-title, #sb-info { position: relative; margin: 0px; padding: 0px; overflow: hidden; }
#sb-title, #sb-title-inner { height: 26px; line-height: 26px; display: none; }
#sb-title-inner { font-size: 16px; }
#sb-info { width: 100%; height: 20px; line-height: 20px; position: absolute; top: 5px; }
#sb-info-inner { height: 20px; line-height: 20px; }
#sb-info-inner { font-size: 12px; }
#sb-nav { float: right; margin-right: 5px; height: 16px; padding: 2px 0px; width: 45%; }
#sb-nav a { display: block; float: right; height: 16px; width: 16px; margin-left: 3px; cursor: pointer; background-repeat: no-repeat; }
#sb-nav-close { background-image: url('cross.png'); }
#sb-nav-next { background-image: url('next.png'); }
#sb-nav-previous { background-image: url('previous.png'); }
#sb-nav-play { background-image: url('play.png'); }
#sb-nav-pause { background-image: url('pause.png'); }
#sb-counter { float: left; width: 45%; }
/* ::::: http://www.wuro.fr/static/css/jquery-ui-1.7.2.custom.css ::::: */

.ui-helper-hidden-accessible { position: absolute; left: -1e+8px; }
.ui-helper-clearfix:after { content: "."; display: block; height: 0px; clear: both; visibility: hidden; }
.ui-helper-clearfix { display: inline-block; }
.ui-helper-clearfix { display: block; }
.ui-widget { font-family: Verdana,Arial,sans-serif; font-size: 1.1em; }
.ui-widget-content { border: 1px solid rgb(170, 170, 170); background: url('ui-bg_flat_75_ffffff_40x100.png') repeat-x scroll 50% 50% rgb(255, 255, 255); color: rgb(34, 34, 34); }
.ui-widget :active { outline: medium none; }
.ui-corner-all { border-radius: 4px 4px 4px 4px; }
button.ui-button::-moz-focus-inner { border: 0px none; padding: 0px; }
.ui-datepicker { width: 17em; padding: 0.2em 0.2em 0px; z-index: 2; display: none; }
/* ::::: http://www.wuro.fr/static/css/css_xav.css ::::: */

/* ::::: http://www.wuro.fr/static/css/skin.css ::::: */

body { background-color: rgb(226, 226, 226); }
/* ::::: http://www.wuro.fr/static/css/autoComplete.css ::::: */

/* ::::: http://www.wuro.fr/static/js/jquery.alerts-1.1/jquery.alerts.css ::::: */

/* ::::: http://www.wuro.fr/static/js/jquery-tooltip/jquery.tooltip.css ::::: */

/* ::::: http://www.wuro.fr/static/css/connexion.css ::::: */

* { margin: 0px; padding: 0px; }
body { color: rgb(0, 0, 0); font: 0.8em/1.3em "Trebuchet MS",Verdana,"Lucida Grande",Tahoma,Helvetica,Sans-Serif; }
a, a:visited { color: rgb(0, 0, 0); outline: medium none; text-decoration: none; }
a:hover { text-decoration: underline; }
h1 { display: none; }
p { margin-bottom: 1.3em; }
div#logo { background: none repeat scroll 0% 0% rgb(255, 255, 255); border: 1px solid rgb(230, 230, 230); margin: 20px auto 1px; width: 498px; text-align: center; }
form#connexion { background: url('../images/home-top.png') no-repeat scroll 0px 100% rgb(218, 218, 218); margin: 0px auto; width: 476px; padding: 10px; border: 1px solid rgb(255, 255, 255); }
form#connexion label { font-size: 1.6em; float: left; width: 145px; margin-right: 5px; margin-bottom: 10px; color: rgb(60, 60, 60); text-shadow: 1px 1px 0px rgb(255, 255, 255); line-height: 40px; }
form#connexion input.textfield { width: 300px; font-size: 1.3em; color: rgb(60, 60, 60); background: none repeat scroll 0% 0% rgb(255, 255, 255); border: 1px solid rgb(191, 191, 191); padding: 7px; vertical-align: middle; font-weight: bold; }
form#connexion input.textfield:focus { border: 1px solid rgb(60, 60, 60); }
form#connexion input.submit { background: none repeat scroll 0% 0% rgb(60, 60, 60); color: rgb(255, 255, 255); width: 220px; margin-left: 195px; padding: 7px; border: 0px none; border-radius: 5px 5px 5px 5px; font-weight: bold; }
form#connexion input.submit:hover { cursor: pointer; background-color: rgb(51, 51, 51); }
form#connexion input.submit:focus { cursor: pointer; background-color: rgb(255, 255, 255); color: rgb(51, 51, 51); }
form#connexion input[type="checkbox"] { vertical-align: middle; }
form#connexion a { font-size: 0.8em; font-style: italic; }
form#connexion label.remember { float: none; font-size: 0.8em; text-shadow: none; color: rgb(0, 0, 0); width: auto; line-height: 1; margin-bottom: 0px; }
div.message { margin-bottom: 10px; padding: 5px; }
div.message.info { background: none repeat scroll 0% 0% rgb(218, 235, 243); border: 1px solid rgb(168, 216, 235); }
div.message.info span { display: block; background: url('../images/exclamation.png') no-repeat scroll 0px 0px transparent; padding-left: 20px; }

#sb-nav-close { background-image: url('../images/cross.png'); }

</style>






</head>
<body>


		<div align="center">
        <img src="../images/logo_ccl.png" />
	</div>
  <h1>Connexion au site</h1>

  	<form action="../include/connexion.inc.php" method="post" id="connexion" name="connexion">
  		<input name="entreprise" value="" type="hidden" />
  		  		
  		  		
	  	<p>
	  		<label for="login">Login :</label>
	  		<input value="" name="login" id="login" class="textfield " type="text" />
	  	</p>
	  	<p>
	  		<label for="pass">Mot de passe :</label>
	  		<input id="password" name="password" class="textfield " type="password" />
	  	</p>	
	  	<p>
	  		<input class="submit" name="Connexion" value="Connexion" type="submit">
	  		<input name="redirection" value="null" type="hidden">
		</p>
	  	<span>
	  		<input name="souvenir" id="souvenir" type="checkbox">
	  		<label for="souvenir" class="remember">Se souvenir de moi</label>&nbsp;|&nbsp;
     		<a href="../login/redir.html" rel="shadowbox;width=500;height=220" id="mdp-oublie">Mot de passe ?</a>
	  	</span>
  	</form>



</body>
</html>

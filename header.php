<?php mb_internal_encoding('UTF-8'); ?>
<?php 
	if(!isset($_SESSION)) session_start();
?>
<link rel = "stylesheet"
	type = "text/css"
	href = "./css/headermenu.css"/>
<div class="header-menu">
	<ul>
		<li class="left"><img src="./imgs/logo.png"></li>
		<li class="left"><a href="./index.php">Accueil</a></li>
		<li class="left"><a href="./products.php">Produits</a></li>
		<li class="left"><a href="./contacts.php">Contacts</a></li>
		<?php
			if(isset($_SESSION["login"])){
				echo "<li class=\"right\"><a href=\"./account.php\">Mon Compte</a></li>";
				echo "<li class=\"right\"><a href=\"./account.php?logout=1\">Me déconnecter</a></li>";
			}else{
				echo "<li class=\"right\"><a href=\"./account.php?login=1\">Se connecter</a></li>";
				echo "<li class=\"right\"><a href=\"./account.php?register=1\">S'enregistrer</a></li>";
			}
		?>
	</ul>
</div>

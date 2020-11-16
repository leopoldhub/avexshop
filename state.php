<?php mb_internal_encoding('UTF-8'); ?>
<?php

function getStateString($int, $row){
	$msg = "";
	switch ($int) {
		case 0:
			//vérification
			$msg = "<p>Vérification du produit...</p>";
			break;
		case 1:
			//préparation
			$msg = "<p>Préparation a l'envoi...</p>";
			break;
		case 2:
			//envoi
			$msg = "<p>Produit envoyé... suivre: </p><a href=\"".$row['suivis']."\"> ici </a>";
			break;
		case 3:
			//rendez-vous
			$msg = "<p>Prise de rendez-vous (vérifiez vos mails/sms)</p>";
			break;
		case 4:
			//remise main propre
			$msg = "<p>attente de remise en main propre : ".$row['dte']."  ".$row['lieu']."</p>";
			break;
		case 5:
			//remis
			$msg = "<p>Produit remis!</p>";
			break;
		case 6:
			//remis
			$msg = "<p>Annulé</p>";
			break;
	}
	return $msg;
}

?>
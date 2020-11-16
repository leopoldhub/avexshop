<?php mb_internal_encoding('UTF-8'); ?>
<?php

function getLivraisonString($int){
	$msg = "";
	switch ($int) {
		case 0:
			//vérification
			$msg = "remise en main propre";
			break;
		case 1:
			//préparation
			$msg = "Poste";
			break;
	}
	return $msg;
}

function getNextState($type, $state){
	switch ($type) {
		case 1:
			switch ($state) {
				case 0:
					return 1;
					break;
				case 1:
					return 2;
					break;
				case 2:
					return 5;
					break;
			}
			break;
		case 0:
			switch ($state) {
				case 0:
					return 3;
					break;
				case 3:
					return 4;
					break;
				case 4:
					return 5;
					break;
			}
			break;
	}
}

?>
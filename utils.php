<?php mb_internal_encoding('UTF-8'); ?>
<?php
	if (!function_exists('array_key_first')) {
	    function array_key_first(array $arr) {
	        foreach($arr as $key => $unused) {
	            return $key;
	        }
	        return NULL;
	    }
	}

	function getFirstValue($array){
		if(is_null($array))return NULL;
		foreach ($array as $cle => $valeur) {
			return $valeur;
		}
	}

?>
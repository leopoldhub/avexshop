<?php mb_internal_encoding('UTF-8'); ?>
<?php
	function connectDataBase(){
		$base = new mysqli("127.0.0.1", "user", "password", "base");
		return $base;
	}

?>
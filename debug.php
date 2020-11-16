clients screen resolution: <script type='text/javascript'>document.write(screen.width+'x'+screen.height); </script><br>
<?php
	foreach($_SERVER as $key => $value){
		echo '$_SERVER["'.$key.'"] = '.$value."<br />";
	}
?>
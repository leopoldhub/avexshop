<?php mb_internal_encoding('UTF-8'); ?>
<?php if(!isset($_SESSION)) session_start(); ?>
<?php require "header.php"; ?>
<!DOCTYPE html>
<html>
	<head>
		<title>Contacts</title>
		<link rel = "stylesheet" type = "text/css" href = "./css/contacts.css"/>
	</head>
	<body>
		<div class="node">
			<img src="./imgs/logo.png">
			<ul>
				<li><p><b>Mail: </b>xxxxxxxx@xxxx.xx</p></li>
				<li><p><b>Téléphone: </b>0600000000</p></li><br>
				<li><p><b>Tweeter: </b><a href="">@xxxxxxx</a></p></li>
				<li><p><b>Instagram: </b><a href="">@xxxxxxx</a></p></li>
			</ul>
			
			
		</div>
		<?php require "footer.php"; ?>
	</body>
</html>
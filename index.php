<?php mb_internal_encoding('UTF-8'); ?>
<?php if(!isset($_SESSION)) session_start(); ?>
<?php require "utils.php"; ?>
<?php require "database.php"; ?>
<!DOCTYPE html>
<html>
	<head>
		<title></title>
	</head>
	<body>
		<?php require "header.php"; ?>

		<link rel = "stylesheet"
			type = "text/css"
			href = "./css/table.css"/>

		<table class="contenttable">
			<thead>
				<tr>
		            <th colspan="2">
		            	<img src="./imgs/logo.png">
		            	<p>site description... with many text......... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... ..... </p>
		            </th>
		        </tr>
			</thead>
			<tbody>
				<tr>
					<link rel = "stylesheet"
						type = "text/css"
						href = "./css/leftmenu.css"/>
		            <td class="lmenu">
		            	<div class="leftmenu">
		            		<h1>Liens utiles</h1>
		            		<ul>
		            			<li><a href="">Produits</a></li>
		            			<li><a href="">CGV</a></li>
		            			<li><a href="">CGU</a></li>
			            		<li><a href="./contacts.php">Contacts</a></li>
		            		</ul>
		            	</div>
		            </td>
		            <td class="content">
		            	<link rel = "stylesheet"
						type = "text/css"
						href = "./css/news.css"/>
						<?php
							$base = connectDataBase();
							if(!$base){
								$error = "connection impossible a la base de données, désolé du dérangement...";
							}
							if(empty($error)){
								$news = $base->query("SELECT * FROM news ORDER BY id DESC;");
								if(empty($error) && empty($news)){
									$error = "impossible de charger les news.";
								}
								if(empty($error)){
									while($row = mysqli_fetch_assoc($news)){
						?>
						            	<div class="news">
						            		<table>
												<thead>
													<tr>
											            <th colspan="2">
											            	<h1><?php echo $row['titre'] ; ?></h1>
											            	<p class="date"><?php echo $row['dte'] ; ?></p>
											            </th>
											        </tr>
												</thead>
												<tbody>
													<tr>
						            					<td class="desc">
						            						<?php echo $row['description'] ; ?>
						            					</td>
						            					<td class="img"><img src="./imgs/news/<?php echo $row['id']; ?>.png"></td>
						            				</tr>
												</tbody>
											</table>
						            	</div>
		            	<?php
		            				}
		            			}
		            		}
		            		$base->close();
		            		if(!empty($error)){
		            			echo $error;
		            		}
		            	?>
		        	</td>
		        </tr>
			</tbody>
		</table>
		<?php require "footer.php"; ?>
	</body>
</html>
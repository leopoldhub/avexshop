<?php mb_internal_encoding('UTF-8'); ?>
<?php 
	if(!isset($_SESSION)) session_start();
	//$_SESSION["login"] = "a";
	require "clearcache.php";
?>
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
			<tbody>
				<tr>
					<link rel = "stylesheet"
						type = "text/css"
						href = "./css/filtermenu.css"/>
		            <td class="lmenu">
		            	<div class="filtermenu">
		            		<h1>Filtrer</h1>
		            		<p>Choisissez des catégories pour filtrer vos résultats ou utilisez des mots clés</p>
		            		<form action="" method="get">
		            			<?php require "database.php"; ?>
								<?php require "utils.php"; ?>
		            			<?php

		            				error_reporting(E_ALL);
									ini_set('display_errors', true);

		            				$parameters = array();

		            				foreach ($_GET as $cle => $valeur) {
										$parameters[htmlspecialchars($cle)] = htmlspecialchars($valeur);
									}

									$keywords = null;

									$keyval = "";

									if(isset($parameters['keywords']) && !empty($parameters['keywords'])){
										$keywords = explode(" ", $parameters['keywords']);
										$keyval = $parameters['keywords'];
									}

									echo "<label for=\"keywords\">Mots clés:</label><br>";
    								echo "<input type=\"text\" id=\"keywords\" name=\"keywords\" placeholder=\"mot1 mot2 mot3\" value=\"".$keyval."\"><br><br>";

								?>

								<label for="order">Trier par:</label><br>
								<select name="order" id="order" selected="selected">
									<?php
										$selected = "new";

										if(isset($_GET['order'])){
											$selected = $_GET['order'];
										}
										if($selected == "new"){
											echo "<option value=\"new\" selected>Nouveauté</option>";
										}else{
											echo "<option value=\"new\">Nouveauté</option>";
										}
										if($selected == "old"){
											echo "<option value=\"old\" selected>Ancien</option>";
										}else{
											echo "<option value=\"old\">Ancien</option>";
										}
										if($selected == "prp"){
											echo "<option value=\"prp\" selected>Prix + => -</option>";
										}else{
											echo "<option value=\"prp\">Prix + => -</option>";
										}
										if($selected == "prm"){
											echo "<option value=\"prm\" selected>Prix - => +</option>";
										}else{
											echo "<option value=\"prm\">Prix - => +</option>";
										}
									?>
								    
								</select><br><br>

		            			<?php

		            				$base = connectDataBase();

		            				$categs = $base->query("SELECT nom FROM categories;");
		            				if($categs){
										while($row = mysqli_fetch_assoc($categs)){
											$name = $row['nom'];
											if(!strpos($name, ':')){
												$scategs = $base->query("SELECT nom FROM categories WHERE nom LIKE '".$name.":%';");
												$checked = "";
												if(array_key_exists($name, $parameters)){
													$checked = "checked";
												}
												echo "<input type=\"checkbox\" id=\"".$name."\" name=\"".$name."\" value=\"1\" ".$checked.">";
												echo "<label for=\"".$name."\">".$name."</label><br>";
												if($scategs){
													echo "<ul>";
													while($srow = mysqli_fetch_assoc($scategs)){
														$sname = $srow['nom'];
														$sfname = explode(":",$srow['nom'])[1];
														$checked = "";
														if(array_key_exists($sname, $parameters)){
															$checked = "checked";
														}
														echo "<li>";
														echo "<input type=\"checkbox\" id=\"".$sname."\" name=\"".$sname."\" value=\"1\" ".$checked.">";
														echo "<label for=\"".$sname."\">".$sfname."</label><br>";
														echo "</li>";
													}
													echo "</ul>";
												}
											}
									    }
									}
		            			?>
								<input type="submit" value="Filtrer">
							</form>
		            	</div>
		            </td>
		            <td class="content">
		            	<link rel = "stylesheet"
							type = "text/css"
							href = "./css/products.css"/>
		            	<div class="products">
		            		<ul>
		            			<?php
		            				$order = "id DESC";
		            				if(isset($_GET['order']) && $_GET['order'] == "old"){
		            					$order = "id ASC";
		            				}else if(isset($_GET['order']) && $_GET['order'] == "prp"){
		            					$order = "prix DESC";
		            				}else if(isset($_GET['order']) && $_GET['order'] == "prm"){
		            					$order = "prix ASC";
		            				}
		            				$products = $base->query("SELECT * FROM products ORDER BY ".$order.";");
									if($products){
										while($row = mysqli_fetch_assoc($products)){

											$show = false;

											if(count($parameters) <= 2){
												$show = true;
											}

											$pcategs = explode(";",$row['categories']);

											foreach ($pcategs as $cle) {
												foreach ($parameters as $key => $value) {
													$splcle = explode(":", $cle);
													if(strcasecmp($cle, htmlentities($key)) == 0 || strcasecmp($splcle[0], htmlentities($key)) == 0){
														$show = true;
													}
												}
											}

											if($show){
												$couleur = getFirstValue($base->query("SELECT couleur FROM categories WHERE nom='".explode(":", $pcategs[0])[0]."';")->fetch_assoc());
												if(empty($couleur)){
													$couleur = "#384A75";
												}

												$iskeyword = false;

												if(!isset($keywords) || $keywords == null || empty($keywords)){
													$iskeyword = true;
												}else{
													foreach ($keywords as $value) {
														if(stripos($row['nom'], $value) !== false || stripos($row['description'], $value) !== false){
															$iskeyword = true;
														}
													}
												}

												if($iskeyword){
													$count = $row['quantite'];
													$count = $count - getFirstValue($base->query("SELECT COUNT(*) FROM commands WHERE produit='".$row['id']."' AND etat!=5 AND etat!=6;")->fetch_assoc());
													if($count > 0){
														echo "<li style=\"background-color: ".$couleur.";\">";
								            			echo "<div class=\"product\">";
								            			echo "<div class=\"img\"><img src=\"./imgs/".$row['id']."/1.png\"></div>";
									            		echo "<a class=\"title\" href=\"./item.php?id=".$row['id']."\"><h1>".$row['nom']."</h1></a>";
									            		echo "<p class=\"price\">".$row['prix']."€</p>";
									            		echo "<p class=\"desc\">".$row['description']."</p>";
								            			echo "</div>";
								            			echo "</li>";
													}
												}
												
											}

									    }
									}

		            				$base->close();
		            			?>
		            		</ul>
		            	</div>
		        	</td>
		        </tr>
			</tbody>
		</table>
		<?php require "footer.php"; ?>
	</body>
</html>
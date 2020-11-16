<?php mb_internal_encoding('UTF-8'); ?>
<?php 
	if(!isset($_SESSION)) session_start();
	//$_SESSION["login"] = "a";
	require "clearcache.php";
?>
<!DOCTYPE html>
<html>
	<head>		
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<?php require "header.php"; ?>
		<?php require "database.php"; ?>
		<?php require "utils.php"; ?>
		<?php require "livraison.php"; ?>
		<?php
			if(!isset($_GET["id"]) || empty($_GET["id"])){
				echo "<script> location.replace(\"./products.php\"); </script>";
				header('Location: ./products.php');
			}else{

				$base = connectDataBase();

				$val = $base->query("SELECT * FROM products WHERE id='".$_GET["id"]."';")->fetch_assoc();

				if(is_null($val)){
					echo "<script> location.replace(\"./products.php\"); </script>";
					header('Location: ./products.php');
				}

				echo "<title>".$val['nom']."</title>";

				$show = true;

				$count = $val['quantite'];
				$count = $count - getFirstValue($base->query("SELECT COUNT(*) FROM commands WHERE produit='".$val["id"]."' AND etat!=5 AND etat!=6;")->fetch_assoc());

				if($count <= 0){
					$show = false;
				}

				$sell = $show;

				if(isset($_SESSION["login"])){ 
					$usrval = $base->query("SELECT * FROM users WHERE id='".$_SESSION["login"]."';")->fetch_assoc();
					if($usrval['type'] == 1){
						$show = true;
					}
					$usrcmd = getFirstValue($base->query("SELECT COUNT(*) FROM commands WHERE produit='".$val["id"]."' AND etat!=5 AND etat!=6 AND acheteur='".$usrval['id']."';")->fetch_assoc());
					if(!empty($usrcmd)){
						$show = true;
					}
				}

				if(!$show){
					echo "<script> location.replace(\"./products.php\"); </script>";
					header('Location: ./products.php');
				}

				$base->close();
			}
		?>
		
	</head>
	<body>
		<link rel = "stylesheet"
			type = "text/css"
			href = "./css/table.css"/>

		<table class="contenttable">
			<tbody>
				<tr>
		            <td class="lmenu">
		            	<table class="carac">
		            		<thead colspan="2">
		            			<h2>Charactéristiques</h2>
		            		</thead>
		            		<tbody>
		            			<tr>
			            			<td>
			            				<p>livraison:</p>
			            			</td>
			            			<td>
			            				<?php echo "<p>".getLivraisonString($val['livraison'])."</p>" ?>
			            			</td>
			            		</tr>
			            		<?php
			            			if(!empty($val['parametres'])){
			            				$parametres = explode(";",$val['parametres']);
				            			if(!empty($parametres)){
				            				foreach($parametres as $value){
				            					$param = explode(":",$value);
					            				echo "<tr>";
						            			echo "<td>";
						            			echo "<p><b>".$param[0].":</b></p>";
						            			echo "</td>";
						            			echo "<td>";
						            			echo "<p>".$param[1]."</p>";
						            			echo "</td>";
						            			echo "</tr>";
				            				}
				            			}
			            			}
			            		?>
			            	</tbody>
		            	</table>
		            </td>
		            <td class="content">
		            	<link rel = "stylesheet"
							type = "text/css"
							href = "./css/item.css"/>
		            	<div class="item">
		            		<?php echo "<h1>".$val['nom']."</h1>"; ?>
							<script>
								var slideIndex = 1;
								showDivs(slideIndex);

								function plusDivs(n) {
									showDivs(slideIndex += n);
								}

								function showDivs(n) {
									var i;
									var x = document.getElementsByClassName("sliderphoto");
									if (n > x.length) {slideIndex = 1}
									if (n < 1) {slideIndex = x.length}
									for (i = 0; i < x.length; i++) {
										x[i].style.display = "none";  
									}
									x[slideIndex-1].style.display = "block";  
								}
							</script>

		            		<div class="slider">
		            			<?php
		            				$first = true;

		            				$files = array_diff(scandir("./imgs/".$_GET["id"]."/"), array('.', '..'));

		            				foreach ($files as $value) {
		            					if($first){
		            						$first = false;
		            						echo "<img class=\"sliderphoto\" src=\"./imgs/".$_GET["id"]."/".$value."\">";
		            					}else{
		            						echo "<img class=\"sliderphoto\" style=\"display:none;\" src=\"./imgs/".$_GET["id"]."/".$value."\">";
		            					}
		            				}
		            			?>
							</div>
							<div class="sliderbuttons" style="width:100%">
								<div class="btnleft btn" onclick="plusDivs(-1)">&#10094;</div>
								<div class="btnright btn" onclick="plusDivs(1)">&#10095;</div>
							</div>
							<form action="./checkout.php" method="post">
								
								<?php 
									echo "<input type=\"hidden\" id=\"id\" name=\"id\" value=\"".$val['id']."\">";
									if(!$sell){
										echo "<div class=\"price disabled\"><input type=\"submit\" value=\"Achetter\" disabled><p>".$val['prix']."</p></div>";
									}else{
										echo "<div class=\"price\"><input type=\"submit\" value=\"Achetter\"><p>".$val['prix']."</p></div>";
									}
								?>

							</form>
							
							<?php echo "<p>".$val['description']."</p>"; ?>
		            	</div>
		        	</td>
		        </tr>
			</tbody>
		</table>
		<?php require "footer.php"; ?>
	</body>
</html>
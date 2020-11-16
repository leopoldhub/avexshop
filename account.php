<?php if(!isset($_SESSION)) session_start(); ?>
<?php require "utils.php"; ?>
<?php require "header.php"; ?>
<?php require "database.php"; ?>
<?php require "state.php"; ?>
<?php require "livraison.php"; ?>
<?php require_once "captcha.php"; ?>
<!DOCTYPE html>
<html>
	<script src='https://www.google.com/recaptcha/api.js' async defer></script>
	<head>
		<title></title>
		<link rel = "stylesheet" type = "text/css" href = "./css/infos.css"/>
	</head>
	<body>
		<?php 

			$captcha_response = false;

			if(isset($_POST['g-recaptcha-response'])){
				$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode(CAPTCHA_SECRET_KEY) .  '&response=' . urlencode($_POST['g-recaptcha-response']);
		        $responseKeys = json_decode(file_get_contents($url),true);

		        if($responseKeys["success"]) {
		        	$captcha_response = true;
		        }
		    }

		    $error = '';
		    $info = '';

		    if(isset($_GET["error"])){
		    	$error = $_GET["error"];
		    }
		    if(isset($_GET["info"])){
		    	$info = $_GET["info"];
		    }
		    ?>
		    <div class="frm">
		    <?php
			if(!isset($_SESSION["login"])){
			?>
			<link rel = "stylesheet" type = "text/css" href = "./css/login.css"/>
			
			<?php 
				if(isset($_GET["login"])){
							if(isset($_POST["mail"]) && isset($_POST["password"])){
								if(empty($error) && (!filter_var($_POST["mail"], FILTER_VALIDATE_EMAIL))){
									$error = "veuillez renseigner une adresse mail valide.";
								}
								if(empty($error) && (!preg_match('/^.{5,30}$/',$_POST["password"]))){
									$error = "veuillez renseigner un mot de passe valide (de 5 à 30 caractères).";
								}
								if(empty($error) && !$captcha_response){
									$error = "merci de compléter le captcha.";
								}
								if(empty($error)){
									$base = connectDataBase();
									if(!$base){
										$error = "connection impossible a la base de données, désolé du dérangement...";
									}
									if(empty($error)){
										$userval = $base->query("SELECT * FROM users WHERE mail='".$_POST["mail"]."';")->fetch_assoc();
										if(empty($error) && (empty($userval) || !password_verify($_POST["password"],$userval['pass']))){
											$error = "identifiants invalides.";
										}
										if(empty($error)){
											$_SESSION["login"] = $userval['id'];
											echo "<script> location.replace(\"./account.php\"); </script>";
											header('Location: ./account.php');
										}
									}
									$base->close();
								}
							}
						?>
						<?php if(!empty($error)){ ?>
							<div class="error"><p><?php echo $error; ?></p></div>
						<?php } ?>
						<?php if(!empty($info)){ ?>
							<div class="info"><p><?php echo $info; ?></p></div>
						<?php } ?>
						<form action="?login" method="post">
							<label><b>adresse mail:</b></label><br>
							<input type="mail" name="mail" placeholder="mon.adresse@mail.fr"><br>
							<label><b>mot de passe:</b></label><br>
							<input type="password" name="password" placeholder="(5 à 30 caractères)">
							<div class="captcha_wrapper">
								<div class="g-recaptcha" data-sitekey="<?php echo CAPTCHA_PUBLIC_KEY; ?>"></div>
							</div>
							<input type="submit" name="Me connecter">
						</form>
						<a href="?register">je n'ai pas de compte</a>

		<?php 	}else if(isset($_GET["register"])){ 
					if(isset($_POST["nom"]) && isset($_POST["prenom"]) && isset($_POST["tel"]) && isset($_POST["mail"]) && isset($_POST["password1"]) && isset($_POST["password2"])){
						if(empty($error) && (!preg_match('/^.{2,30}$/',$_POST["nom"]))){
							$error = "veuillez renseigner votre nom (2 à 30 caractères).";
						}
						if(empty($error) && (!preg_match('/^.{2,30}$/',$_POST["prenom"]))){
							$error = "veuillez renseigner votre prenom (2 à 30 caractères).";
						}
						if(empty($error) && (!preg_match('/^[0-9]{10}$/',$_POST["tel"]))){
							$error = "veuillez renseigner votre numéro de téléphone (10 chiffres).";
						}
						if(empty($error) && (!filter_var($_POST["mail"], FILTER_VALIDATE_EMAIL))){
							$error = "veuillez renseigner une adresse mail valide.";
						}
						if(empty($error) && (!preg_match('/^.{5,30}$/',$_POST["password1"]))){
							$error = "veuillez renseigner un mot de passe valide (de 5 à 30 caractères).";
						}
						if(empty($error) && ($_POST["password1"] != $_POST["password2"])){
							$error = "vos mots de passe ne correspondent pas.";
						}
						if(empty($error) && !$captcha_response){
							$error = "merci de compléter le captcha.";
						}
						if(empty($error)){
							$base = connectDataBase();
							if(!$base){
								$error = "connection impossible a la base de données, désolé du dérangement...";
							}
							if(empty($error)){
								$userval = $base->query("SELECT * FROM users WHERE mail='".$_POST["mail"]."';")->fetch_assoc();
								if(empty($error) && !empty($userval)){
									$error = "cette adresse mail est déja utilisée.";
								}
								if(empty($error) && !$base->query("INSERT INTO users (id, nom, prenom, mail, adresse, tel, pass, type) VALUES (NULL, '".$_POST["nom"]."', '".$_POST["prenom"]."', '".$_POST["mail"]."', NULL, '".$_POST["tel"]."', '".password_hash($_POST["password1"], PASSWORD_BCRYPT)."', '0')")){
									$error = "érreur lors de l'ajout de l'utilisateur dans la base de données: <br>".$base->error;
								}
								if(empty($error)){
									$_SESSION["login"] = $base->insert_id;
									echo "<script> location.replace(\"./account.php\"); </script>";
									header('Location: ./account.php');
								}
							}
							$base->close();
						}
					}
		?>
						<?php if(!empty($error)){ ?>
							<div class="error"><p><?php echo $error; ?></p></div>
						<?php } ?>
						<?php if(!empty($info)){ ?>
							<div class="info"><p><?php echo $info; ?></p></div>
						<?php } ?>

						<form action="?register" method="post">
							<label>nom:</label><br>
							<input type="text" name="nom" placeholder="(2 à 30 caractères)"><br>
							<label>prenom:</label><br>
							<input type="text" name="prenom" placeholder="(2 à 30 caractères)"><br>
							<label>téléphone:</label><br>
							<input type="tel" name="tel" placeholder="06XXXXXXXX"><br>
							<label>adresse mail:</label><br>
							<input type="mail" name="mail" placeholder="mon.adresse@mail.fr"><br>
							<label>mot de passe:</label><br>
							<input type="password" name="password1" placeholder="(5 à 30 caractères)"><br>
							<input type="password" name="password2" placeholder="(5 à 30 caractères)">
							<div class="captcha_wrapper">
								<div class="g-recaptcha" data-sitekey="<?php echo CAPTCHA_PUBLIC_KEY; ?>"></div>
							</div>
							<input type="submit" name="M'enregistrer">
						</form>
						<a href="?login">j'ai déja un compte</a>
		<?php	}else{
					echo "<script> location.replace(\"./account.php?login\"); </script>";
					header('Location: ./account.php?login');
				}
			}else{
				if(isset($_GET["logout"])){
					$_SESSION["login"] = null;
					echo "<script> location.replace(\"./account.php\"); </script>";
					header('Location: ./account.php');
				}else{
					$base = connectDataBase();
					if(empty($error) && !$base){
						$error = "connection impossible a la base de données, désolé du dérangement...";
					}
					$userval = $base->query("SELECT * FROM users WHERE id='".$_SESSION["login"]."';")->fetch_assoc();
					if(empty($error) && empty($userval)){
						echo "<script> location.replace(\"./account.php?error=".urlencode("érreur lors de l'authentification. essayez de vous reconnecter.")."\"); </script>";
						header('Location: ./account.php?error='.urlencode("érreur lors de l'authentification. essayez de vous reconnecter.").'');
					}
					$base->close();
					if($userval['type'] == 0){
						$base = connectDataBase();
						if(empty($error) && !$base){
							$error = "connection impossible a la base de données, désolé du dérangement...";
						}
						if(isset($_POST['password1']) && isset($_POST['password2'])){
							$info = '';
							if(empty($error) && (!preg_match('/^.{5,30}$/',$_POST["password1"]))){
								$error = "veuillez renseigner un mot de passe valide (de 5 à 30 caractères).";
							}
							if(empty($error) && ($_POST["password1"] != $_POST["password2"])){
								$error = "vos mots de passe ne correspondent pas.";
							}
							if(empty($error) && !$captcha_response){
								$error = "merci de compléter le captcha.";
							}
							if(empty($error) && !$base->query("UPDATE users SET pass='".password_hash($_POST['password1'], PASSWORD_BCRYPT)."' WHERE id='".$_SESSION['login']."';")){
								$error = "impossible de changer votre mot de passe pour le moment du a une érreur serveur.";
							}
							if(empty($error)){
								echo "<script> location.replace(\"./account.php?info=".urlencode("vos informations ont bien été changées.")."\"); </script>";
								header('Location: ./account.php?info='.urlencode("vos informations ont bien été changées.").'');
							}
						}
						if(isset($_POST['tel']) && isset($_POST['adresse'])){
							$info = '';
							if(empty($error) && (!preg_match('/^[0-9]{10}$/',$_POST["tel"]))){
								$error = "veuillez renseigner votre numéro de téléphone (10 chiffres).";
							}
							if(empty($error) && (!preg_match('/^.{5,30}$/',$_POST["adresse"]))){
								$error = "veuillez renseigner votre adresse (de 5 à 40 caractères).";
							}
							if(empty($error) && !$captcha_response){
								$error = "merci de compléter le captcha.";
							}
							if(empty($error) && !$base->query("UPDATE users SET tel='".$_POST['tel']."', adresse='".$_POST['adresse']."' WHERE id='".$_SESSION['login']."';")){
								$error = "impossible de changer votre adresse et numéro de téléphone pour le moment du a une érreur serveur.";
							}
							if(empty($error)){
								echo "<script> location.replace(\"./account.php?info=".urlencode("vos informations ont bien été changées.")."\"); </script>";
								header('Location: ./account.php?info='.urlencode("vos informations ont bien été changées.").'');
							}
						}
						$base->close();
		?>
						<link rel = "stylesheet" type = "text/css" href = "./css/account.css"/>
						<h1>Mon compte</h1>
						<h2>profil</h2>
						<?php if(!empty($error)){ ?>
							<div class="error"><p><?php echo $error; ?></p></div>
						<?php } ?>
						<?php if(!empty($info)){ ?>
							<div class="info"><p><?php echo $info; ?></p></div>
						<?php } ?>
							<p><b>nom: </b><?php echo $userval['nom'] ; ?></p>
							<p><b>prenom: </b><?php echo $userval['prenom'] ; ?></p>
							<p><b>mail: </b><?php echo $userval['mail'] ; ?></p>
							<form class="left" method="post">
								<label><b>tel: </b></label><br>
								<input type="text" name="tel" value="<?php echo $userval['tel'] ; ?>"><br>
								<label><b>adresse (obligatoire pour les livraisons): </b></label><br>
								<input type="text" name="adresse" value="<?php echo $userval['adresse'] ; ?>"><br>
								<div class="captcha_wrapper">
									<div class="g-recaptcha" data-sitekey="<?php echo CAPTCHA_PUBLIC_KEY; ?>"></div>
								</div>
								<input type="submit" value="mettre a jour">
							</form>
							<form class="right" method="post">
								<label><b>mot de passe: </b></label><br>
								<input type="password" name="password1"><br>
								<label><b>mot de passe: </b></label><br>
								<input type="password" name="password2"><br>
								<div class="captcha_wrapper">
									<div class="g-recaptcha" data-sitekey="<?php echo CAPTCHA_PUBLIC_KEY; ?>"></div>
								</div>
								<input type="submit" value="mettre a jour">
							</form>
							<?php
								$error = '';
								$info = '';

								$base = connectDataBase();
								if(empty($error) && !$base){
									$error = "connection impossible a la base de données, désolé du dérangement...";
								}
								$commands = $base->query("SELECT * FROM commands WHERE acheteur='".$_SESSION["login"]."' ORDER BY id DESC;");
								if(empty($error) && !$commands){
									$error = "connection impossible de récupérer vos commandes.";
								}
								if(empty($error)){
									?>
									<table>
										<thead>
											<tr>
												<td><b>numéro de commande</b></td>
												<td><b>produit</b></td>
												<td><b>etat</b></td>
											</tr>
										</thead>
										<tbody>
										<?php
											while($row = mysqli_fetch_assoc($commands)){
												?>
												<tr>
													<td><?php echo $row['numcommande'] ; ?></td>
														<?php $produit = $base->query("SELECT * FROM products WHERE id='".$row['produit']."'")->fetch_assoc(); ?>
													<td><a href="./item.php?id=<?php echo $row['produit'] ; ?>"><?php echo $produit['nom'] ; ?></a></td>
													<td><?php echo getStateString($row['etat'], $row) ; ?></td>
												</tr>
												<?php
											}
										?>
										</tbody>
									</table>
								<?php
								}
							?>
							<?php if(!empty($error)){ ?>
							<div class="error"><p><?php echo $error; ?></p></div>
							<?php } ?>
							<?php if(!empty($info)){ ?>
								<div class="info"><p><?php echo $info; ?></p></div>
							<?php } ?>

						<?php

						?>
						
						


		<?php
					}else {
		?>
						<link rel = "stylesheet" type = "text/css" href = "./css/dashboard.css"/>

						<div class="btnbar">
							<form action="" id="menuform" method="post">
								<input type="hidden" name="section" id="section">
								<input type="submit" name="sec1" id="sec1" value="Produits">
								<input type="submit" name="sec2" id="sec2" value="Commandes">
								<input type="submit" name="sec3" id="sec3" value="News">
							</form>
						</div>

						<script>
							var form = document.getElementById('menuform');

							var sec1 = document.getElementById('sec1');
							var sec2 = document.getElementById('sec2');
							var sec3 = document.getElementById('sec3');

							var section = document.getElementById('section');

							form.addEventListener('submit', function(e) {
								e.preventDefault();
							});

							sec1.onclick = function() {
								section.setAttribute('value', '1');
								form.submit();
							}

							sec2.onclick = function() {
								section.setAttribute('value', '2');
								form.submit();
							}

							sec3.onclick = function() {
								section.setAttribute('value', '3');
								form.submit();
							}

						</script>
		<?php
						if((isset($_POST['section']) && $_POST['section'] == 1) || !isset($_POST['section']) || ($_POST['section'] != 1 && $_POST['section'] != 2 && $_POST['section'] != 3)){

							if(isset($_POST['nom']) && isset($_POST['description']) && isset($_FILES['miniature']) && isset($_FILES['file']) && isset($_POST['quantite']) && isset($_POST['prix']) && isset($_POST['categories']) && isset($_POST['livraison']) && isset($_POST['parametres'])){
								if(empty($error) && empty($_POST['nom'])){
									$error = "merci de donner un nom au produit.";
								}
								if(empty($error) && empty($_POST['description'])){
									$error = "merci de mettre une description.";
								}
								if(empty($error) && empty($_FILES['miniature'])){
									$error = "merci de définir une miniature.";
								}
								if(empty($error)){
									$base = connectDataBase();
									if(!$base){
										$error = "connection impossible a la base de données, désolé du dérangement...";
									}
									if(empty($error)){
										$maxid = getFirstValue($base->query("SELECT MAX(id) FROM products;")->fetch_assoc());
										if(is_null($maxid)){
											$maxid = 0;
										}else{
											$maxid++;
										}

										$root = $_SERVER["DOCUMENT_ROOT"];

										if(!file_exists($root."./imgs/".$maxid)){
											mkdir($root."/imgs/".$maxid,0705);
										}

										move_uploaded_file($_FILES['miniature']['tmp_name'][0],$root."/imgs/".$maxid."/0.png");

										$countfiles = count($_FILES['file']['name']);
										$o = $countfiles;
										for($i=0;$i<$countfiles;$i++){
											move_uploaded_file($_FILES['file']['tmp_name'][$i],$root."/imgs/".$maxid."/".$o.".png");
											$o--;
										}
										if(empty($error) && !$base->query("INSERT INTO products (id, nom, prix, quantite, description, livraison, parametres, categories) VALUES ($maxid, '".$_POST['nom']."', '".$_POST['prix']."', '".$_POST['quantite']."', '".$_POST['description']."', '".$_POST['livraison']."', '".implode(";", explode('
',$_POST['parametres']))."', '".implode(";", $_POST['categories'])."')")){
											$error = "érreur lors de l'ajout du produit dans la base de données: <br>".$base->error;
										}
									}
									if(empty($error) && empty($info)){
										$info = "produit ajouté avec succes!";
										echo "<script> location.replace(\"./account.php?info=".urlencode($info)."\"); </script>";
										header('Location: ./account.php?info='.urlencode($info).'');
									}
									$base->close();
								}
							}else if(isset($_POST['quantite'])){
								$base = connectDataBase();
								if(!$base){
									$error = "connection impossible a la base de données, désolé du dérangement...";
								}
								if(empty($error) && !$base->query("UPDATE products SET quantite=".$_POST['quantite']." WHERE id=".$_POST['id'].";")){
									$error = "impossible de changer la quantité dans la base de données.";
								}
								if(empty($error)){
									$info = "la quantité a bien été changée!";
								}
								$base->close();
							}


		?>					
							<?php if(!empty($error)){ ?>
								<div class="error"><p><?php echo $error; ?></p></div>
							<?php } ?>
							<?php if(!empty($info)){ ?>
								<div class="info"><p><?php echo $info; ?></p></div>
							<?php } ?>
							<h1>Produits</h1>
							<form enctype="multipart/form-data" id="addproduct" method="post">
								<h2>ajouter un produit</h2>
								<input type="text" name="nom" placeholder="nom"><br>
								<textarea name="description" id="description" form="addproduct" placeholder="description"></textarea><br>
								<input type="text" name="vendeur" placeholder="vendeur: 0600000000 xxxxxxx@xxx.xx"><br>
								<input type="file" name="miniature[]"><br>
								<input type="file" name="file[]" multiple><br>
								<label><b>quantité:</b></label><br>
								<input type="number" name="quantite" value="1"><br>
								<label><b>prix:</b></label><br>
								<input type="text" name="prix" value="1"><br>
								<select name="categories[]" multiple>
									<?php
										$base = connectDataBase();
										$categs = $base->query("SELECT nom FROM categories;");
					    				if($categs){
											while($row = mysqli_fetch_assoc($categs)){
												echo "<option value=\"".$row['nom']."\">".$row['nom']."</option>";
											}
										}
										$base->close();
									?>
								</select><br>
								<select name="livraison" id="livraison">
							    	<option value="1"><?php echo getLivraisonString(1); ?></option>
							    	<option value="0"><?php echo getLivraisonString(0); ?></option>
						    	</select><br>
						    	<textarea name="parametres" id="parametres" form="addproduct" placeholder="certificat:oui"></textarea><br>
						    	<input type="submit" name="Ajouter">
							</form>
							<h2>liste des produits</h2>
							<?php
								$error = '';
								$info = '';
								$base = connectDataBase();
								if(empty($error) && !$base){
									$error = "connection impossible a la base de données, désolé du dérangement...";
								}
								if(empty($error)){
									$products = $base->query("SELECT * FROM products ORDER BY id DESC;");
									if(empty($error) && !$products){
										$error = "impossible de récupérer les produits, désolé du dérangement...";
									}
									if(empty($error)){
									?>
										<input type="submit" name="showprodzero" id="showprodzero" value="Afficher les produits non disponnibles">
										<table>
											<thead>
												<tr>
													<td><b>id</b></td>
													<td><b>nom</b></td>
													<td><b>prix</b></td>
													<td><b>quantité</b></td>
													<td><b>disponnible</b></td>
													<td><b>description</b></td>
													<td><b>vendeur</b></td>
													<td><b>livraison</b></td>
													<td><b>paramètres</b></td>
													<td><b>catégories</b></td>
												</tr>
											</thead>
											<tbody>
											<?php
												while($row = mysqli_fetch_assoc($products)){
													$show = "on";
													$style = "";
													if($row['quantite'] <= 0){
														$show = "off";
														$style = "display: none;";
													}
													?>
													<tr name="<?php echo $show ; ?>" style="<?php echo $style ; ?>">
													<td><?php echo $row['id'] ; ?></td>
													<td><a href="./item.php?id=<?php echo $row['id'] ; ?>"><?php echo $row['nom'] ; ?></a></td>
													<td><?php echo $row['prix'] ; ?></td>
													<td>
														<form method="post">
															<input type="hidden" name="section" value="<?php echo $_POST['section'] ; ?>">
															<input type="hidden" name="id" value="<?php echo $row['id'] ; ?>">
															<input type="number" name="quantite" value="<?php echo $row['quantite'] ; ?>">
															<input type="submit" value="changer">
														</form>
													</td>
													<?php $dispo = getFirstValue($base->query("SELECT count(*) FROM commands WHERE produit='".$row['id']."' AND etat!=5 AND etat!=6;")->fetch_assoc()); ?>
													<td><?php echo (($row['quantite'] - $dispo)<0?0:($row['quantite'] - $dispo)) ; ?></td>
													<td><?php echo $row['description'] ; ?></td>
													<td><?php echo $row['vendeur'] ; ?></td>
													<td><?php echo getLivraisonString($row['livraison']) ; ?></td>
													<td><?php echo $row['parametres'] ; ?></td>
													<td><?php echo $row['categories'] ; ?></td>
													</tr>
													<?php
												}
											?>
											</tbody>
										</table>
										<script>
											var showprodzero = document.getElementById('showprodzero');
											var zero = document.getElementsByName('off');

											var swtch = false;

											showprodzero.onclick = function() {
												if(swtch == true){
													swtch = false;
													for (i=0;i<zero.length;i++){
    													zero[i].setAttribute('style', 'display: none;');
    												}
													showprodzero.setAttribute('value', 'Afficher les produits non disponnibles');
												}else{
													swtch = true;
													for (i=0;i<zero.length;i++){
    													zero[i].setAttribute('style', '');
    												}
													showprodzero.setAttribute('value', 'Masquer les produits non disponnibles');
												}
											}

										</script>
									<?php
									}
								}
								$base->close();
							?>
		<?php
						}else if(isset($_POST['section']) && $_POST['section'] == 2){
							if(isset($_POST['etat'])){
								$base = connectDataBase();
								if(!$base){
									$error = "connection impossible a la base de données, désolé du dérangement...";
								}
								if(empty($error) && !$base->query("UPDATE commands SET etat=".$_POST['etat']." WHERE id=".$_POST['id'].";")){
									$error = "impossible de changer l'état' dans la base de données.";
								}
								if(isset($_POST['dte']) && isset($_POST['lieu'])){
									if(empty($error) && !$base->query("UPDATE commands SET dte='".$_POST['dte']."', lieu='".$_POST['lieu']."' WHERE id=".$_POST['id'].";")){
										$error = "impossible de changer la date et le lieu dans la base de données.";
									}
								}
								if(empty($error)){
									$info = "étape changée!";
								}
								$base->close();
							}
		?>
							<?php if(!empty($error)){ ?>
								<div class="error"><p><?php echo $error; ?></p></div>
							<?php } ?>
							<?php if(!empty($info)){ ?>
								<div class="info"><p><?php echo $info; ?></p></div>
							<?php } ?>
							<h1>Commandes</h1>
							<h2>liste des commandes</h2>
							<?php
								$error = '';
								$info = '';
								$base = connectDataBase();
								if(empty($error) && !$base){
									$error = "connection impossible a la base de données, désolé du dérangement...";
								}
								if(empty($error)){
									$commands = $base->query("SELECT * FROM commands ORDER BY id DESC;");
									if(empty($error) && !$commands){
										$error = "impossible de récupérer les commandes, désolé du dérangement...";
									}
									if(empty($error)){
									?>
										<input type="submit" name="showcmdzero" id="showcmdzero" value="Afficher les commandes terminées ou annulées">
										<table>
											<thead>
												<tr>
													<td><b>id</b></td>
													<td><b>acheteur</b></td>
													<td><b>contact acheteur</b></td>
													<td><b>produit</b></td>
													<td><b>vendeur</b></td>
													<td><b>numéro de commande</b></td>
													<td><b>etat</b></td>
												</tr>
											</thead>
											<tbody>
											<?php
												while($row = mysqli_fetch_assoc($commands)){
													$show = "on";
													$style = "";
													if($row['etat'] >= 5){
														$show = "off";
														$style = "display: none;";
													}
													?>
													<tr name="<?php echo $show ; ?>" style="<?php echo $style ; ?>">
													<td><?php echo $row['id'] ; ?></td>
													<?php $acheteur = $base->query("SELECT * FROM users WHERE id='".$row['acheteur']."'")->fetch_assoc(); ?>
													<td><?php echo "[".$row['acheteur']."] ".$acheteur['nom']." ".$acheteur['prenom'] ; ?></td>
													<td><?php echo $acheteur['mail']." | ".$acheteur['tel']." | ".$acheteur['adresse'] ; ?></td>
													<?php $produit = $base->query("SELECT * FROM products WHERE id='".$row['produit']."'")->fetch_assoc(); ?>
													<td><a href="./item.php?id=<?php echo $row['produit'] ; ?>"><?php echo $produit['nom'] ; ?></a></td>
													<td><?php echo $produit['vendeur'] ; ?></td>
													<td><?php echo $row['numcommande'] ; ?></td>
													<td><?php echo getStateString($row['etat'], $row) ; ?></td>
													<td>
														<?php if($row['etat'] < 5){ ?>
														<form method="post">
															<input type="hidden" name="section" value="<?php echo $_POST['section'] ; ?>">
															<input type="hidden" name="id" value="<?php echo $row['id'] ; ?>">
															<input type="hidden" name="etat" value="<?php echo getNextState($produit['livraison'], $row['etat']) ; ?>">
															<?php if($row['etat'] == 3){ ?>
																<input type="text" name="dte" placeholder="le xx/xx/xxxx à xxhxx">
																<input type="text" name="lieu" placeholder="place de l'hotel de ville de Compiègne">
															<?php } ?>
															<input type="submit" value="étape suivante">
														</form>
														<?php } ?>
													</td>
													<td>
														<?php if($row['etat'] < 5){ ?>
														<form method="post">
															<input type="hidden" name="section" value="<?php echo $_POST['section'] ; ?>">
															<input type="hidden" name="id" value="<?php echo $row['id'] ; ?>">
															<input type="hidden" name="etat" value="6">
															<input type="submit" value="annuler">
														</form>
														<?php } ?>
													</td>
													</tr>
													<?php
												}
											?>
											</tbody>
										</table>
										<script>
											var showcmdzero = document.getElementById('showcmdzero');
											var zero = document.getElementsByName('off');

											var swtch = false;

											showcmdzero.onclick = function() {
												if(swtch == true){
													swtch = false;
													for (i=0;i<zero.length;i++){
    													zero[i].setAttribute('style', 'display: none;');
    												}
													showcmdzero.setAttribute('value', 'Afficher les commandes terminées ou annulées');
												}else{
													swtch = true;
													for (i=0;i<zero.length;i++){
    													zero[i].setAttribute('style', '');
    												}
													showcmdzero.setAttribute('value', 'Masquer les commandes terminées ou annulées');
												}
											}

										</script>
									<?php
									}
								}
								$base->close();
							?>
		<?php
						}else if(isset($_POST['section']) && $_POST['section'] == 3){
							if(isset($_POST['titre']) && isset($_POST['description']) && isset($_FILES['image'])){
								if(empty($error) && empty($_POST['titre'])){
									$error = "merci de donner un titre a la news.";
								}
								if(empty($error) && empty($_POST['description'])){
									$error = "merci de mettre une description.";
								}
								if(empty($error) && empty($_FILES['image'])){
									$error = "merci de définir une miniature.";
								}
								if(empty($error)){
									$base = connectDataBase();
									if(!$base){
										$error = "connection impossible a la base de données, désolé du dérangement...";
									}
									if(empty($error)){
										$maxid = getFirstValue($base->query("SELECT MAX(id) FROM news;")->fetch_assoc());
										if(is_null($maxid)){
											$maxid = 0;
										}else{
											$maxid++;
										}

										$root = $_SERVER["DOCUMENT_ROOT"];

										if(!file_exists($root."./imgs/news")){
											mkdir($root."/imgs/news",0705);
										}

										move_uploaded_file($_FILES['image']['tmp_name'][0],$root."/imgs/news/".$maxid.".png");

										date_default_timezone_set("Europe/Paris");
										$dte = date("d/m/Y à H\hi");

										if(empty($error) && !$base->query("INSERT INTO news (id, dte, titre, description) VALUES ($maxid, '".$dte."', '".$_POST['titre']."', '".$_POST['description']."')")){
											$error = "érreur lors de l'ajout de la news dans la base de données: <br>".$base->error;
										}
									}
									if(empty($error) && empty($info)){
										$info = "news ajoutée avec succes!";
										/*echo "<script> location.replace(\"./account.php?info=".urlencode($info)."\"); </script>";
										header('Location: ./account.php?info='.urlencode($info).'');*/
									}
									$base->close();
								}
							}
							?>
							<?php if(!empty($error)){ ?>
								<div class="error"><p><?php echo $error; ?></p></div>
							<?php } ?>
							<?php if(!empty($info)){ ?>
								<div class="info"><p><?php echo $info; ?></p></div>
							<?php } ?>
							<form enctype="multipart/form-data" id="addnews" method="post">
								<h2>Ajouer une news</h2>
								<input type="hidden" name="section" value="<?php echo $_POST['section'] ; ?>">
								<input type="text" name="titre" placeholder="Titre"><br>
								<textarea name="description" id="description" form="addnews" placeholder="<h3>Sous Titre</h3><p>texte <b>important</b></p>"></textarea><br>
								<input type="file" name="image[]"><br>
						    	<input type="submit" name="Ajouter">
							</form>
							<?php
						}
		?>

		<?php
					}
		?>

		<?php
				}
			}
		?>
		</div>
		<?php require "footer.php"; ?>
	</body>
</html>
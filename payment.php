<?php if(!isset($_SESSION)) session_start(); ?>
<?php require_once 'config.php'; ?>
<?php mb_internal_encoding('UTF-8'); ?>
<?php require "utils.php"; ?>
<?php require "header.php"; ?>
<?php require "database.php"; ?>
<?php require "livraison.php"; ?>
<?php

$payment_id = $statusMsg = '';
$ordStatus = 'error';

$base = connectDataBase();
$userval = $base->query("SELECT * FROM users WHERE id='".intval($_SESSION["login"])."';")->fetch_assoc();
if(!isset($_SESSION['login']) || !$base || !$userval || $userval['type'] == 1){
	$statusMsg = "Utilisateur invalide... essayez de vous reconnecter... (payement non éffectué)";
}else if(!isset($_POST["id"]) || empty($_POST["id"])){
	$statusMsg = "Produit invalide... (payement non éffectué)";
}

if(empty($statusMsg)){
	$product = $base->query("SELECT * FROM products WHERE id='".$_POST["id"]."';")->fetch_assoc();
	$count = $product['quantite'];
	$count = $count - getFirstValue($base->query("SELECT COUNT(*) FROM commands WHERE produit='".$product['id']."' AND etat!=5 AND etat!=6;")->fetch_assoc());
	if(!$product || !$count){
		$statusMsg = "Produit invalide... retournez sur la page du produit et réessayez... (payement non éffectué)";
	}else if($count <= 0){
		$statusMsg = "Ce produit n'est plus disponnible... (payement non éffectué)";
	}else if($product['livraison'] == 1 && ($userval['adresse'] == null || empty($userval['adresse']))){
		$statusMsg = "Ce produit est livré par la poste. Vous devez obligatoirement définir une adresse postale dans 'mon compte'<br> avant de pouvoir d'éffecter un payement de ce type. (payement non éffectué)";
	}
}

if(empty($statusMsg)){
	if(!empty($_POST['stripeToken'])){
		$token = $_POST['stripeToken'];
		$name = $userval['nom'];
		$email = $userval['mail'];

		require_once('./stripe/init.php');

		\Stripe\Stripe::setApiKey(STRIPE_API_KEY);

		try {
			$customer = \Stripe\Customer::create(array(
				'name' => $name,
				'email' => $email,
				'source' => $token
			));
		}catch(Exception $e){
			$api_error = $e->getMessage();
		}

		if(empty($api_error) && $customer){

			$itemPriceCents = ($product['prix']*100);

			try{
				$charge = \Stripe\Charge::create(array(
					'customer' => $customer->id,
					'amount' => $itemPriceCents,
					'currency' => $currency,
					'description' => '['.$product['id'].'] '.$product['nom']
				));
			}catch(Exception $e){
				$api_error = $e->getMessage();
			}

			if(empty($api_error) && $charge){
				$chargeJson = $charge->jsonSerialize();

				if($chargeJson['amount_refunded'] == 0 && empty($chargeJson['failure_code']) && $chargeJson['paid'] == 1 && $chargeJson['captured'] == 1){

					$transactionId = $chargeJson['balance_transaction'];
					$paidAmount = $chargeJson['amount'];
					$paidAmount = ($paidAmount / 100);
					$paidCurrency = $chargeJson['currency'];
					$payment_status = $chargeJson['status'];

					///////////////////////////
					// sauvegarder trace bdd //
					///////////////////////////

					///////////////////////////
					//  ajouter commande bdd //
					///////////////////////////

					$base->query("INSERT INTO commands (id, acheteur, produit, dte, lieu, numcommande, etat, suivis) VALUES (NULL, '".$userval['id']."', '".$product['id']."', NULL, NULL, '".$transactionId."', '0', NULL)");

					//set payment id!
					$payment_id = $base->insert_id;

					if($payment_status == 'succeeded'){
						$ordStatus = 'success';
						$statusMsg = 'Votre achat a bien été éffectué! rendez-vous sur "mon compte" pour suivre votre produit.';
					}else{
						$statusMsg = "Votre achat a échoué! (payement non éffectué)";
					}

				}else{
					$statusMsg = "La transaction a échouée! (payement non éffectué)";
				}
			}else{
				$statusMsg = "La création de charge a échouée! (payement non éffectué) $api_error";
			}

		}else{
			$statusMsg = "Informations de carte invalide! (payement non éffectué) $api_error";
		}

	}else{
		$statusMsg = "Érreur lors de l'envoi de la requète! (payement non éffectué)";
	}
}

$base->close();
?>

<!DOCTYPE html>
<html>
	<head>
		<title></title>
		<link rel = "stylesheet" type = "text/css" href = "./css/infos.css"/>
		<link rel = "stylesheet" type = "text/css" href = "./css/checkout.css"/>
	</head>
	<body>
		<div class="panel" style="padding-top: 1%; padding-bottom: 1%;">
			<div class="status" style="text-align: left;">
				<?php if(!empty($payment_id)){ ?>

					<h3 class="info"><?php echo $statusMsg; ?></h3>

					<h3>Informations de payement:</h3>
					<p><b>Numéro de commande: </b><?php echo $payment_id; ?></p>
					<p><b>ID de transaction: </b><?php echo $transactionId; ?></p>
					<p><b>Montant payé: </b><?php echo $paidAmount." ".$paidCurrency; ?></p>
					<p><b>État du payement: </b><?php echo $payment_status; ?></p>
					<h3>Informations du produit:</h3>
					<p><b>Nom: </b><?php echo $product['nom']; ?></p>
					<p><b>Prix: </b><?php echo $product['prix']." ".$currency; ?></p>
					<p><b>Livraison: </b><?php echo getLivraisonString($product['livraison']); ?></p>

					<?php if($product['livraison'] == 1){ ?>
						<p><b>Adresse de livraison: </b><?php echo $userval['adresse']; ?></p>
					<?php } ?>

					<a href="./account.php" class="btn" style="text-decoration: none;">Mon compte</a>

				<?php }else{ ?>
					<h3 class="error"><?php echo $statusMsg; ?></h3>
				<?php } ?>
			</div>
		</div>
	</body>
</html>
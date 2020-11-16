<?php if(!isset($_SESSION)) session_start(); ?>
<?php require_once 'config.php'; ?>
<?php mb_internal_encoding('UTF-8'); ?>
<?php require "utils.php"; ?>
<?php require "header.php"; ?>
<?php require "database.php"; ?>
<?php require "livraison.php"; ?>
<?php

$statusMsg = '';

$base = connectDataBase();
$userval = $base->query("SELECT * FROM users WHERE id='".intval($_SESSION["login"])."';")->fetch_assoc();
if(!isset($_SESSION['login']) || !$base || !$userval || $userval['type'] == 1){
  $statusMsg = "Utilisateur invalide... essayez de vous reconnecter...";
}else if(!isset($_POST["id"]) || empty($_POST["id"])){
  $statusMsg = "Produit invalide...";
}

if(empty($statusMsg)){
  $product = $base->query("SELECT * FROM products WHERE id='".$_POST["id"]."';")->fetch_assoc();
  $count = $product['quantite'];
  $count = $count - getFirstValue($base->query("SELECT COUNT(*) FROM commands WHERE produit='".$product['id']."' AND etat!=5 AND etat!=6;")->fetch_assoc());
  if(!$product || !$count){
    $statusMsg = "Produit invalide... retournez sur la page du produit et réessayez...";
  }else if($count <= 0){
    $statusMsg = "Ce produit n'est plus disponnible...";
  }else if($product['livraison'] == 1 && ($userval['adresse'] == null || empty($userval['adresse']))){
    $statusMsg = "Ce produit est livré par la poste. Vous devez obligatoirement définir une adresse postale dans 'mon compte'<br> avant de pouvoir d'éffecter un payement de ce type.";
  }
}

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Achat de <?php echo $product['nom']; ?></title>
    <script src="https://js.stripe.com/v3/"></script>
  </head>
  <body>
    <div class="panel">
      <?php if(empty($statusMsg)){ ?>
        <img src="./imgs/cbs.png">
        <div class="panel-heading">
            <!--<h3 class="panel-title">Achat</h3>-->
            <p><b>Produit:</b> <?php echo $product['nom']; ?></p>
            <p><b>Prix:</b> <?php echo ''.$product['prix'].' '.$currency; ?></p>
            <p><b>Livraison:</b> <?php echo getLivraisonString($product['livraison']); ?></p>
        </div>
        <div class="panel-body">
            <!-- Display errors returned by createToken -->
            <div id="paymentResponse" class="error"></div>
            <!-- Payment form -->
            <form action="payment.php" method="POST" id="paymentFrm">
                <div class="form-group">
                    <label>Numéro de carte</label>
                    <div id="card_number" class="field"></div>
                </div>
                <div class="row">
                    <div class="left">
                        <div class="form-group">
                            <label>Date d'expiration</label>
                            <div id="card_expiry" class="field"></div>
                        </div>
                    </div>
                    <div class="right">
                        <div class="form-group">
                            <label>Code de sécurité (CVV)</label>
                            <div id="card_cvc" class="field"></div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success" id="payBtn">Payer</button>
            </form>
        </div>
      <?php }else{ ?>
        <p class="error"><?php echo $statusMsg; ?></p>
      <?php } ?>
    </div>
    <script>
      var stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');

      var elements = stripe.elements();

      var style = {
        base: {
          fontWeight: 500,
          fontFamily: 'Roboto, Open Sans, Segoe UI, sans-serif',
          fontSize: '16px',
          lineHeight: '1.8',
          color: '#555',
          backgroundColor: '#fff',
          '::placeholder': {
            color: '#888'
          },
        },
        invalid: {
          color: '#eb1c26',
        }
      };

      var cardElement = elements.create('cardNumber', {
        style: style
      });
      cardElement.mount('#card_number');

      var exp = elements.create('cardExpiry', {
        'style': style
      });
      exp.mount('#card_expiry');

      var cvc = elements.create('cardCvc', {
        'style': style
      });
      cvc.mount('#card_cvc');

      var form = document.getElementById('paymentFrm');

      var resultContainer = document.getElementById('paymentResponse');

      cardElement.addEventListener('change', function(event) {
        if(event.error){
          resultContainer.innerHTML = '<p>'+event.error.message+'</p>';
        }else{
          resultContainer.innerHTML = '';
        }
      });

      form.addEventListener('submit', function(e) {
        e.preventDefault();
        createToken();
      });

      function createToken() {
        stripe.createToken(cardElement).then(function(result) {
          if(result.error){
            resultContainer.innerHTML = '<p>'+result.error.message+'</p>';
          }else{
            stripeTokenHandler(result.token);
          }
        });
      }

      function stripeTokenHandler(token){

        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'stripeToken');
        hiddenInput.setAttribute('value', token.id);
        form.appendChild(hiddenInput);

        var idInput = document.createElement('input');
        idInput.setAttribute('type', 'hidden');
        idInput.setAttribute('name', 'id');
        idInput.setAttribute('value', '<?php echo $product['id']; ?>');
        form.appendChild(idInput);

        form.submit();
      }

    </script>
    <link rel = "stylesheet" type = "text/css" href = "./css/infos.css"/>
    <link rel = "stylesheet" type = "text/css" href = "./css/checkout.css"/>
    <?php require "footer.php"; ?>
  </body>
</html>

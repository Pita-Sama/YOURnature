<?php
session_start();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Annullato - Fiori Ribelli</title>
    <link rel="stylesheet" href="css/payment_cancel.css">
</head>
<body>
    <header>
        <h1>Fiori Ribelli</h1>
    </header>

    <div class="cancel-container">
        <div class="cancel-icon">✕</div>
        <h2>Pagamento Annullato</h2>
        <p>Il tuo pagamento è stato annullato o si è verificato un problema durante l'elaborazione.</p>
        <p>Nessun addebito è stato effettuato sulla tua carta.</p>
        
        <a href="checkout.php" class="retry-button">Riprova</a>
        <a href="cart.php" class="back-button">Torna al carrello</a>
    </div>

    <footer>
        <p>&copy; 2025 Fiori Ribelli - Tutti i diritti riservati</p>
    </footer>
</body>
</html>

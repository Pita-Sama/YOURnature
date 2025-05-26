<?php
session_start();
require_once('vendor/autoload.php');

// Verifica che sia presente l'ID del pagamento
if (!isset($_GET['payment_intent'])) {
    header("Location: index.php");
    exit;
}

$payment_intent_id = $_GET['payment_intent'];

// Chiave privata
\Stripe\Stripe::setApiKey('sk_test_51RQR0lRrDuxDKDzorVsUY8EDHiTXoCFatWVKBN2dkOPtyjYuBDcssan4NVC2s1N8YNogpdFMDcdnENtG6npWdlnH00gTiLyBz4');

try {
    // Recupera i dettagli del pagamento
    $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
    
    // Verifica che il pagamento sia andato a buon fine
    if ($intent->status !== 'succeeded') {
        throw new Exception("Il pagamento non è stato completato con successo.");
    }
    
    // A questo punto possiamo registrare l'ordine nel database
    // Connessione al database
    require_once("collegamento_db.php");
    $pdo = pdoDB();
    
    // Ottieni i dati di spedizione
    $shipping = $_SESSION['shipping'];
    
    // Crea un nuovo ordine
    $stmt = $pdo->prepare("INSERT INTO ordini (stato, id_user) VALUES ('pagato', :user_id)");
    // Se l'utente è loggato, usa il suo ID, altrimenti metti NULL
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $stmt->execute(['user_id' => $user_id]);
    $ordine_id = $pdo->lastInsertId();
    
    // Registra il pagamento
    $stmt = $pdo->prepare("INSERT INTO pagamenti (data_pagamento, metodo_pagamento, stato_transazione, riferimento_transazione, id_ordine) 
                          VALUES (NOW(), 'carta', 'completato', :riferimento, :id_ordine)");
    $stmt->execute([
        'riferimento' => $payment_intent_id,
        'id_ordine' => $ordine_id
    ]);
    
    // Svuota il carrello
    $_SESSION['cart'] = [];
    setcookie("carrello", "", time() - 3600, "/");
    
} catch (Exception $e) {
    $_SESSION['payment_error'] = $e->getMessage();
    header("Location: checkout.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Riuscito - Fiori Ribelli</title>
    <link rel="stylesheet" href="css/payment_success.css">
</head>
<body>
    <header>
        <h1>Fiori Ribelli</h1>
    </header>

    <div class="success-container">
        <div class="success-icon">✓</div>
        <h2>Pagamento completato con successo!</h2>
        <p>Grazie per il tuo ordine. La conferma è stata inviata al tuo indirizzo email.</p>
        
        <div class="order-details">
            <p class="order-id">Numero ordine: <?php echo $ordine_id; ?></p>
            <p>Metodo di pagamento: Carta di credito</p>
            <p>Stato: Pagato</p>
        </div>
        
        <p>La tua conferma d'ordine è stata inviata a: <?php echo htmlspecialchars($shipping['email']); ?></p>
        
        <a href="index.php" class="continue-button">Torna alla home</a>
    </div>

    <footer>
        <p>&copy; 2025 Fiori Ribelli - Tutti i diritti riservati</p>
    </footer>
</body>
</html>

<?php
session_start();
require_once('vendor/autoload.php'); // Includi la libreria Stripe PHP

// Verifica che ci siano prodotti nel carrello e dati di spedizione
if (!isset($_SESSION['cart']) || empty($_SESSION['cart']) || !isset($_SESSION['shipping'])) {
    header("Location: checkout.php");
    exit;
}

// Calcola il totale
$totale_prezzo = 0;
foreach ($_SESSION['cart'] as $item) {
    $totale_prezzo += $item['price'] * $item['quantity'];
}

// Configurazione Stripe API
\Stripe\Stripe::setApiKey('sk_test_51RQR0lRrDuxDKDzorVsUY8EDHiTXoCFatWVKBN2dkOPtyjYuBDcssan4NVC2s1N8YNogpdFMDcdnENtG6npWdlnH00gTiLyBz4'); // Chiave privata per collegarsi a Stripe

// Ottieni i dati di spedizione
$shipping = $_SESSION['shipping'];

try {
    // Crea un intento di pagamento con Stripe
    $intent = \Stripe\PaymentIntent::create([
        'amount' => $totale_prezzo * 100, // Stripe richiede l'importo in centesimi
        'currency' => 'eur',
        'description' => 'Ordine Fiori Ribelli',
        'metadata' => [
            'customer_email' => $shipping['email'],
            'shipping_address' => json_encode([
                'name' => $shipping['nome'],
                'address' => $shipping['indirizzo'],
                'city' => $shipping['citta'],
                'postal_code' => $shipping['cap'],
                'country' => 'IT'
            ])
        ]
    ]);
} catch (Exception $e) {
    // Gestisci l'errore
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
    <title>Pagamento - Fiori Ribelli</title>
    <script src="https://js.stripe.com/v3/"></script>
    <link rel="stylesheet" href="css/payment_process.css">
</head>
<body>
    <header>
        <h1>Fiori Ribelli</h1>
        <a href="checkout.php" style="color: #27ae60; text-decoration: none;">Torna al checkout</a>
    </header>

    <div class="payment-container">
        <h2>Pagamento</h2>
        
        <div class="order-summary">
            <p>Importo totale:</p>
            <div class="order-total">€<?php echo number_format($totale_prezzo, 2); ?></div>
        </div>
        
        <form id="payment-form">
            <div class="form-row">
                <label for="card-element">Carta di credito o debito</label>
                <div id="card-element"></div>
                <div id="card-errors" role="alert"></div>
            </div>
            
            <button id="submit-button" class="pay-button">
                <div class="spinner" id="spinner"></div>
                <span id="button-text">Paga ora</span>
            </button>
        </form>
    </div>

    <footer>
        <p>&copy; 2025 Fiori Ribelli - Tutti i diritti riservati</p>
    </footer>

    <script>
        // Crea un'istanza di Stripe
        var stripe = Stripe('pk_test_51RQR0lRrDuxDKDzoY7m6vFyrv7G60I2pudq7Coc0q9qzEdMj0unlcNYYm7bNoBOBD2cVwwDrx6u6Rp0no07IuPf5006TQ4u3nW'); // chiave API pubblica
        var elements = stripe.elements();
        
        // Stile per il form di pagamento
        var style = {
            base: {
                color: '#32325d',
                fontFamily: 'Arial, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#e74c3c',
                iconColor: '#e74c3c'
            }
        };
        
        // Crea un elemento per la carta di credito
        var card = elements.create('card', {style: style});
        card.mount('#card-element');
        
        // Gestisci gli errori di validazione
        card.addEventListener('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
        
        // Gestisci l'invio del form
        var form = document.getElementById('payment-form');
        var submitButton = document.getElementById('submit-button');
        var spinner = document.getElementById('spinner');
        var buttonText = document.getElementById('button-text');
        
        form.addEventListener('submit', function(event) {
            event.preventDefault();// utilizzo questa funzione per gestire il pagamento in modo asincrono (non facendo ricaricare la pagina)
            
            // Disabilita il pulsante e mostra lo spinner
            submitButton.disabled = true;
            spinner.style.display = 'inline-block';
            buttonText.textContent = 'Elaborazione...';
            
            stripe.confirmCardPayment('<?php echo $intent->client_secret; ?>', {
                payment_method: {
                    card: card
                }
            }).then(function(result) {
                if (result.error) {
                    // Mostra l'errore
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                    
                    // Riabilita il pulsante
                    submitButton.disabled = false;
                    spinner.style.display = 'none';
                    buttonText.textContent = 'Paga ora';
                } else {
                    // Pagamento completato con successo, reindirizza alla pagina di conferma
                    window.location.href = 'payment_success.php?payment_intent=' + result.paymentIntent.id;
                }
            });
        });
    </script>
</body>
</html>

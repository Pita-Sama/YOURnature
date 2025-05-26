<?php
require_once 'collegamento_db.php';
require_once 'product/getProduct.php';
require_once 'product/setProduct.php';

if (isset($_GET['name'])) {
    
    $nome_prodotto = $_GET["name"];
    $pdo = pdoDB();

    if (isset($_COOKIE['items'])) {
        $cookie_data = stripslashes($_COOKIE['items']);
        $cart_data = json_decode($cookie_data, true);
    } else {
        $cart_data = array();
    }

    $item_id_list = array_column($cart_data, 'name');

    if (in_array($nome_prodotto, $item_id_list)) {
        foreach ($cart_data as $id => $values) {
            if ($cart_data[$id]['name'] == $nome_prodotto) {
                $cart_data[$id]['quantita'] += 1;
            }
        }
    } else {
        $prodotto_stmt = getProduct($pdo, $nome_prodotto);

        if ($prodotto_stmt !== null && $prodotto_stmt->rowCount() > 0) {
            $prodotto = $prodotto_stmt->fetch();
            $cart_data[] = setProduct($prodotto['id'],$prodotto['nome'],$prodotto['prezzo']);
        }
    }

    $json = json_encode($cart_data, true);
    setcookie('items', $json, time() + (86400 * 30), "/");
}
?>



<?php

function getProduct($pdo, $nome_prodotto) {
    $sql = "SELECT * FROM prodotti WHERE nome = :nome";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":nome", $nome_prodotto);
    $stmt->execute();
    return $stmt;
}

?>

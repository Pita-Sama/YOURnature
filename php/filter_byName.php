<?php
    require_once ('collegamento_db.php');
	require_once ('product/getTableProduct.php');
    
    function estrazioneProdotti($nome){
        $pdo = pdoDb();
        $nome = $_POST[$nome]; //lettere digitate dalla barra 
        $sql = "SELECT *
                FROM prodotti
                WHERE nome LIKE :nome";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nome' => $nome . '%']);
        $pdo = null;
        return $stmt;
    }

    

    if(isset($_POST["name"])){
        try {
            $prodotti = estrazioneProdotti("name");
            $tabella = getTableProduct($prodotti);
            echo $tabella;
        } catch (PDOException $e) {
            echo "Errore nel database: " . $e->getMessage();
        }
    }
    
?>

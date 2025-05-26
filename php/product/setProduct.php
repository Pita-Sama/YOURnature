<?php

function setProduct($id,$nome,$prezzo){
    $prodotto_array = array(
        "id" => $id,
        "name" => $nome,
        "prezzo" => $prezzo,
        "quantita" => 1
    );

    return $prodotto_array;
}

?>
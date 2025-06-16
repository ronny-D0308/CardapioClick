<?php
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 error_reporting(E_ALL);


function alertPHP($parametro) {
    // Escapa corretamente o parâmetro para uso seguro em JavaScript
    $jsSafe = json_encode($parametro); // transforma em string segura
    echo "<script> alert($jsSafe); </script>";
}

function qtdLinhas($valor) {
    $valor = mysqli_num_rows($valor);
    $texto = json_encode($valor); // transforma em string segura
    echo "<script> alert($texto); </script>";
}

function exibirSQL($query, $texto) {
    echo "<br>". $texto ."<br>". $query ."<br><br>";
}

function exibirFormatado($textoFormat) {
    echo "<pre>";
    print_r($textoFormat);
    echo "</pre>";
}

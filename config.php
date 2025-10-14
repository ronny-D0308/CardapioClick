<?php
    $servername = "localhost";
    $username = "root";
    $password = "Bryan#=1608";
    $bdname = "comandadigital";

    $conn = new mysqli( $servername, $username, $password, $bdname);

    if($conn->connect_error) {
        die("Erro na conexão". $conn->connect_error);
    }

    date_default_timezone_set('America/Sao_Paulo');

    $endereco = "https://cardapioclick.byethost14.com/";


?>
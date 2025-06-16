<?php
    $Nome = $_POST['nome_cliente'];
    $Garcon = $_POST['nome_garcon'];
    $Valor = $_POST['valor'];

    include('config.php');

    $sql = "INSERT INTO comandaspendentes(comp_Cliente, comp_Garcom, comp_Valor, comp_Data ) VALUES ('$Nome', '$Garcon', '$Valor', NOW())";
            //echo $sql;
    $query = mysqli_query($conn, $sql);

    if($query) {
        echo "Envio concluido";

    }else{
        echo "Erro no envio". mysqli_error($conn);
    }

    header("location: Comandas.php");

?> 
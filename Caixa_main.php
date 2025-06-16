<?php
include 'config.php';

session_start();
if(!isset($_SESSION['usuario'])) {
    header('Location: Validacao.php');
    exit;
}
$usuario = $_SESSION['usuario'];

$dataatual = date("Y-m-d");
$hora = date("H:i:s");

// INSERIR O VALOR DO CAIXA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valor_digitado = mysqli_escape_string($conn, $_POST['ValorAbertura']);
    $valor_tratado = str_replace(['.', ','], ['', '.'], $valor_digitado);
    $ValorAbertura = floatval($valor_tratado);

    $sql = "INSERT INTO caixa (cx_ValorAbertura, cx_DataAbertura, cx_HoraAbertura) 
            VALUES($ValorAbertura, '$dataatual', '$hora')";
    $query = mysqli_query($conn, $sql);
    if ($query) {
        include 'avisoDinamico.php';
        avisoDinamico("Caixa aberto", "#01B712");
        //header("Refresh:3; url=Caixa_comandas.php");
    } else {
        include 'avisoDinamico.php';
        avisoDinamico("Erro ao abrir o caixa", "#CB0606");
    }
}

$sql_cons = "SELECT * FROM caixa WHERE cx_Fechado <> 'S' AND cx_DataAbertura = '$dataatual'";
$query_cons = mysqli_query($conn, $sql_cons);
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <meta charset="UTF-8">
    <title>.: Caixa :.</title>

    <style type="text/css">
        body {
            padding-top: 40px;
        }
        form {
            width: 400px;
        }
        form input {
            border-radius: 5px;
            border: none;
            outline: none;
            background-color: rgba(0, 0, 0, 0.4);
            color: white;
        }
        form button {
            border-radius: 5px;
            border: none;
            outline: none;
            background-color: rgba(0, 0, 0, 0.4);
            color: white;
            width: 80px
        }
    </style>

    <script>
      $(document).ready(function(){
         $('#valorAbertura').mask('#.##0,00', {reverse: true});
      });
    </script>

</head>
<body>
    <!-- VERIFICAÇÃO SE TEM CAIXA ABERTO -->
    <?php
    if(mysqli_num_rows($query_cons) > 0) { 

        include'Caixa_layout.php';

    } else { ?>
        <!-- SE NÃO TIVER CAIXA ABERTO, INICIA A ABERTURA -->
        <div style=" margin: 0 auto; width: 400px; background-color: #da6c22; padding: 10px; border-radius: 8px; text-align: center;">
            <form action="" method="POST">
                <h1> Abertura de caixa </h1>
                <input type="text" name="ValorAbertura" id="valorAbertura">
                <button type="submit" name=""> Abrir </button>
            </form>
        </div>

    <?php } ?>


</body>
</html>
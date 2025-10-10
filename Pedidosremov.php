<?php
// Desativa a exibição de erros na tela
//ini_set('display_errors', 0);
//ini_set('display_startup_errors', 0);
//error_reporting(0);

include 'config.php';
include 'funcoesPHP.php';
include 'avisoDinamico.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}
$usuario = $_SESSION['usuario'];

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql_delete = "DELETE FROM justificaremocao WHERE jrm_Id = $id";
    $query = mysqli_query($conn, $sql_delete);
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="icon" href="Cardapioclick.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> .:Entrada de Estoque:. </title>

    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Kreon:wght@300..700&display=swap');
        *{
            font-family: 'Kreon',sans-serif;
            margin: 0;
        }
        body {
            padding-top: 50px;
            background-color: #da6c22;
        }
        .conteiner {
            margin: 0 auto;
            width: 400px;
            background-color: #da6c22;
            padding: 20px;
            border-radius: 8px;
        }
        table {
            background-color: #FFF;
        }
        .botaoimprimir {
            width: 80px;
            height: 25px;
            background-color:rgb(175, 84, 23);
            border-radius: 5px;
            color: #FFF;
        }

        a {
            text-decoration: none;
            color: green;
        }
    </style>

    <script>
        function imprimirTabela() {
            var conteudo = document.getElementById("tabela").innerHTML;
            var janela = window.open("", "", "width=800,height=600");
            janela.document.write(`
                <html>
                    <head>
                        <title>Imprimir Tabela</title>
                        <style>
                            table {
                                width: 100%;
                                border-collapse: collapse;
                            }
                            th, td {
                                border: 1px solid black;
                                padding: 8px;
                                text-align: center;
                            }
                        </style>
                    </head>
                    <body>
                        ${conteudo}
                    </body>
                </html>
            `);
            janela.document.close(); // fecha o stream do documento
            janela.print();
        }
    </script>
</head>

</head>
<body>

    <a class="sair" style="text-decoration:none; position: absolute; top: 1%; left: 2%;" href="Central_adm.php"> <img src="imagens/left.png" width="40px" > </a>

    <?php
        $sql_geral = "SELECT *
                      FROM justificaremocao 
                      ";
        $query_geral = mysqli_query($conn, $sql_geral);
        
        if (!$query_geral) {
            echo "Erro ao buscar informações";
        } else {
    ?>
        <div id="tabela" style="max-width: 100%; overflow-x: auto; display: flex; justify-content: center; margin-top: 50px;">
            <table width="50%" align="center" border="1" cellspacing="0" cellpadding="5">
                <thead>
                    <tr>
                        <th> Dia </th>
                        <!-- <th> Quantidade </th> -->
                        <th> Item </th>
                        <th> Justificativa </th>
                    </tr>
                </thead>
                <tbody align="center">
                    <?php while($linhas = mysqli_fetch_object($query_geral)) {
                    
                        echo "<tr data-id='" . $linhas->jrm_Id . "'>";
                        echo "<td>" . date('d-m-Y H:i:s', strtotime($linhas->jrm_Datahora)) . "</td>";
                        //echo "<td>{$linhas->jrm_Quantidade}</td>";
                        echo "<td>{$linhas->jrm_Item}</td>";
                        echo "<td>{$linhas->jrm_Justificativa}</td>";
                        echo "<td> <a href='Pedidosremov.php?id=$linhas->jrm_Id'> Justificado </a> </td>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
                
        <!-- BOTÃO IMPRIMIR -->
        <div style="width: 100%; text-align: center; margin-top: 10px">
            <input type="button" class="botaoimprimir" value="Imprimir" onclick="imprimirTabela()">
        </div>
    <?php } ?>
</body>
</html>
</body>
</html>
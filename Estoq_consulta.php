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
        }
        .conteiner {
            margin: 0 auto;
            width: 400px;
            background-color: #da6c22;
            padding: 20px;
            border-radius: 8px;
        }
        form {
            width: 380px;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px 30px;
        }
        form span {
            display: flex;
            flex-direction: column;

        }
        form input {
            border-radius: 5px;
            border: none;
            outline: none;
            background-color: rgba(0, 0, 0, 0.4);
            color: white;
        }
        form select {
            border-radius: 5px;
            border: none;
            outline: none;
            background-color: rgba(0, 0, 0, 0.4);
            color: white;
        }
        #quantidade {
            width: 40px;
            text-align:center;
        }
        .botao {
            width: 150px;
            height: 30px;
            cursor: pointer;
        }
        h1 {
            text-align: center;
            color: white;
            margin-bottom: 10px;
        }
        #BotaobuscarProd {
            width: 70px;
            height: 20px;
            color: black;
            cursor: pointer;
        }
        .InputbuscarProd {
            width: 120px;
            height: 20px;
            color: black;
            cursor: pointer;
        }
        .botaoimprimir {
            width: 80px;
            height: 25px;
            background-color: #da6c22;
            border-radius: 5px;
        }
    </style>

    <script>
        function buscarProd() {
            const busca = document.getElementById("buscaProd").value;
            const url = window.location.pathname + "?buscaProd=" + encodeURIComponent(busca);
            window.location.href = url;
        }
        
        function entradaProd(botao) {
            const linha = botao.closest("tr");
            const id = linha.getAttribute("data-id");
        
            // Redireciona para a mesma página com o ID na URL
            window.location.href = window.location.pathname + "?entrada=S&id=" + encodeURIComponent(id);
        }

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

    <a class="sair" style="text-decoration:none; position: absolute; top: 1%; left: 2%;" href="Estoque_main.php"> <img src="imagens/left.png" width="40px" > </a>

<div style="margin-bottom:10%; width: 100%; display: flex; justify-content: center;">
    <form method="POST">
        <h2>Pesquise pelo produto aqui!</h2>
        <input type="text" class="InputbuscarProd" name="InputbuscarProd">
        <input type="submit" value="Buscar">
    </form>
</div>


<?php if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if(empty($_POST['InputbuscarProd'])) {
        $condicao = '';
    } else {
        $InputbuscarProd = $_POST['InputbuscarProd'];
        $condicao = "WHERE etq_Nome LIKE '". $InputbuscarProd ."%'";
    }


    $sql_geral = "SELECT 
                      etq.etq_Id,
                      etq.etq_Nome,
                      etq.etq_Categoria,
                      /*etq.etq_Fornecedor,
                      etq.etq_Unidade,*/
                      SUM(rom.rom_Quantidade) AS total_quantidade
                  FROM estoque etq 
                  INNER JOIN romaneio rom ON etq.etq_Id = rom.rom_Idproduto
                  ". $condicao ."
                  GROUP BY 
                      etq.etq_Id,
                      etq.etq_Nome,
                      etq.etq_Categoria
                      /*etq.etq_Fornecedor,
                      etq.etq_Unidade*/
                  ";
        //echo $sql_geral;
    $query_geral = mysqli_query($conn, $sql_geral);

    if (!$query_geral) {
        echo "Erro ao buscar informações";
    } else {
?>
    <div id="tabela" style="max-width: 100%; overflow-x: auto; display: flex; justify-content: center; margin-top: 50px;">
        <table width="50%" align="center" border="1" cellspacing="0" cellpadding="5">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <!-- <th>Preço</th>
                    <th>Fornecedor</th> -->
                    <th>Quantidade</th>
                </tr>
            </thead>
            <tbody align="center">
                <?php while($linhas = mysqli_fetch_object($query_geral)) {

                    $cat = strtolower($linhas->etq_Categoria);

                    // Ajustar quantidade para carnes e porções (de gramas para Kg)
                    if ($cat == "carnes" || $cat == "porcoes" || $cat == "destilados") {
                        $Qtd = number_format($linhas->total_quantidade / 1000, 2, ',', '.');
                    } else {
                        $Qtd = $linhas->total_quantidade;
                    }
                    // Unidade conforme categoria
                    switch ($cat) {
                        case 'bebidas':
                        case 'bebida':
                            $Unid = "Unidades";
                            break;
                    
                        case 'porcoes':
                            $Unid = "Pct";
                            break;
                    
                        case 'carnes':
                            $Unid = "Kg";
                            break;
                    
                        case 'destilados':
                            $Unid = "L";
                            break; // ← Faltava este break!
                    
                        default:
                            $Unid = "";
                            break;
                    }
                    echo "<tr data-id='{$linhas->etq_Id}'>";
                    echo "<td>{$linhas->etq_Nome}</td>";
                    echo "<td>{$linhas->etq_Categoria}</td>";
                    echo "<td>{$Qtd} {$Unid}</td>";
                    echo "</tr>";
                } ?>

            </tbody>
        </table>
    </div>
    
    <!-- BOTÃO IMPRIMIR -->
    <div style="width: 100%; text-align: center; margin-top: 10px">
        <input type="button" class="botaoimprimir" value="Imprimir" onclick="imprimirTabela()">
    </div>
<?php } } ?>
</body>
</html>
</body>
</html>
<?php
include 'config.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$usuario = $_SESSION['usuario'];

// Corrigindo o SQL (sem *)
$sql_del = "DELETE FROM romaneio
            WHERE rom_Quantidade = 0
              AND rom_Idproduto IN (
                  SELECT rom_Idproduto
                  FROM (
                      SELECT rom_Idproduto
                      FROM romaneio
                      WHERE rom_Quantidade > 0
                      GROUP BY rom_Idproduto
                  ) AS produtos_com_estoque
              )";

// Executa a query
$query = mysqli_query($conn, $sql_del);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Cardapioclick.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- ESSENCIAL -->
    <title>.: Estoque :.</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kreon:wght@300..700&display=swap');
        *{
            font-family: 'Kreon',sans-serif;
            margin: 0;
        }
        body{
            background-color:#da6c22;
            padding: 20px;
        }
        nav {
            width: 100%;
            height: 40px; 
        }
        .navBar {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
            margin: 0 auto;     /* centraliza horizontalmente */
            box-sizing: border-box;
        }
        .navBar li {
            font-size: 20px;
            list-style-type: none;
            width: auto;
            height: auto;
            cursor: pointer;
        }
        .links{
            text-decoration: none;
            color: black;
        }
        .conteiner-relatorio {
            margin-top: 10%;
            display: flex;
            flex-direction: row;
            gap: 10px 20px;
            flex-wrap: wrap;
        }
        .card {
            width: 200px; 
            height: auto;
            background-color: rgba(0, 0, 0, 0.6);
            border-radius: 10px;
            color: white;
            text-align:center;
            padding: 10px;
        }
        a {
            text-decoration: none;
        }

        @media print {
            body * {
                visibility: hidden;
            }
        }

        @media (max-width: 800px),
               (max-width: 600px),
               (max-width: 400px){

          .navBar {
            grid-template-columns: repeat(1, 2fr);
            grid-template-rows: repeat(4, 2fr);
            justify-content: start;
          }

          .navBar ul {
            margin-left: 0;
          }
          .navBar li {
            font-size: 15px;
          }

          .conteiner-relatorio {
            margin-top: 40%;
          }
          .card {
            width: 150px; 
            height: auto;
            background-color: rgba(0, 0, 0, 0.6);
            border-radius: 10px;
            color: white;
            text-align:center;
            padding: 5px;
          }
        }
    </style>
</head>
<body>
    <a class="sair" style="text-decoration:none; " href="Central_adm.php"> <img src="imagens/left.png" width="40px" > </a>


    <!-- MENU | NAVBAR DA PAGINA -->
    <nav>
        <ul class="navBar">
            <li> <a class="links" href="Estoq_cadprod.php" > 
                    <img src="imagens/register.png" width="20px">
                    Cadastrar Produto </a> </li>

            <li> <a class="links" href="Estoq_editprod.php" > 
                    <img src="imagens/edit.png" width="20px">
                    Editar Produto </a> </li>
            <li> <a class="links" href="Estoq_removprod.php" > 
                    <img src="imagens/remove.png" width="20px">
                    Desativar Produto </a> </li>

            <li> <a class="links" href="Estoq_entradaProd.php" > 
                    <img src="imagens/enter.png" width="20px">
                    Entrada de Estoque </a> </li>
            <li> <a class="links" href="Estoq_consulta.php" > 
                    <img src="imagens/lupa.png" width="20px">
                    Consulta de Estoque </a> </li>
        </ul>
    </nav>

    <!-- BOAS VINDAS AO USUARIO (OPCIONAL) -->
    <!-- <h1> Bem vindo ao estoque ?php echo $usuario ?> </h1> -->

    <!-- RELATORIOS DO ESTOQUE PARA ATUALIZAÇÃO (ESTOQUE EM FALTA, PERTO DE ACABAR) -->
    <div class="conteiner-relatorio">

        <?php
            $sql_geral = "SELECT rom_Datavenci, rom_Idproduto, SUM(rom_Quantidade) AS rom_Quantidade
                          FROM romaneio
                          GROUP BY rom_Idproduto;
                          ";
            $query_geral = mysqli_query($conn, $sql_geral);

            while($linhas = mysqli_fetch_object($query_geral)) {

                    $dataHoje = new DateTime(); // hoje
                    $dataLimite = new DateTime(); // hoje + 7 dias
                    $dataLimite->modify('+7 days');
                    $dataVencimento = new DateTime($linhas->rom_Datavenci);

                    if ($dataHoje > $dataVencimento) {
                        $sql = "SELECT etq_Nome FROM estoque WHERE etq_Id = ". $linhas->rom_Idproduto ."";
                        $query = mysqli_query($conn, $sql);
                        $linhasProd = mysqli_fetch_object($query);
                        echo "<div class='card'>";
                            echo "<h3> O produto (<font size='3px'>". $linhasProd->etq_Nome."</font>) já vencido </h3>";
                        echo "</div>";
                    }

                    if ($dataVencimento <= $dataLimite) {
                        $sql = "SELECT etq_Nome FROM estoque WHERE etq_Id = ". $linhas->rom_Idproduto ."";
                        $query = mysqli_query($conn, $sql);
                        $linhasProd = mysqli_fetch_object($query);
                        echo "<div class='card'>";
                            echo "<h3> O produto (<font size='3px'>". $linhasProd->etq_Nome."</font>) vence em breve </h3> <p>Próximos 7 dias</p>";
                        echo "</div>";
                    }

                    if ($linhas->rom_Quantidade <= 3) {
                        $sql = "SELECT * FROM estoque WHERE etq_Id = ". $linhas->rom_Idproduto ."";
                        $query = mysqli_query($conn, $sql);
                        $linhasProd = mysqli_fetch_object($query);
                        echo "<a href='Estoq_entradaProd.php?entrada=S&id=". $linhasProd->etq_Id ."&categoria=". $linhasProd->etq_Categoria ."'>";
                            echo "<div class='card'>";
                                echo "<h3> O produto (<font size='3px'>". $linhasProd->etq_Nome."</font>) está com estoque baixo </h3><p>". $linhas->rom_Quantidade ."</p>";
                            echo "</div>";
                        echo "</a>";
                    }
            }
        ?>
    </div>

</body>
</html>

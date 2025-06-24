<?php
    include 'config.php';
    include 'funcoesPHP.php';
    include 'avisoDinamico.php';
    
    session_start();
    if(!isset($_SESSION['usuario'])) {
        header('Location: Validacao.php');
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
    <title> .:Remover Produto:. </title>

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

        form input {
            border-radius: 5px;
            border: none;
            outline: none;
            background-color: rgba(0, 0, 0, 0.4);
            color: white;
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
        .botaoTabela {
            cursor: pointer;
        }
    </style>

    <script>
        // FUNÇÃO JS QUE COLOCA O VALOR DO INPUT DENTO DA URL E SUBMITA
        function buscarProd() {
            var busca = document.getElementById("buscaProd").value;
            var url = window.location.pathname + "?buscaProd=" + encodeURIComponent(busca);
            window.location.href = url;
        }

        function excluirProd(botao) {
            const linha = botao.closest("tr");
            const id = linha.getAttribute("data-id");
        
            if (confirm("Tem certeza que deseja excluir este produto?")) {
                // Cria um formulário oculto
                const form = document.createElement("form");
                form.method = "POST";
                form.action = "";
        
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "excluirId";
                input.value = id;
        
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</head>
<body>
    <a class="sair" style="text-decoration:none; position: absolute; top: 1%; left: 2%;" href="Estoque_main.php"> <img src="imagens/left.png" width="40px" > </a>

    <div class="conteiner">
        <h1> Remover produto do estoque </h1>
    </div>

    <!-- TABELA DE REGISTROS -->

    <?php
        // CONSULTA PARA GERAR OS REGISTROS DENTRO DO BANCO
        // SE TIVER PRODUTO DE BUSCA

        if (isset($_GET['buscaProd'])) {
            $buscaProd = $_GET['buscaProd'];
            $sql_geral = "SELECT * FROM estoque WHERE etq_Nome LIKE '$buscaProd%' AND etq_Ativo <> 'N'";
        } else {
            $sql_geral = "SELECT * FROM estoque WHERE etq_Ativo <> 'N'";
        }
        $query_geral = mysqli_query($conn, $sql_geral);
        
        if (!$query_geral) {
            echo "Erro ao buscar informações";
        }
    ?>

    <div style="margin-top: 10%; width: 100%; display: flex; flex-direction: row; justify-content: center;">
        <form id="formBusca" onsubmit="buscarProd(); return false;">
            <h2> Pesquise pelo produto aqui! </h2>
            <input type="text" class="InputbuscarProd" id="buscaProd">
            <input type="button" id="BotaobuscarProd" value="Buscar" onclick="buscarProd()">
        </form>
    </div>

    <div style="max-width: 100%; overflow-x: auto; display: flex; justify-content: center; margin-top: 50px;">
        <table width="50%" align="center" border="0" cellspacing="0" cellpadding="5">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <!-- <th>Quantidade</th> -->
                    <!-- <th>Fornecedor</th>
                    <th>Data de entrada</th>
                    <th>Data de vencimento</th> -->
                    <th></th>
                </tr>
            </thead>

            <tbody align="center" border="0" cellspacing="0" cellpadding="5">
                <?php
                    while($linhas = mysqli_fetch_object($query_geral)) {
                        echo "<tr data-id='". $linhas->etq_Id ."'>";
                            echo "<td>". $linhas->etq_Nome ."</td>";
                            echo "<td>". $linhas->etq_Categoria ."</td>";
                            //echo "<td>". $linhas->etq_Quantidade ."</td>";
                            //echo "<td>". $linhas->etq_Fornecedor ."</td>";
                            //echo "<td>". muda_data_pt($linhas->etq_Dataentrada) ."</td>";
                            //echo "<td>". muda_data_pt($linhas->etq_Datavenci) ."</td>";
                            echo "<td> 
                                    <img onclick='excluirProd(this)' class='botaoTabela' src='imagens/remove.png' width='20px' alt='Editar'>
                                  </td>";
                        echo "</tr>";
                    }

                ?>
            </tbody>
        </table>
    </div>
    <br><br>


    <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluirId'])) {
            $idExcluir = intval($_POST['excluirId']);
        
            $sql_delete = "UPDATE estoque SET etq_Ativo = 'N' WHERE etq_Id = $idExcluir";
            $query_delete = mysqli_query($conn, $sql_delete);
        
            if ($query_delete) {
                avisoDinamico("Produto excluído com sucesso!", "#18B308");
            } else {
                avisoDinamico("Erro ao tentar excluir o produto.", "#CF1414");
            }
        }

    ?>

</body>
</html>
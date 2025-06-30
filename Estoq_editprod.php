<?php
    // Desativa a exibição de erros na tela
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);

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
    <title> .:Edição de Produto:. </title>

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
            justify-content: center;
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


        function alterarQuantidade(valor) {
            const input = document.getElementById('quantidade');
            let atual = parseInt(input.value) || 0;
    
            atual += valor;
    
            if (atual < 0) atual = 0; // Impede números negativos
    
            input.value = atual;
        }

        function formatarDataParaInput(dataBR) {
            const [dia, mes, ano] = dataBR.split('/');
            return `${ano}-${mes.padStart(2, '0')}-${dia.padStart(2, '0')}`;
        }


        function editarValor(botao) {
            const foralinha = document.getElementById("foralinha");
            foralinha.style.display = 'flex';
            // Encontra a linha (tr) do botão clicado
            const linha = botao.closest("tr");
        
            // Coleta todas as células (td), exceto o botão
            const celulas = linha.querySelectorAll("td");

            //const valores = celulas[2].textContent.trim();

            document.getElementById("NomeProd").value = celulas[0].textContent.trim();
            document.getElementById("categoria").value = celulas[1].textContent.trim();

            //const dataBR = celulas[6].textContent.trim(); // Ex: "31/05/2025"
            //const dataFormatada = formatarDataParaInput(dataBR);
            
            //document.getElementById("Datavenci").value = dataFormatada;

            const id = linha.getAttribute("data-id");
            document.getElementById("etqId").value = id;
        }
    </script>
</head>
<body>
    <a class="sair" style="text-decoration:none; position: absolute; top: 1%; left: 2%;" href="Estoque_main.php"> <img src="imagens/left.png" width="40px" > </a>

    <div class="conteiner">
        <h1> Editar Produto </h1>
        <form action="" method="POST">
            <input type="hidden" name="etqId" id="etqId" value="">
            <span>
                <label for="NomeProd">Nome:</label>
                <input type="text" name="NomeProd" id="NomeProd" value="">
            </span>
            
            <span>
                <label for="categoria">Categoria:</label>
                <select name="categoria" id="categoria">
                    <option value="Bebidas" > Bebida </option>
                    <option value="Pereciveis" > Pereciveis </option>
                    <option value="Limpeza" > Produtos de limpeza </option>
                    <option value="Carnes" > Carnes </option>
                    <option value="Embalagens" > Embalagens </option>
                    <option value="Bomboniere" > Bomboniere </option>
                </select>
            </span>

            <span style="display: none;" id="foralinha">
                <label for="Ativar_desativar">Ativar / Desativar:</label>
                <select name="Ativar_desativar" id="Ativar_desativar">
                    <option value="S" > Ativar </option>
                    <option value="N" > Desativar </option>
                </select>
            </span>


            <div style="text-align: center; width: 100%">
                <input class="botao" type="submit" name="" value="Editar Produto">
            </div>
        </form>
    </div>

    <!-- TABELA DE REGISTROS -->

    <?php
        // CONSULTA PARA GERAR OS REGISTROS DENTRO DO BANCO
        // SE TIVER PRODUTO DE BUSCA

        if (isset($_GET['buscaProd'])) {
            $buscaProd = $_GET['buscaProd'];
            $sql_geral = "SELECT * FROM estoque WHERE etq_Nome LIKE '$buscaProd%'";
        } else {
            $sql_geral = "SELECT * FROM estoque";
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
        <table width="30%" align="center" border="0" cellspacing="0" cellpadding="5">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <!-- <th>Quantidade</th> -->
                    <!-- <th>Preço</th>
                    <th>Fornecedor</th>
                    <th>Data de entrada</th>
                    <th>Data de vencimento</th> -->
                    <th><img src="imagens/edit.png" width="20px" alt="Editar"></th>
                </tr>
            </thead>

            <tbody align="center" border="0" cellspacing="0" cellpadding="5">
                <?php
                    while($linhas = mysqli_fetch_object($query_geral)) {
                        echo "<tr data-id='". $linhas->etq_Id ."'>";
                            echo "<td>". $linhas->etq_Nome ."</td>";
                            echo "<td>". $linhas->etq_Categoria ."</td>";
                            //echo "<td>". $linhas->etq_Quantidade ."</td>";
                            //echo "<td>". number_format($linhas->etq_Preco, 2,',','.') ."</td>";
                            //echo "<td>". $linhas->etq_Fornecedor ."</td>";
                            //echo "<td>". muda_data_pt($linhas->etq_Dataentrada) ."</td>";
                            //echo "<td>". muda_data_pt($linhas->etq_Datavenci) ."</td>";
                            echo "<td> 
                                    <img onclick='editarValor(this)' class='botaoTabela' src='imagens/edit.png' width='20px' alt='Editar'>
                                  </td>";
                        echo "</tr>";
                    }

                ?>
            </tbody>
        </table>
    </div>
    <br><br>


    <?php
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            // RECEBENDO OS VALORES DOS INPUTS
            $etqId = $_POST['etqId'];
            $NomeProd = $_POST['NomeProd'];
            $categoria = $_POST['categoria'];
            $Ativar_desativar = $_POST['Ativar_desativar'];

            if (!empty($NomeProd)) {
                $sql_insert = "UPDATE estoque SET etq_Nome = '$NomeProd', etq_Categoria = '$categoria', etq_Ativo = '$Ativar_desativar' WHERE etq_Id = $etqId";
                    //echo $sql_insert;
                $query_insert = mysqli_query($conn, $sql_insert);

                if (!$query_insert) {
                    avisoDinamico("Erro ao tentar atualizar o produto", "#CF1414");
                } else {
                    avisoDinamico("Produto atualizado com sucesso!!", "#18B308");
                }
            } else {
                avisoDinamico("O nome do produto não pode estar vazio!", "#A9AB06");
            }
        }
    ?>

</body>
</html>
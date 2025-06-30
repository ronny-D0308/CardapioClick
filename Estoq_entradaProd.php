<?php
// Desativa a exibição de erros na tela
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

include 'config.php';
include 'funcoesPHP.php';
include 'avisoDinamico.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: Validacao.php');
    exit;
}
$usuario = $_SESSION['usuario'];

// TRATAMENTO DO POST (Atualizar produto)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['IdProd'])) {

    $IdProd = intval($_POST['IdProd']);
    $categoria = $_POST['categoria'];
    $quantidade = $_POST['quantidade'];
    $Datavenci = $_POST['Datavenci'];
    $Preco = $_POST['preco'];

    //$sql_prod = "SELECT * FROM romaneio WHERE rom_Idproduto = $IdProd";
    //$query_prod = mysqli_query($conn, $sql_prod);
    //$linha = mysqli_fetch_object($query_prod);
    //$QuantidadeBD = $linha->rom_Qtdunidade;


    // INICIA AS VERIFICAÇÕES PARA INSERÇÃO DE VALORES NO ESTOQUE
    if ($categoria == "Carnes") {
        $valor = $Preco;
        $quantidade = $quantidade * 1000;
    }
    else if ($categoria == "Bebidas") {
        $valor = $Preco;
        $quantidade = $quantidade;
    } 
    else {
        $valor = $Preco;
        $quantidade = $quantidade;
    }


    $sql_insert = "INSERT INTO romaneio (rom_Idproduto, rom_Dataentrada, rom_Datavenci, rom_Quantidade, rom_Preco)
                    VALUES ('$IdProd', NOW(), '$Datavenci', $quantidade, $valor)";
        //echo $sql_insert;
    $query_insert = mysqli_query($conn, $sql_insert) or die(mysqli_error($conn));

    if (!$query_insert) {
        avisoDinamico("Erro ao tentar atualizar o produto", "#CF1414");
    } else {
        avisoDinamico("Produto atualizado com sucesso!!", "#18B308");
        header('Location: Estoq_entradaProd.php');
    }
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
            width: 60px;
            text-align:center;
        }

        #preco {
            width: 80px;
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
    function buscarProd() {
        const busca = document.getElementById("buscaProd").value;
        const url = window.location.pathname + "?buscaProd=" + encodeURIComponent(busca);
        window.location.href = url;
    }
    
    function alterarQuantidade(valor) {
        const input = document.getElementById('quantidade');
        let atual = parseInt(input.value) || 0;
        atual += valor;
        if (atual < 0) atual = 0;
        input.value = atual;
    }
    
    function formatarDataParaInput(dataBR) {
        const [dia, mes, ano] = dataBR.split('/');
        return `${ano}-${mes.padStart(2, '0')}-${dia.padStart(2, '0')}`;
    }
    
    function entradaProd(botao) {

        const linhaSelecionada = botao.closest("tr");
        const id = linhaSelecionada.getAttribute("data-id");

        const colunas = linhaSelecionada.querySelectorAll("td");
        const categoria = colunas[1].innerText;
    
        // Redireciona para a mesma página com o ID na URL
        window.location.href = window.location.pathname + "?entrada=S&id=" + encodeURIComponent(id) + "&categoria=" + encodeURIComponent(categoria);
    }
        
    </script>
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
    $InputbuscarProd = $_POST['InputbuscarProd'];
    $sql_geral = "SELECT * FROM estoque WHERE etq_Nome LIKE '$InputbuscarProd%'";
    $query_geral = mysqli_query($conn, $sql_geral);

    if (!$query_geral) {
        echo "Erro ao buscar informações";
    } else {
?>
    <div style="max-width: 100%; overflow-x: auto; display: flex; justify-content: center; margin-top: 50px;">
        <table width="30%" align="center" border="0" cellspacing="0" cellpadding="5">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <!-- <th>Preço</th> 
                    <th>Unidade</th>-->
                    <th><img src="imagens/enter.png" width="20px" alt="Editar"></th>
                </tr>
            </thead>
            <tbody align="center">
                <?php while($linhas = mysqli_fetch_object($query_geral)) {
                    echo "<tr data-id='" . $linhas->etq_Id . "'>";
                    echo "<td>{$linhas->etq_Nome}</td>";
                    echo "<td>{$linhas->etq_Categoria}</td>";
                    //echo "<td>{$linhas->etq_Preco}</td>";
                    //echo "<td>{$linhas->etq_Unidade}</td>";
                    echo "<td><img onclick='entradaProd(this)' class='botaoTabela' src='imagens/enter.png' width='20px' alt='Editar'></td>";
                    echo "</tr>";
                } ?>
            </tbody>
        </table>
    </div>
<?php } } ?>


<?php 
    // Verifica se veio um ID via GET e carrega o produto
    if (isset($_GET['entrada']) && $_GET['entrada'] === 'S' && isset($_GET['id']) && isset($_GET['categoria'])) {

        $id = intval($_GET['id']);
        $categoria = $_GET['categoria'];

        $sql_produto = "SELECT * FROM romaneio WHERE rom_Id = $id";
        $res_produto = mysqli_query($conn, $sql_produto);
        $dados = mysqli_fetch_assoc($res_produto);
 ?>
    <div class="conteiner">
        <h1>Entrada de estoque</h1>
        <form method="POST" action="">

            <input type="hidden" name="IdProd" id="IdProd" value="<?=$id?>">
            <input type="hidden" name="categoria" id="categoria" value="<?=$categoria?>">

            <span>
                <input type="hidden" name="romId" value="<?=$dados['rom_Id']?>" id="romId">
            </span>
            <!--
            <span>
                <label for="categoria">Categoria:</label>
                <select name="categoria" id="categoria" value="<?=$dados['rom_Categoria']?>">
                    <option value="bedida">Bebida</option>
                    <option value="periciveis">Perecíveis</option>
                    <option value="limpeza">Produtos de limpeza</option>
                    <option value="embalagens">Embalagens</option>
                    <option value="bomboniere">Bomboniere</option>
                </select>
            </span>
            <span>
                <label for="unidade">Unidade:</label>
                <select name="unidade" id="unidade" value="<?=$dados['rom_Unidade']?>">
                    <option value="Kg">Kg</option>
                    <option value="L">Litro</option>
                    <option value="Und">Unidade</option>
                    <option value="Pct">Pacote</option>
                </select>
            </span> -->
            <span>
                <label for="quantidade">Quantidade:</label>
                <div style="display: flex; align-items: center; gap: 10px">
                    <button type="button" onclick="alterarQuantidade(-1)">−</button>
                    <input type="text" name="quantidade" id="quantidade" value="<?=$dados['rom_Quantidade']?>">
                    <button type="button" onclick="alterarQuantidade(1)">+</button>
                </div>
            </span>

            <span>
                <label for="preco">Preço:</label>
                <input type="text" name="preco" id="preco" value="<?= number_format($dados['rom_Preco'], 2,',','.')?>">
            </span>

            <span>
                <label for="Datavenci">Data de Validade:</label>
                <input type="date" name="Datavenci" id="Datavenci" value="<?=$dados['rom_Datavenci']?>">
            </span>

            <div style="text-align: center; width: 100%">
                <input class="botao" type="submit" value="Adicionar ao estoque">
            </div>
        </form>
    </div>
<?php } ?>
</body>
</html>
</body>
</html>
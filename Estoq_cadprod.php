<?php
include 'config.php';

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
    <title>Cadastro de Produto</title>

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
    </style>

    <script>
        function alterarQuantidade(valor) {
            const input = document.getElementById('quantidade');
            let atual = parseInt(input.value) || 0;
    
            atual += valor;
    
            if (atual < 0) atual = 0; // Impede números negativos
    
            input.value = atual;
        }
    </script>
</head>
<body>
    <a class="sair" style="text-decoration:none; position: absolute; top: 1%; left: 2%;" href="Estoque_main.php"> <img src="imagens/left.png" width="40px" > </a>

    <div class="conteiner">
        <h1> Cadastrar Produto </h1>
        <form action="" method="POST">
            <span>
                <label for="NomeProd">Nome:</label>
                <input type="text" name="NomeProd" id="NomeProd">
            </span>
            
            <span>
                <label for="categoria">Categoria:</label>
                <select name="categoria">
                    <option value="Bebidas" > Bebidas </option>
                    <option value="Porcoes" > Porções </option>
                    <option value="Carnes" > Carnes </option>
                    <option value="Bomboniere" > Bomboniere </option>
                    <!-- <option value="Limpeza" > Produtos de limpeza </option> -->
                    <!-- <option value="Embalagens" > Embalagens </option> -->
                </select>
            </span>
            
            <!--
            <span>
                <label for="unidade">Unidade:</label>
                <select name="unidade">
                    <option value="Kg" > Quilograma(Kg) </option>
                    <option value="L" > Litro(L) </option>
                    <option value="Und" > Unidade(und)</option>
                    <option value="Pct" > Pacote(Pct)</option>
                </select>
            </span>
            
            <span>
                <label for="quantidade">Quantidade:</label>
                <div style="display: flex; align-items: center; gap: 10px">
                    <button type="button" onclick="alterarQuantidade(-1)">−</button>
                    <input type="text" name="quantidade" id="quantidade" value="0" min="0">
                    <button type="button" onclick="alterarQuantidade(1)">+</button>
                </div>
            </span>

            <span>
                <label for="preco">Preço:</label>
                <input type="text" name="preco" id="preco">
            </span>
            
            <span>
                <label for="fornecedor">Fornecedor:</label>
                <input type="text" name="fornecedor" id="fornecedor">
            </span>
            
            <span>
                <label for="Dataentrada">Data de Validade:</label>
                <input type="date" name="Dataentrada" id="Dataentrada">
            </span>
            -->
            
            <!-- <span>
                <label for="fornecedor">Código:</label>
                <input type="text" name="fornecedor" id="fornecedor">
            </span> -->

            <div style="text-align: center; width: 100%">
                <input class="botao" type="submit" name="" value="Cadastrar Produto">
            </div>
        </form>
    </div>

    <?php
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            // RECEBENDO OS VALORES DOS INPUTS
            $NomeProd = $_POST['NomeProd'];
            $categoria = $_POST['categoria'];
            //$unidade = $_POST['unidade'];
            //$preco = intval($_POST['preco']);
            //$fornecedor = $_POST['fornecedor'];

            $sql = "SELECT * from estoque WHERE etq_Nome = '$NomeProd'";
            $query = mysqli_query($conn, $sql);

            if (mysqli_num_rows($query) > 0) {
                include 'avisoDinamico.php';
                echo "<br>";
                avisoDinamico("Este produto já foi cadastrado necessita somente de adicionar", "#ABA509");
                echo "
                    <div style='width:100%; text-align: center; ' >
                        <h3> <a href='Estoq_entradaProd.php'> Clique aqui </a> para acessar a página de Entrada de produto </h3>
                    </div>";
            } else {
                /*, etq_Preco, etq_Fornecedor, etq_Unidade*/
                 /*, '$preco', '$fornecedor',, '$unidade'*/

                $sql_insert = "INSERT INTO estoque (etq_Nome, etq_Categoria) 
                               VALUES ('$NomeProd', '$categoria')";
                $query_insert = mysqli_query($conn, $sql_insert);

                if (!$query_insert) {
                    include 'avisoDinamico.php';
                    avisoDinamico("Erro ao tentar inserir o produto", "#CF1414");
                } else {
                    include 'avisoDinamico.php';
                    avisoDinamico("Produto inserido com sucesso!!", "#18B308");
                }
            }
        }
    ?>

</body>
</html>
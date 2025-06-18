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
    <title>Cadastro de Usuário</title>

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
    <a class="sair" style="text-decoration:none; position: absolute; top: 1%; left: 2%;" href="Central_adm.php"> <img src="imagens/left.png" width="40px" > </a>

    <div class="conteiner">
        <h1> Cadastrar Usuário </h1>
        <form action="" method="POST">
            <span>
                <label for="Nome">Nome:</label>
                <input type="text" name="Nome" id="Nome">
            </span>

            <span>
                <label for="Senha">Senha:</label>
                <input type="password" name="Senha" id="Senha">
            </span>
            
            <span>
                <label for="Cargo">Cargo:</label>
                <select name="Cargo">
                    <option value="Funcio" > Garçom </option>
                    <option value="Admin" > Administrador </option>
                    <!-- <option value="Caixa" > Caixa </option> -->
                </select>
            </span>
            <div style="text-align: center; width: 100%">
                <input class="botao" type="submit" name="" value="Cadastrar">
            </div>
        </form>
    </div>

    <?php
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            // RECEBENDO OS VALORES DOS INPUTS
            $Nome = $_POST['Nome'];
            $Cargo = $_POST['Cargo'];
            $Senha = $_POST['Senha'];

            $sql = "SELECT * from acessos WHERE ace_Nome = '$Nome'";
            $query = mysqli_query($conn, $sql);

            if (mysqli_num_rows($query) > 0) {
                include 'avisoDinamico.php';
                echo "<br>";
                avisoDinamico("Este usuário já foi cadastrado", "#ABA509");
            } else {
                $sql_insert = "INSERT INTO acessos (ace_Nome, ace_Senha, ace_Cargo) 
                               VALUES ('$Nome', $Senha, '$Cargo')";
                $query_insert = mysqli_query($conn, $sql_insert);

                if (!$query_insert) {
                    include 'avisoDinamico.php';
                    avisoDinamico("Erro ao tentar inserir o Usuário", "#CF1414");
                } else {
                    include 'avisoDinamico.php';
                    avisoDinamico("Usuário inserido com sucesso!!", "#18B308");
                }
            }
        }
    ?>

</body>
</html>
<?php
include 'config.php';

session_start();
if(!isset($_SESSION['usuario'])) {
    header('Location: Validacao.php');
    exit;
}
$usuario = $_SESSION['usuario'];

// ----------------- SCRIPT PHP PARA DESATIVAR USUÁRIOS ------------

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $flag = $_GET['flag'];

    $sql_upd = "UPDATE acessos SET ace_Ativo = '$flag' WHERE ace_Id = $id";
    $query = mysqli_query($conn, $sql_upd);
}
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
        a {
            text-decoration: none;
            color: black;
        }

        /*------ BOTÃO TOGGLE -----*/
            /* Toggle container */
            .switch {
              position: relative;
              display: inline-block;
              width: 60px;
              height: 34px;
            }

            /* Esconde o checkbox real */
            .switch input {
              opacity: 0;
              width: 0;
              height: 0;
            }

            /* Estilo do "slider" */
            .slider {
              position: absolute;
              cursor: pointer;
              top: 0;
              left: 0;
              right: 0;
              bottom: 0;
              background-color: #ccc;
              transition: 0.4s;
              border-radius: 34px;
            }

            /* Círculo dentro do switch */
            .slider:before {
              position: absolute;
              content: "";
              height: 26px;
              width: 26px;
              left: 4px;
              bottom: 4px;
              background-color: white;
              transition: 0.4s;
              border-radius: 50%;
            }

            /* Se marcado */
            input:checked + .slider {
              background-color:#da6c22;
            }

            input:checked + .slider:before {
              transform: translateX(26px);
            }
            .conteiner2 {
                display: none;
                margin: 0 auto;
                width: 400px;
                background-color: #da6c22;
                padding: 20px;
                border-radius: 8px;
            }
        /*------ FIM BOTÃO TOGGLE -----*/

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

    
    <!-- BOTÃO TOGGLE PARA MUDANÇA DO FORM -->
    <div style="width: 400px; margin: 0 auto; text-align: center; padding: 20px;">
        <label class="switch">
            <input type="checkbox" id="toggleSwitch">
            <span class="slider"></span>
        </label>
    </div>

    <div class="conteiner" id="conteiner">
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
    
    <div class="conteiner2" id="conteiner2">
        <?php
            $sql_geral = "SELECT * FROM acessos";
            $query = mysqli_query($conn, $sql_geral);

            if (mysqli_num_rows($query) > 0) {

                echo "<div id='tabela' style='width: 100%; overflow-x: auto; display: flex; justify-content: center;'>";
                    echo "<table width='90%' align='center' border='1' cellspacing='0' cellpadding='5' bgcolor='#FFF'>";
                        echo "<thead>";
                            echo "<tr>";
                                echo "<th> Nome </th>";
                                echo "<th> Função </th>";
                            echo "</tr>";
                        echo "</thead>";
                        echo "<tbody align='center'>";
                            while($linhas = mysqli_fetch_object($query)) {
                                if($linhas->ace_Ativo == 'S') {
                                    $acao = "<span style='color: rgb(155, 4, 4);'> Desativar </span>";
                                    $flag = "N";
                                } else {
                                    $acao = "<span style='color: rgb(18, 110, 6);'> Ativar </span>";
                                    $flag = "S";
                                }

                                switch ($linhas->ace_Cargo) {
                                    case "Admin":
                                        $Cargo = "Administrador";
                                        break;
                                    case "Funcio":
                                        $Cargo = "Garçom";
                                        break;
                                    case "Caixa":
                                        $Cargo = "Caixa";
                                        break;
                                    default:
                                        $Cargo = "Não designado";
                                }

                                echo "<tr data-id='" . $linhas->ace_Id ."'>";
                                    echo "<td>{$linhas->ace_Nome}</td>";
                                    echo "<td>{$Cargo}</td>";
                                    echo "<td> <a href='Cadastro.php?id=$linhas->ace_Id&flag=". $flag ."'>". $acao ."</a> </td>";
                                echo "</tr>";
                            }
                        echo "</tbody>";
                    echo "</table>";
                echo "</div>";
            } else {
                echo "<div style='width: 100%; text-align: center;'>";
                    echo "<h2> Sem usuários cadastrados </h2>";
                echo "</div>";
            }
        ?>
    </div>

    <!-- JAVASCRIPT PARA O BOTÃO TOGGLE -->
    <script>
        document.getElementById("toggleSwitch").addEventListener("change", function () {

            const conteiner = document.getElementById("conteiner");
            const conteiner2 = document.getElementById("conteiner2");

               if (this.checked) {
                    conteiner.style.display = 'none';
                    conteiner2.style.display = 'flex';
               } else {
                    conteiner.style.display = 'block';
                    conteiner2.style.display = 'none';
               }
        });
    </script>

</body>
</html>
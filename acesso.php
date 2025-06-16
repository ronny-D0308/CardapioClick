<?php   
        session_start();

        include('config.php');

        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $user = $_POST['usuario'];
            $pass = $_POST['senha'];
            $cargo = $_POST['cargo'];
            

            $consul = "SELECT ace_Nome, ace_Senha, ace_Cargo FROM acessos WHERE ace_Nome = ?";
            $stmt = $conn->prepare($consul);
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($db_user, $db_senha, $db_cargo);
            
            if ($stmt->num_rows > 0) {
                $stmt->fetch(); 

                if ($pass == $db_senha && $db_cargo == $cargo && $cargo == "Admin") {
                    $_SESSION["loggedin"] = true;
                    $_SESSION["usuario"] = $user;
                    header("location: Central_adm.php");

                } elseif ($pass == $db_senha && $db_cargo == $cargo && $cargo == "Funcio") {
                    $_SESSION["leggedin"] = true;
                    $_SESSION["usuario"] = $user;
                    return header("location: Comandas.php");

                } elseif ($pass == $db_senha && $db_cargo == $cargo && $cargo == "Caixa") {
                    $_SESSION["leggedin"] = true;
                    $_SESSION["usuario"] = $user;
                    return header("location: Caixa_main.php");

                } else {
                    include 'avisoDinamico.php';
                    avisoDinamico("Senha ou usuário inválida!", "#CF1414");
                    echo "
                        <div style='width:100%; text-align: center; margin-top: 10%;' >
                            <h3> <a href='Validacao.php'> Clique aqui </a> para voltar </h3>
                        </div>";
                }
            
            } else {
                echo "Usuário inválido!";
            }
            $stmt->close();
        }
        $conn->close();
?>

<?php
  //INICIA UMA SESSÃO
  session_start();

  $_SESSION = array();
  if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(
          session_name(),
          '',
          time() - 42000,
          $params["path"],
          $params["domain"],
          $params["secure"],
          $params["httponly"]
      );
  }

  // DESTROI A SESSÃO PARA EVITAR ROUBO DE SESSÃO
  session_destroy();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Cardapioclick.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validação de usuário</title>

<!--ESTILIZAÇÃO DA PÁGINA-->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kreon:wght@300..700&display=swap');
        
        *{
            font-family: 'kreon', Arial, Helvetica, sans-serif;
            margin: 0px;
            padding: 0px;
        }
        body{
            background-color:#da6c22;
        }
        h1{
            text-align: center;
            font-size:60px;
            letter-spacing: 10px;
            color: white;
            margin: 30px 0px 40px 0px;
            text-shadow: 5px 2px 3px black;
        }
        .conteiner{
            background-color: #6e2e03;
            display: flex;
            flex-direction:column;
            justify-content: center;
            width: 500px;
            height: auto;
            margin: 0 auto;
            padding: 40px 0px 30px 0px; 
            border-radius: 4px;
            border: solid 2px black;
        }
        #form{
            display: flex;
            flex-direction: column;
            gap: 50px;
            margin:0 auto;
        }
        .campo{
            background-color: transparent;
            border: none;
            border-bottom: white 3px solid;
            width: 200px;
            outline: none;
            color: white;
            font-size: 20px;
        }
       
        .butao{
            height: 35px;
            width: 120px;
            background-color:#da6c22;
            color: aliceblue;
            font-size: 20px;
            font-weight: bolder;
            cursor: pointer;
            border: solid black 1px;
            margin-bottom: 20px;
        }
        p{
            text-align: center;
            color: red;
            font-size: 20px;
        }
        .radio{
            display: flex;
            flex-direction: row;
            color: white;
            gap: 20px;
        }
        .nivel{
             display:flex;
             flex-direction:row;
             gap:10px;
             color:white;
        }
        .acesso{
	     width:15px;
	     height:15px;
	     border-radius:15px;
	     border:none;
	     cursor:pointer;
	 }
	 
	 #conteiner_adm{
	     display:none;
	 }
	 .form_adm{
	     display:flex;
	     flex-direction:column;
	     margin:0;
	 }
	 
        @media screen and (min-width:300px){
            
            h1{
                font-size: 40px;
            }
            .conteiner{
                width: auto;
                margin: 0px 15px 0px 15px;
                padding: 10px 0px;
            }

        }
    </style>

</head>


<body>
    <h1>CardápioClick
    </h1>

    <div class="conteiner">
        <form  action="acesso.php" method="POST" id="form">

            <input class="campo" type="text" id="usuario" name="usuario" id="usuario" placeholder="Digite seu usuário">
            <input class="campo" type="password" id="senha" name="senha" id="senha" placeholder="Digites sua senha">
            <select name="cargo" required >
                <option name="funcio" value="Funcio" >Funcionário</option>
                <option name="adm" value="Admin" >Administrador</option>
                <option name="adm" value="Caixa" >Caixa</option>
            </select>
            
            <button class="butao" type="submit" id="submit" name="submit"> ACESSAR </button>

        </form>
    
    </div>    
</body>
</html>

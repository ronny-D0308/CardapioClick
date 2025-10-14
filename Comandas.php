<?php
  include 'config.php';

  session_start();

  if(!isset($_SESSION['usuario'])) {
    header('Location:index.php');
    exit;
  }
  $usuario = $_SESSION["usuario"];

  $comandaId = !empty($_GET['comandaId']) ? $_GET['comandaId'] : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <link rel="icon" href="Cardapioclick.ico" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Comandas</title>


  <style>
	@import url('https://fonts.googleapis.com/css2?family=Winky+Sans:ital,wght@0,300..900;1,300..900&display=swap');
  </style>
  <style type="text/css">
  	* {
  		padding: 0px;
  		margin: 0px;
  		font-family: "Winky Sans", sans-serif;
		font-optical-sizing: auto;
		font-style: normal;
  	}
    /* Estilo para as divs "comandas" */
    .comandas {
      width: 50%;
      height: 150px;
      background-color: #b88406;
      color: white;
      padding: 10px;
      cursor: pointer;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      border-radius: 10px;
    }

    /* Estilo para o modal (janela centralizada) */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    /* Estilo para o modal (janela centralizada) */
    .vale_modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
      z-index: 1000;
      padding-top: 15%;
    }

    .modal-content {
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      max-width: 600px;
      width: 80%;
      max-height: 80%;
      overflow-y: auto;
    }

    .vale_content {
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      max-width: 400px;
      width: 80%;
      max-height: 80%;
      overflow-y: auto;
      margin: 0 auto;

    }

    .vale_content input {
      width: 190px;
      height: 30px;
      background-color: #b88406;
      color: white;
      outline: none;
      border: none;
      border-radius: 10px;
      padding-left: 5px;
    }

    .vale_content h2{
      text-align: center;
      margin-bottom: 10px;
    }

    input::placeholder {
      color: white;
    }

    .close {
      position: absolute;
      top: 10px;
      right: 10px;
      font-size: 20px;
      cursor: pointer;
      color: red;
    }

    .comandas h1 {
      margin: 0;
      text-align: center;
    }

    /* Estilo para os botões dentro do modal */
    .div-botao {
      display: flex;
      flex-direction: row;
      justify-content: space-around;
      width: 100%;
      margin-top: 10px;
    }

    .div-botao button {
      width: 45%;
      height: 30px;
      background-color: #b88406;
      color: white;
      border: none;
      cursor: pointer;
      border-radius: 5px;
    }

    .div-botao button:hover {
      background-color: rgba(0, 0, 0, 0.7);
    }

    #botao-vale {
      background-color: transparent;
      font-size: 20px;
      border: none;
      color: white;
      cursor: pointer;
    }

    /* Estilo para os itens do cardápio */
    .menu-item {
      margin: 10px 0;
    }

    .menu-item input {
      margin-right: 10px;
    }

    .quantity {
      margin-left: 10px;
      font-weight: bold;
    }

    /* Estilo para o controle de quantidade */
    .quantity-control {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .quantity-control button {
      width: 30px;
      height: 30px;
      background-color: #b88406;
      color: white;
      border: none;
      cursor: pointer;
      border-radius: 5px;
    }

    .quantity-control input {
      width: 50px;
      text-align: center;
    }
    /* Botão de Toggle */
    .toggle-button {
        position: absolute;
        top: 15px;
        left: 20px;
        padding: 10px 15px;
        background-color: transparent;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }
    
    /* Estilo da Sidebar */
    .sidebar {
        height: 100vh; /* 100% da altura da tela */
        width: 250px; /* Largura da sidebar */
        background-color: #6b4e09;
        color: white;
        position: fixed;
        top: 0;
        left: -260px; /* Esconde a sidebar fora da tela */
        transition: 0.3s; /* Transição suave para a sidebar */
        padding-top: 20px;
        padding-left: 10px;
    }
    
    .sidebar h2 {
        color: #fff;
        margin-left: 10px;
    }
    
    .sidebar ul {
        list-style: none;
        padding: 0;
    }
    
    .sidebar ul li {
        padding: 10px;
        margin: 5px 0;
    }
    
    .sidebar ul li a {
        color: #fff;
        text-decoration: none;
        font-size: 18px;
    }
    
    /* Conteúdo principal */
    .content {
        margin-left: 0;
        padding: 20px;
        flex: 1;
    }
    
    /* Classe para mostrar a sidebar */
    .sidebar.active {
        left: 0; /* Quando ativa, a sidebar vai para a posição 0 */
    }

    .conteiner {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 30px;
      max-width: 1200px; /* controla o tamanho máximo do grid */
      margin: 0 auto;     /* centraliza horizontalmente */
      padding: 20px;
      box-sizing: border-box;
    }
    
    @media (max-width: 800px) {
      .conteiner {
        grid-template-columns: repeat(3, 2fr);
      }
      .comandas {
        width: auto;
        height: 100px;
        background-color: #b88406;
        color: white;
        cursor: pointer;
      }

    }


    
    @media (max-width: 600px) {
      h1 {
        font-size: 20px;
      }
      .conteiner {
        grid-template-columns: repeat(3, 2fr);
      }
      .comandas {
        width: auto;
        height: 100px;
        background-color: #b88406;
        color: white;
        cursor: pointer;
      }
    }


    
    @media (max-width: 400px) {
      h1 {
        font-size: 20px;
      }
      .conteiner {
        grid-template-columns: repeat(1, 2fr);
      }
      .comandas {
        width: auto;
        height: 100px;
        background-color: #b88406;
        color: white;
        cursor: pointer;
      }
    }
    /*----- ESTILO DOS BOTÕES DE FORMA DE PAGAMENTO ----*/
        .botoesformapag {
            background-color: #da6c22;
            color: white; 
            font-size: 15px;
            margin: 5px;
        }

  </style>
</head>

<body>
	<div style="background-color: #b88406; display: flex; flex-direction: row; justify-content:center; align-items: center; height: 60px; margin-bottom: 20px;">

		<h1>CardápioClick</h1>
		
		<!-- Botão para abrir a sidebar -->
	    <button class="toggle-button">
	    	<div style="width: 25px; height: 3px; background-color: black; margin-bottom: 5px;"></div>
	    	<div style="width: 25px; height: 3px; background-color: black; margin-bottom: 5px;"></div>
	    	<div style="width: 25px; height: 3px; background-color: black;"></div>
	    </button>

	    <!-- Sidebar -->
	    <div class="sidebar">
	        <h2>Menu</h2>
	        <ul>
	            <!-- <li><button id="botao-vale">Fazer vale</button></li> -->
	            <li><a href="index.php">sair</a></li>
	        </ul>
	    </div>
	</div>

  <div class="conteiner">
    <div class="comandas" id="1">
      <h1>Comanda: 1</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 1 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>

    <div class="comandas" id="2">
      <h1>Comanda: 2</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 2 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>

    <div class="comandas" id="3">
      <h1>Comanda: 3</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 3 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>

    <div class="comandas" id="4">
      <h1>Comanda: 4</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 4 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>

    <div class="comandas" id="5">
      <h1>Comanda: 5</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 5 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>

    <div class="comandas" id="6">
      <h1>Comanda: 6</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 6 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>

    <div class="comandas" id="7">
      <h1>Comanda: 7</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 7 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>

    <div class="comandas" id="8">
      <h1>Comanda: 8</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 8 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>

    <div class="comandas" id="9">
      <h1>Comanda: 9</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 9 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>

    <div class="comandas" id="10">
      <h1>Comanda: 10</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 10 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>

    <div class="comandas" id="11">
      <h1>Comanda: 11</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 11 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>
    <div class="comandas" id="12">
      <h1>Comanda: 12</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 12 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>
    <div class="comandas" id="13">
      <h1>Comanda: 13</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 13 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>
    <div class="comandas" id="14">
      <h1>Comanda: 14</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 14 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>
    <div class="comandas" id="15">
      <h1>Comanda: 15</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 15 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>
    <div class="comandas" id="16">
      <h1>Comanda: 16</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 16 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>
    <div class="comandas" id="17">
      <h1>Comanda: 17</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 17 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>
    <div class="comandas" id="18">
      <h1>Comanda: 18</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 18 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>
    <div class="comandas" id="19">
      <h1>Comanda: 19</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 19 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>
    <div class="comandas" id="20">
      <h1>Comanda: 20</h1>
      <br>
      <h3>
        <?php
              $sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = 20 AND ven_Finalizada <> 'S'");
	            $result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	            $nome_cliente = $result_nome_cliente->ven_Cliente;
              echo $nome_cliente ?? '';
        ?>
      </h3>
    </div>

    <!-- Replicar para as outras comandas... -->
  </div>

  <!-- Modal para exibir a comanda -->
  <div id="modal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Comanda Detalhada</h2>
      <div id="modal-content-body"></div>

        <input type="hidden" id="venMesa" value="">
        <div id="total-value" style="margin-top: 10px; font-weight: bold; text-align: center;">Total: R$ 0,00</div>
        <div class="div-botao">
        <button type="button" id="add-item">Adicionar item</button>
        <button type="button" id="close-comanda">Fechar comanda</button>

      </div>
    </div>
  </div>



  <!-- Modal para exibir a comanda -->
  <div id="vale_modal" class="vale_modal">
    <div class="vale_content">
      <span id="close_vale">&times;</span>
          <form action="vale.php" method="POST">
              <h2>Fazer vale</h2>
              <div style="display: grid; grid-template-columns: 1fr; justify-items: center; gap: 20px 0px;">
                  <input type="text" name="nome_cliente" placeholder="Digite o nome do cliente">
                  <input type="text" name="nome_garcon" placeholder="Digite o nome do garçon">
                  <input class="campoVale" type="number" step="0.01" name="valor" placeholder="Valor da comanda" required>
              </div>
              <div class="div-botao">
                <button type="submit">Fazer vale</button>
              </div>
          </form>
    </div>
  </div>



  <!-- Modal para o cardápio -->
    <div id="menu-modal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Cardápio</h2>
      <div id="menu-items">
        <div class="menu-item">
          <input type="checkbox" id="item1">
          <label for="item1">Feijão - R$ 5,00</label>
          <div class="quantity-control" id="quantity-item1" style="display: none;">
            <button type="button" class="decrease">-</button>
            <input type="number" value="1" min="1" />
            <button type="button" class="increase">+</button>
          </div>
        </div>
        <div class="menu-item">
          <input type="checkbox" id="item2">
          <label for="item2">Arroz - R$ 4,00</label>
          <div class="quantity-control" id="quantity-item2" style="display: none;">
            <button type="button" class="decrease">-</button>
            <input type="number" value="1" min="1" />
            <button type="button" class="increase">+</button>
          </div>
        </div>
        <div class="menu-item">
          <input type="checkbox" id="item3">
          <label for="item3">Salada - R$ 3,00</label>
          <div class="quantity-control" id="quantity-item3" style="display: none;">
            <button type="button" class="decrease">-</button>
            <input type="number" value="1" min="1" />
            <button type="button" class="increase">+</button>
          </div>
        </div>
        <div class="menu-item">
          <input type="checkbox" id="item4">
          <label for="item4">Suco de Laranja - R$ 6,00</label>
          <div class="quantity-control" id="quantity-item4" style="display: none;">
            <button type="button" class="decrease">-</button>
            <input type="number" value="1" min="1" />
            <button type="button" class="increase">+</button>
          </div>
        </div>
        <div class="menu-item">
          <input type="checkbox" id="item5">
          <label for="item5">Sobremesa - R$ 2,50</label>
          <div class="quantity-control" id="quantity-item5" style="display: none;">
            <button type="button" class="decrease">-</button>
            <input type="number" value="1" min="1" />
            <button type="button" class="increase">+</button>
          </div>
        </div>
        <button id="add-to-order">Adicionar à Comanda</button>
      </div>
    </div>
    </div>
  

  <script>
    let itensDaComandaAtual = [];
    const comandas = document.querySelectorAll('.comandas');
    const valeModal = document.getElementById('vale_modal');
    const modal = document.getElementById('modal');
    //const botaoVale = document.getElementById('botao-vale');
    const closeBtn = document.querySelector('.close');
    const closeVale = document.getElementById('close_vale');
    const modalContent = document.getElementById('modal-content-body');
    const totalValue = document.getElementById('total-value');
    //const menuModal = document.getElementById('menu-modal');
    //const menuCloseBtn = menuModal.querySelector('.close');
    const addToOrderBtn = document.getElementById('add-to-order');
    const addItemBtn = document.getElementById('add-item');
    const closeComandaBtn = document.getElementById('close-comanda');
    let selectedComanda = null;

    // Armazena os itens de cada comanda em um objeto
    const comandasItens = {
      comanda1: [],
      comanda2: [],
      comanda3: [],
      comanda4: [],
      comanda5: [],
      comanda6: [],
      comanda7: [],
      comanda8: [],
      comanda9: [],
      comanda10: [],
      comanda11: [],
      comanda12: [],
      comanda13: [],
      comanda14: [],
      comanda15: [],
      comanda16: [],
      comanda17: [],
      comanda18: [],
      comanda19: [],
      comanda20: [],
    };

    comandas.forEach(comanda => {
      comanda.addEventListener('click', function () {
        const comandaId = comanda.id;
        selectedComanda = comanda;

        fetch(`verificar_comanda.php?mesa=${comandaId}`)
          .then(response => response.json())
          .then(data => {
            itensDaComandaAtual = data.itens; // <- agora você tem acesso depois
            modalContent.innerHTML = '';
            let totalComanda = 0;

            if (data.temItens && data.itens.length > 0) {
              const primeiroItem = data.itens[0];
              document.getElementById('venMesa').value = primeiroItem.ven_Mesa;

              let tabelaHTML = `
                <table style="width:100%; border-collapse: collapse; font-family: sans-serif;">
                  <tr>
                    <td colspan="2" style="padding: 8px; font-weight: bold;">Cliente: ${primeiroItem.Cliente}</td>
                    <td colspan="2" style="padding: 8px; font-weight: bold;">Garçom: ${primeiroItem.Garcom}</td>
                  </tr>
                  <tr>
                    <th style="text-align: left; padding: 6px;">Item</th>
                    <th style="text-align: center; padding: 6px;"></th>
                    <th style="text-align: right; padding: 6px;">Valor Unitário</th>
                    <th style="text-align: right; padding: 6px;">Subtotal</th>
                  </tr>
              `;

              // Agrupa os itens por nome
              const agrupados = {};

              data.itens.forEach(item => {
                const nome = item.nome || 'Item';
                const precoUnitario = item.preco_unitario ?? item.Valor;

                if (!agrupados[nome]) {
                  agrupados[nome] = {
                    quantidade: item.quantidade || 1,
                    preco: precoUnitario,
                    subtotal: (item.subtotal || item.Valor),
                  };
                } else {
                  agrupados[nome].quantidade += item.quantidade || 1;
                  agrupados[nome].subtotal += (item.subtotal || item.Valor);
                }

                totalComanda = item.ven_Valor;
              });

              // Gera as linhas da tabela
              for (const nome in agrupados) {
                const item = agrupados[nome];
                tabelaHTML += `
                  <tr>
                    <td style="padding: 6px;">${nome}</td>
                    <td style="text-align: center;"></td>
                    <td style="text-align: right;"> ${item.quantidade}  X  ${item.preco.toFixed(2)}</td>
                    <td style="text-align: right;">R$ ${item.subtotal.toFixed(2)}</td>
                  </tr>
                `;
              }

              tabelaHTML += `</table>`;
              modalContent.innerHTML = tabelaHTML;
              totalValue.innerHTML = `<p style="margin-top: 10px; font-weight: bold;">Total: R$ ${totalComanda.toFixed(2)}</p>`;
              modal.style.display = 'flex';
            } else {
              modalContent.innerHTML = `
                <p style="padding: 10px; font-weight: bold;">Esta comanda não tem itens.</p>
              `;
              totalValue.innerHTML = '';
              modal.style.display = 'flex';
            }
          })
          .catch(error => {
            console.error('Erro ao verificar a comanda:', error);
          });
      });
    });


    closeBtn.addEventListener('click', function() {
      modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
      if (event.target === valeModal) {
        valeModal.style.display = 'none';
      }
    });


    window.addEventListener('click', function(event) {
      if (event.target === modal) {
        modal.style.display = 'none';
      }
    });

    addItemBtn.addEventListener('click', function () {
      if (selectedComanda && selectedComanda.id) {
        const comandaId = selectedComanda.id;
        console.log('Redirecionando para comanda:', comandaId);
        window.location.href = `Adicionar_itens.php?comandaId=${comandaId}`;
      } else {
        //alert('Nenhuma comanda selecionada.');
      }
    });

    // Exibe o controle de quantidade quando o checkbox for marcado
    const checkboxes = document.querySelectorAll('.menu-item input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        const quantityControl = document.getElementById('quantity-' + checkbox.id);
        if (checkbox.checked) {
          quantityControl.style.display = 'flex';
        } else {
          quantityControl.style.display = 'none';
        }
      });
    });

    // Controle de quantidade de produtos
    document.querySelectorAll('.quantity-control').forEach(control => {
      const decreaseBtn = control.querySelector('.decrease');
      const increaseBtn = control.querySelector('.increase');
      const quantityInput = control.querySelector('input');

      decreaseBtn.addEventListener('click', function() {
        let quantity = parseInt(quantityInput.value);
        if (quantity > 1) {
          quantityInput.value = quantity - 1;
        }
      });

      increaseBtn.addEventListener('click', function() {
        let quantity = parseInt(quantityInput.value);
        quantityInput.value = quantity + 1;
      });
    });

    // Adiciona os itens à comanda
    addToOrderBtn.addEventListener('click', function() {
      checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
          const quantity = parseInt(document.querySelector(`#quantity-${checkbox.id} input`).value);
          const itemName = checkbox.nextElementSibling.textContent.trim().split(" - ")[0];
          //const itemPrice = productPrices[checkbox.id];
          
          // Verificar se o item já foi adicionado à comanda
          const comandaId = selectedComanda.id;
          const itemIndex = comandasItens[comandaId].findIndex(item => item.name === itemName);
          
          if (itemIndex >= 0) {
            // Se já estiver na comanda, só aumenta a quantidade
            comandasItens[comandaId][itemIndex].quantity += quantity;
          } else {
            // Se não, adiciona o item à comanda
            comandasItens[comandaId].push({ name: itemName, quantity: quantity, price: itemPrice });
          }
        }
      });

      //menuModal.style.display = 'none'; // Fecha o modal de cardápio
    });

closeComandaBtn.addEventListener('click', function () {
    const venMesa = parseInt(document.getElementById('venMesa').value);

    if (confirm("Deseja realmente finalizar e imprimir esta comanda?")) {

        // Overlay escuro
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
        overlay.style.zIndex = '999';
        document.body.appendChild(overlay);

        // Modal de pagamentos
        const modal = document.createElement('div');
        modal.style.position = 'fixed';
        modal.style.top = '50%';
        modal.style.left = '50%';
        modal.style.transform = 'translate(-50%, -50%)';
        modal.style.backgroundColor = 'white';
        modal.style.padding = '20px';
        modal.style.boxShadow = '0 0 10px rgba(0,0,0,0.5)';
        modal.style.zIndex = '1000';
        modal.style.display = 'flex';
        modal.style.flexDirection = 'column';
        modal.style.alignItems = 'center';
        modal.style.width = '350px';
        modal.style.borderRadius = '8px';

        modal.innerHTML = `
            <h4 style='color:black;'>Forma(s) de Pagamento</h4>
            <div id="pagamentos-container" style="width:100%; margin-bottom:10px;">
                <div class="pagamento-item" style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <select class="forma-pagamento" style="flex:1; margin-right:5px;">
                        <option value="">Selecione...</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="credito">Cartão de Crédito</option>
                        <option value="debito">Cartão de Débito</option>
                        <option value="pix">PIX</option>
                    </select>
                    <input type="number" class="valor-pagamento" placeholder="Valor" style="flex:1;">
                </div>
            </div>
            <button id="add-payment-method" class="botoesformapag" style="margin-bottom:10px;">Adicionar método</button>
            <button class='botoesformapag' id="confirmarPagamento">Confirmar</button>
            <button class='botoesformapag' id="cancelarPagamento">Cancelar</button>
        `;
        document.body.appendChild(modal);

        // Adicionar novo método de pagamento
        document.getElementById('add-payment-method').addEventListener('click', () => {
            const container = document.getElementById('pagamentos-container');
            const div = document.createElement('div');
            div.classList.add('pagamento-item');
            div.style.display = 'flex';
            div.style.justifyContent = 'space-between';
            div.style.marginBottom = '5px';
            div.innerHTML = `
                <select class="forma-pagamento" style="flex:1; margin-right:5px;">
                    <option value="">Selecione...</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="credito">Cartão de Crédito</option>
                    <option value="debito">Cartão de Débito</option>
                    <option value="pix">PIX</option>
                </select>
                <input type="number" class="valor-pagamento" placeholder="Valor" style="flex:1;">
            `;
            container.appendChild(div);
        });

        // Confirmar pagamento
        document.getElementById('confirmarPagamento').addEventListener('click', () => {
            const itens = itensDaComandaAtual.map(item => ({
                nome: item.nome,
                quantidade: item.quantidade
            }));

            const pagamentos = [];
            const selects = modal.querySelectorAll('.forma-pagamento');
            const valores = modal.querySelectorAll('.valor-pagamento');

            for (let i = 0; i < selects.length; i++) {
                const forma = selects[i].value;
                const valor = parseFloat(valores[i].value) || 0;
                if (forma && valor > 0) {
                    pagamentos.push({ forma, valor });
                }
            }

            if (pagamentos.length === 0) {
                alert("Informe pelo menos um método de pagamento válido.");
                return;
            }

            // Envia dados para o PHP
            fetch(`fechar_comanda.php?venMesa=${venMesa}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ itens, pagamentos })
            })
            .then(res => res.json())
            .then(data => {
                if (data.sucesso) {
                    document.body.removeChild(modal);
                    document.body.removeChild(overlay);
                    window.location.reload();
                } else {
                    alert('Erro ao fechar comanda: ' + data.erro);
                }
            })
            .catch(err => console.error(err));
        });

        // Cancelar
        document.getElementById('cancelarPagamento').addEventListener('click', () => {
            document.body.removeChild(modal);
            document.body.removeChild(overlay);
        });
    }
});


    // Selecionando os elementos
    const toggleButton = document.querySelector('.toggle-button');
    const sidebar = document.querySelector('.sidebar');
    const content = document.querySelector('.content');

    // Função para abrir e fechar a sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('active');
    }

    // Função para fechar a sidebar se o clique for fora dela
    function closeSidebar(event) {
        if (!sidebar.contains(event.target) && !toggleButton.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    }

    // Adicionando eventos
    toggleButton.addEventListener('click', toggleSidebar); // Para abrir e fechar a sidebar
    document.addEventListener('click', closeSidebar); // Para fechar a sidebar ao clicar fora dela


  </script>
</body>
</html>

<?php
	session_start();

	if(!isset($_SESSION['usuario'])) {
		header('Location:Validacao.php');
		exit;
	}
	$usuario = $_SESSION["usuario"];

	$comandaId = !empty($_GET['comandaId']) ? $_GET['comandaId'] : '';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Cardapioclick.ico" type="image/x-icon">
    <meta name="viewport"  content="width=device-width, initial-scale=1.0">
    <title>.:Comanda Digital:.</title> 

	<style>
		@import url('https://fonts.googleapis.com/css2?family=Kreon:wght@300..700&display=swap');

		*{
			font-family: 'kreon','Arial';
			margin: 0;
			color: white;
		}
		body{
			background-color:#b88406;
		}
		h1{
			margin: 10px;
			margin-bottom: 20px;
			font-size: 40px;
			color: white;
			text-align: center;
			font-family: 'kreon','Arial';
		}
		.adicionar{
			text-align: center;
			cursor: pointer;
		}
		#menu ul{
			list-style-type: none;
			padding: 0;
			margin: 0;
			text-align: center;
		}

		.conteiner{
			margin-bottom: 30px;
		}

		.informs{
			text-align: center;
		}

		.conteiner_espetos{
			display:flex;
			justify-content:space-around;
			padding:20px;
			flex-wrap:wrap;
			row-gap:10px;
		}
		.conteiner_bebidas{
			display:flex;
			justify-content:space-around;
			padding:20px;
			flex-wrap:wrap;
			row-gap:10px;
		}

		.conteiner_acomp{
			display:flex;
			justify-content:space-around;
			padding:20px;
			flex-wrap:wrap;
			row-gap:10px;
		}

		.conteiner_whisky{
			display:flex;
			justify-content:space-around;
			padding:20px;
			flex-wrap:wrap;
			row-gap:10px;
		}

		.botao{
			display: flex;
			justify-content: center;
			gap: 20px;
		}
		#nome_cliente{
			margin: 20px 15px 30px 15px;
			display: inline;
			background: transparent;
			border: 0px;
			color:white;
			outline: none;
			border-bottom: 3px white solid;
			font-size: 20px;
			width: 150px;
			text-align:center;
		}
		::-webkit-input-placeholder{
			color:white;
			opacity: 50%;
		}
		.QUANT{
			display: flex;
			background: transparent;
			border: 0px;
			color: white;
			outline: none;
			border-bottom: 3px white solid;
			font-size:15px ;
			width:60px;
		}

		.nome{
			display: flex;
			flex-direction: column;
			color: white;
			font-size: 25px;
		}

		.itens{
			font-size: 30px;
			margin: 30px 0px 10px 20px;
			color: white;
			font-weight: bold;
			text-align: left;
			margin-left: 7%;
		}
		#butao_enviar{
			height: 40px;
			width: 250px;
			background-color: #733309;
			color: aliceblue;
			font-size: 20px;
			border-radius: 10px;
			font-weight: bolder;
			justify-content: center;
			border: 0;
			cursor: pointer;
		}
		#total {
			margin: 0 auto;
		}
		.conteiner-produto{
			text-align: center;
			background: rgba(0, 0, 0, 0.3);
			width:auto;
			border-radius:5px;
			display:flex;
			flex-direction:column;
			align-items: center;
			padding:5px;
		}

		@media screen and (min-width:300px){
			.button{
				margin-left: auto;
				margin-right: auto;
				width: 40%;
			}
			
		}
	</style>

</head>

<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.conteiner-produto').forEach(produto => {
			const label = produto.querySelector('label');
			const input = produto.querySelector('input');
			const nomeItem = label.textContent.trim();
	
			// Envia o nome do item ao backend
			fetch(`Bloqueia_item.php`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({ item: nomeItem })
			})
			.then(response => response.json())
			.then(data => {
				if (data.bloquear === true) {
					console.log("Produto desativado");
	                // Desativa o input corretamente:
	                input.style.display = 'none';
	                //input.disabled = true;
	                //input.style.backgroundColor = "#473004"; // visual opcional
	                //input.style.cursor = "not-allowed";
				}
			})
			.catch(error => {
				console.error('Erro na requisição:', error);
			});
		});
	});
</script>


<body>
    <div class="conteiner">
		<a class="sair" style="text-decoration:none; margin:0px 0px 0px 10px;" href="Comandas.php"> <img src="imagens/left.png" width="40px" > </a>

        <h1>CardápioClick</h1>
		
		<!--
		<div class="adicionar">
			<h1 onclick="chamar()">+</h1>
		</div>
		-->

        <form method="POST" action="processa.php" onsubmit="atualizarHiddenTotal(); getItensSelecionados();">

        	<!-- INPUT QUE CARREGA OS ITEMS SELECIONADOS -->
        	<input type="hidden" name="itens_selecionados" id="itens_selecionados_input">
			
			<div class="informs">
				<input id="nome_cliente" type="text" name="nome_cliente" placeholder="Cliente" color="white" maxlength="20" required>
				<input id="nome_cliente" type="number" name="mesa" placeholder="Mesa" color="white" maxlength="20" value="<?=$comandaId?>">
				<!-- <input id="nome_cliente" type="text" name="garcon" placeholder="Garçom" color="white" maxlength="20" > -->
			</div>

            
			<!--CAMPOS DOS ESPETOS-->
                <h1 class="itens">Espetos:</h1>

                <div class="conteiner_espetos">

                	<div class="conteiner-produto">
						<label class="nome">Gado</label>
						<input name="E1" class="QUANT"  id="E1" type="number"  min="0" onblur="atualizarTotal()">
					</div>

					<div class="conteiner-produto">
						<label class="nome">Porco</label>
						<input name="E2" class="QUANT"  id="E2" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Frango</label>
						<input name="E3" class="QUANT"  id="E3" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Linguiça</label>
						<input name="E4" class="QUANT"  id="E4" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Calabresa</label>
						<input name="E5" class="QUANT"  id="E5" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Queijo</label>
						<input name="E6" class="QUANT"  id="E6" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<!--
					<div class="conteiner-produto">
						<label class="nome">Coração de Frango</label>
						<input name="E7" class="QUANT"  id="E7" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Maminha</label>
						<input name="E8" class="QUANT"  id="E8" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Picanha</label>
						<input name="E9" class="QUANT"  id="E9" type="number"  min="0" onblur="atualizarTotal()">
                   	</div>
					-->

                </div>
	<hr>
				
		<!--CAMPOS DAS BEBIDAS-->
                <h1 class="itens">Bebidas:</h1>

                <div class="conteiner_bebidas">
					<div class="conteiner-produto">
						<label class="nome">Coca-cola 1LT</label>
						<input name="B1" class="QUANT"  id="B1" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Guaraná 1LT</label>
						<input name="B2" class="QUANT"  id="B2" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Devassa</label>
						<input name="B3" class="QUANT"  id="B3" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Pitchulinha</label>
						<input name="B4" class="QUANT"  id="B4" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Refrigerante Lata</label>
						<input name="B5" class="QUANT"  id="B5" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Suco</label>
						<input name="B6" class="QUANT"  id="B6" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Àgua com gás</label>
						<input name="B7" class="QUANT"  id="B7" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Àgua sem gás</label>
						<input name="B08" class="QUANT"  id="B08" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Àgua Fresh</label>
						<input name="B9" class="QUANT"  id="B9" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Heineken 600ml</label>
						<input name="B10" class="QUANT"  id="B10" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Coca zero</label>
						<input name="B11" class="QUANT"  id="B11" type="number"  min="0" onblur="atualizarTotal()">
					</div>

					<div class="conteiner-produto">
						<label class="nome">Heineken Long Neck</label>
						<input name="B12" class="QUANT"  id="B12" type="number"  min="0" onblur="atualizarTotal()">
					</div>

					<!--
					<div class="conteiner-produto">
						<label class="nome">Corona Long Neck</label>
						<input name="B13" class="QUANT"  id="B13" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Spaten Long Neck</label>
						<input name="B14" class="QUANT"  id="B14" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Budweiser Long Neck</label>
						<input name="B15" class="QUANT"  id="B15" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Stella Artois Long Neck</label>
						<input name="B16" class="QUANT"  id="B16" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Ice Smirnoff</label>
						<input name="B17" class="QUANT"  id="B17" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Ice Cabaré</label>
						<input name="B18" class="QUANT"  id="B18" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Skol Beats</label>
						<input name="B19" class="QUANT"  id="B19" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Brahma Duplo Malte 300ml</label>
						<input name="B20" class="QUANT"  id="B20" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					-->

					<div class="conteiner-produto">
						<label class="nome">Skol</label>
						<input name="B21" class="QUANT"  id="B21" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Red Bull</label>
						<input name="B22" class="QUANT"  id="B22" type="number"  min="0" onblur="atualizarTotal()">
					</div>

                </div>
	<hr>

		<!--CAMPOS DOS WHISKYS-->
				<!--
				<h1 class="itens">Bebida Quente:</h1>
				<div class="conteiner_whisky">
					<div class="conteiner-produto">
						<label class="nome">Red Label</label>
						<input name="W1" class="QUANT"  id="W1" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Black Label</label>
						<input name="W2" class="QUANT"  id="W2" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Buchanan's</label>
						<input name="W3" class="QUANT"  id="W3" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Black White</label>
						<input name="W4" class="QUANT"  id="W4" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Old Par 12 Anos</label>
						<input name="W5" class="QUANT"  id="W5" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Old Par 18 Anos</label>
						<input name="W6" class="QUANT"  id="W6" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Ballantine</label>
						<input name="W7" class="QUANT"  id="W7" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Cachaça Amarela</label>
						<input name="W8" class="QUANT"  id="W8" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">						
						<label class="nome">Cachaça Empalhada</label>
						<input name="W9" class="QUANT"  id="W9" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Dreher</label>
						<input name="W10" class="QUANT"  id="W10" type="number"  min="0" onblur="atualizarTotal()">
					</div>
				</div>
				-->

	<hr>
		<!--CAMPOS DOS ACOMPANHANTES-->	

			<h1 class="itens">Acompanhante:</h1>
			<div class="conteiner_acomp">
				<div class="conteiner-produto">
					<label class="nome" for="acomp" style="display:none;">Feijão</label>
					<h2>Baião</h2>
					<input class="QUANT"  type="number" id="A1" name="A1" min="0" onblur="atualizarTotal()">
				</div>
				<div class="conteiner-produto">
					<label class="nome" for="acomp">Arroz</label>
					<input class="QUANT"  type="number" id="A2" name="A2" min="0" onblur="atualizarTotal()">
				</div>
			</div>
	<hr>

		<!--CAMPOS DOS PETISCOS-->	

		<h1 class="itens">Petiscos:</h1>
			<div class="conteiner_acomp">
				<div class="conteiner-produto">
					<label class="nome" for="acomp">Bolinha</label>
					<input class="QUANT"  type="number" id="P1" name="P1" min="0" onblur="atualizarTotal()">
				</div>
				<div class="conteiner-produto">
					<label class="nome" for="acomp">Batata</label>
					<input class="QUANT"  type="number" id="P2" name="P2" min="0" onblur="atualizarTotal()">
				</div>
			</div>


				<h3 id="total">Total: R$ </h3>
				<input type="hidden" name="total" id="total_input">

				<div class="botao">
					<button id="butao_enviar" type="submit" name="Envio_de_comanda"> ADICIONAR À COMANDA </button>
				</div> 

        </form>
    </div>
    		<!--
			<script>
				function chamar() {
					window.open ("Comandas.php");
				}
			</script>
			-->

	<script>
		const precos = {
			E1: 5.00, E2: 5.00, E3: 5.00, E4: 5.00, E5: 5.00, E6: 6.00, E7: 8.00, E8: 15.00, E9: 17.00,

			B1: 5.00, B2: 8.00, B3: 5.00, B4: 3.50, B5: 5.50, B6: 6.00, B7: 3.00, B8: 2.50, B9: 3.00,
			B10: 12.00, B11: 6.00, B12: 10.00, B13: 10.50, B14: 9.50, B15: 9.00, B16: 10.00, B17: 8.00, B18: 7.50, 
			B19: 9.00, B20: 7.00, B21: 7.00, B22: 7.00,

			W1: 10.00, W2: 10.00, W3: 10.00, W4: 10.00, W5: 10.00, W6: 10.00, W7: 10.00, W8: 10.00, W9: 10.00, W10: 10.00,

			A1:8.00, A2: 9.00,

			P1: 15.00, P2: 15.00
		};


        function atualizarTotal() {
			let total = 0;

			document.querySelectorAll(".QUANT").forEach(input => {
				let id = input.id;
				let quantidade = parseFloat(input.value) || 0; // Garante que valores vazios sejam considerados como 0
				if (precos[id]) {
					total += quantidade * precos[id];
				}
			});

			document.getElementById("total").textContent = `Total: R$ ${total.toFixed(2)}`;
		}

		function atualizarHiddenTotal() {
			let totalText = document.getElementById("total").innerText;
			let totalValue = totalText.replace("Total: R$ ", "").trim();
			document.getElementById("total_input").value = totalValue;
		}

		// FUNÇÃO JAVASCRIPT QUE CARREGA OS ITENS E SEUS VALORES 
		function getItensSelecionados() {
			const itensSelecionados = [];

			document.querySelectorAll('.conteiner-produto').forEach(produto => {
				const input = produto.querySelector('input');
				const label = produto.querySelector('label');

				if (input && label) {
					let quantidade = parseFloat(input.value);
					if (quantidade > 0) {
						const id = input.id;
						const preco_unitario = precos[id] || 0;
						const subtotal = quantidade * preco_unitario;

						itensSelecionados.push({
							nome: label.textContent.trim(),
							quantidade: quantidade,
							preco_unitario: preco_unitario,
							subtotal: subtotal
						});
					}
				}
			});

			console.log("Itens com valores:", itensSelecionados); // Debug no console

			document.getElementById("itens_selecionados_input").value = JSON.stringify(itensSelecionados);
		}

	</script>
			

</body>
</html>



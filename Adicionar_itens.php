<?php
	error_reporting(0);       // Desativa todos os relatórios de erro
    ini_set('display_errors', 0);  // Garante que os erros não sejam exibidos na tela
    

	include 'config.php';

	session_start();

	if(!isset($_SESSION['usuario'])) {
		header('Location:index.php');
		exit;
	}
	$usuario = $_SESSION["usuario"];

	$comandaId = !empty($_GET['comandaId']) ? $_GET['comandaId'] : '';

	$sql_nome_cliente = mysqli_query($conn, "SELECT ven_Cliente FROM vendas WHERE ven_Mesa = $comandaId AND ven_Finalizada <> 'S'");
	$result_nome_cliente = mysqli_fetch_object($sql_nome_cliente);
	$nome_cliente = $result_nome_cliente->ven_Cliente;
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
			background-color:#da6c22;
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
		#butao_enviar_manual{
			height: 40px;
			width: auto;
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
		        const label = produto.querySelector('label') || produto.querySelector('h2'); // Inclui h2 para "Baião"
		        const input = produto.querySelector('input');
		        
		        if (!label || !input) return; // Pula se não encontrar elementos
		        
		        const nomeItem = label.textContent.trim();
		
		        // Envia o nome do item ao backend
		        fetch(`Bloqueia_item.php`, {
		            method: 'POST',
		            headers: {
		                'Content-Type': 'application/json'
		            },
		            body: JSON.stringify({ item: nomeItem })
		        })
		        .then(response => {
		            if (!response.ok) {
		                throw new Error(`HTTP error! status: ${response.status}`);
		            }
		            return response.json();
		        })
		        .then(data => {
		            if (data.bloquear === true) {
		                console.log(`Produto bloqueado: ${nomeItem}`);
		                
		                // ✅ Melhor visualização para produtos bloqueados
		                input.style.display = 'none';
		                
		                // ✅ Adicionar indicador visual
		                const indicador = document.createElement('div');
		                indicador.className = 'produto-indisponivel';
		                indicador.innerHTML = '❌ Indisponível';
		                indicador.style.cssText = `
		                    color: #ff6b6b;
		                    font-size: 14px;
		                    font-weight: bold;
		                    margin-top: 5px;
		                    text-align: center;
		                `;
		                
		                // Adicionar o indicador após o input
		                input.parentNode.appendChild(indicador);
		                
		                // ✅ Opcional: Escurecer o container do produto
		                produto.style.opacity = '0.5';
		                produto.style.filter = 'grayscale(100%)';
		            }
		            
		            // ✅ Log de informações adicionais se disponível
		            if (data.motivo) {
		                console.log(`Motivo do bloqueio para ${nomeItem}: ${data.motivo}`);
		            }
		        })
		        .catch(error => {
		            console.error(`Erro ao verificar disponibilidade de ${nomeItem}:`, error);
		            
		            // ✅ Em caso de erro, mostrar aviso discreto
		            const avisoErro = document.createElement('div');
		            avisoErro.innerHTML = '⚠️ Erro ao verificar';
		            avisoErro.style.cssText = `
		                color: #ffa500;
		                font-size: 12px;
		                margin-top: 3px;
		                text-align: center;
		            `;
		            input.parentNode.appendChild(avisoErro);
		        });
		    });
		});
		
		function adicionarManual() {
		    const item = document.querySelector('input[name="Man_item"]').value.trim();
		    const quant = document.querySelector('input[name="Man_qtd"]').value;
			const preco = parseFloat(document.querySelector('input[name="Man_preco"]').value);
		    const comandaId = <?= $comandaId ?>; // Certifique-se que $comandaId está definido no PHP
		    const nomeCliente = document.querySelector('input[name="nome_cliente"]').value.trim(); // ✅ Novo
		
		
		    if (!item || preco <= 0) {
		        alert('Preencha corretamente o item e preço do item.');
		        return;
		    }
		
		    fetch('adicionar_manual.php', {
		        method: 'POST',
		        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
		        body: `Man_item=${encodeURIComponent(item)}&Man_qtd=${quant}&Man_preco=${preco}&comandaId=${comandaId}&nome_cliente=${encodeURIComponent(		nomeCliente)}`
		    })
		    .then(res => res.json())
		    .then(data => {
		        if (data.erro) {
		            alert('Erro: ' + data.mensagem);
		        } else {
		            // alert(data.mensagem);
		
		            // Atualizar lista de itens (opcional)
		            console.log('Itens da comanda:', data.itens);
		
		            // Atualizar total no front-end
		            atualizarTotal(data.total);
		
		            // Redirecionar se necessário
		            if (data.redirect) {
		                window.location.href = data.redirect;
		            }
		        }
		    })
		    .catch(err => alert('Erro ao adicionar item: ' + err));
		}
			
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
				<input id="nome_cliente" type="text" name="nome_cliente" placeholder="Cliente" color="white" maxlength="20" 
							value="<?= isset($nome_cliente) ? $nome_cliente : "" ?>"  required>
				<input id="nome_cliente" type="number" name="mesa" placeholder="Mesa" color="white" maxlength="20" value="<?= $comandaId ?>">
				<!-- <input id="nome_cliente" type="text" name="garcon" placeholder="Garçom" color="white" maxlength="20" > -->
			</div>

            
			<!-- CAMPOS DOS ESPETOS -->
                <h1 class="itens">Refeição:</h1>
                <div class="conteiner_espetos">
                	<div class="conteiner-produto">
						<label class="nome">1 Pessoa</label>
						<input name="E1" class="QUANT"  id="E1" type="number"  min="0" onblur="atualizarTotal()">
					</div>

					<div class="conteiner-produto">
						<label class="nome">2 Pessoa</label>
						<input name="E2" class="QUANT"  id="E2" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">3 Pessoa</label>
						<input name="E3" class="QUANT"  id="E3" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">4 Pessoa</label>
						<input name="E4" class="QUANT"  id="E4" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">5 Pessoa</label>
						<input name="E5" class="QUANT"  id="E5" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<!--
					<div class="conteiner-produto">
						<label class="nome">Queijo</label>
						<input name="E6" class="QUANT"  id="E6" type="number"  min="0" onblur="atualizarTotal()">
					</div>
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
						<label class="nome">Coca 600ml</label>
						<input name="B3" class="QUANT"  id="B3" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Coca-cola 2L</label>
						<input name="B25" class="QUANT"  id="B25" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<!--
					<div class="conteiner-produto">
						<label class="nome">Pitchulinha</label>
						<input name="B4" class="QUANT"  id="B4" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					-->
					<div class="conteiner-produto">
						<label class="nome">Refrigerante Lata</label>
						<input name="B5" class="QUANT"  id="B5" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome"> Copo suco </label>
						<input name="B6" class="QUANT"  id="B6" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome"> Jarra suco </label>
						<input name="B26" class="QUANT"  id="B26" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Àgua com gás</label>
						<input name="B7" class="QUANT"  id="B7" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Àgua sem gás</label>
						<input name="B8" class="QUANT"  id="B8" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<!--
					<div class="conteiner-produto">
						<label class="nome">Aquárius Fresh</label>
						<input name="B9" class="QUANT"  id="B9" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					-->
					<div class="conteiner-produto">
						<label class="nome">Baly</label>
						<input name="B11" class="QUANT"  id="B11" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Heineken 600ml</label>
						<input name="B10" class="QUANT"  id="B10" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Heineken Long Neck</label>
						<input name="B12" class="QUANT"  id="B12" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Corona Long Neck</label>
						<input name="B13" class="QUANT"  id="B13" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<!--
					<div class="conteiner-produto">
						<label class="nome">Spaten Long Neck</label>
						<input name="B14" class="QUANT"  id="B14" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					-->
					<div class="conteiner-produto">
						<label class="nome">Budweiser Long Neck</label>
						<input name="B15" class="QUANT"  id="B15" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Stella Artois Long Neck</label>
						<input name="B16" class="QUANT"  id="B16" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<!--
					<div class="conteiner-produto">
						<label class="nome">Ice Smirnoff</label>
						<input name="B17" class="QUANT"  id="B17" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Skol Beats</label>
						<input name="B19" class="QUANT"  id="B19" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					-->
					<div class="conteiner-produto">
						<label class="nome">Ice Cabaré</label>
						<input name="B18" class="QUANT"  id="B18" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Brahma 600ml</label>
						<input name="B20" class="QUANT"  id="B20" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Brahma 300ml</label>
						<input name="B21" class="QUANT"  id="B21" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Skol 600ml</label>
						<input name="B22" class="QUANT"  id="B22" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Skol 300ml</label>
						<input name="B23" class="QUANT"  id="B23" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Red Bull</label>
						<input name="B24" class="QUANT"  id="B24" type="number"  min="0" onblur="atualizarTotal()">
					</div>
                </div>
				
				<hr>

			<!--CAMPOS DOS WHISKYS-->
				<h1 class="itens">Destilados:</h1>
				<div class="conteiner_whisky">
					<div class="conteiner-produto">
						<label class="nome">Red Label</label>
						<input name="W1" class="QUANT"  id="W1" type="number"  min="0" onblur="atualizarTotal()">
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
						<label class="nome">Cachaça Amarela</label>
						<input name="W8" class="QUANT"  id="W8" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Dreher</label>
						<input name="W10" class="QUANT"  id="W10" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<!--
					<div class="conteiner-produto">
						<label class="nome">Buchanan's</label>
						<input name="W3" class="QUANT"  id="W3" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome">Black Label</label>
						<input name="W2" class="QUANT"  id="W2" type="number"  min="0" onblur="atualizarTotal()">
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
						<label class="nome">Cachaça Empalhada</label>
						<input name="W9" class="QUANT"  id="W9" type="number"  min="0" onblur="atualizarTotal()">
					</div>
					-->
				</div>

				
				<hr>

			<!--CAMPOS DOS ACOMPANHANTES-->	

				<h1 class="itens">Carnes:</h1>
				<div class="conteiner_acomp">
					<div class="conteiner-produto">
						<label class="nome" for="acomp">Gado</label>
						<input class="QUANT"  type="number" id="C1" name="C1" min="0" placeholder="gramas" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome" for="acomp">Porco</label>
						<input class="QUANT"  type="number" id="C2" name="C2" min="0" placeholder="gramas" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome" for="acomp">Frango</label>
						<input class="QUANT"  type="number" id="C3" name="C3" min="0" placeholder="gramas" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome" for="acomp">Linguiça</label>
						<input class="QUANT"  type="number" id="C4" name="C4" min="0" placeholder="gramas" onblur="atualizarTotal()">
					</div>
				</div>
				
				<hr>


				<!--CAMPOS DOS ACOMPANHANTES-->	

				<h1 class="itens">Acompanhante:</h1>
				<div class="conteiner_acomp">
				<div class="conteiner-produto">
					<label class="nome" for="acomp" style="display:none;">Feijão</label>
					<h2>Baião </h2>
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
						<label class="nome" for="acomp">Macaxeira</label>
						<input class="QUANT"  type="number" id="P1" name="P1" min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome" for="acomp">Batata</label>
						<input class="QUANT"  type="number" id="P2" name="P2" min="0" onblur="atualizarTotal()">
					</div>
					<div class="conteiner-produto">
						<label class="nome" for="acomp">Filé trinchado</label>
						<input class="QUANT"  type="number" id="P3" name="P3" min="0" onblur="atualizarTotal()">
					</div>
				</div>

			<!--CAMPOS DOS DE ADIÇÃO MANUAL -->	

				<h1 class="itens">Item manual:</h1>
				<div class="conteiner_acomp">
					<div class="conteiner-produto" style="display: flex; flex-direction: row; gap: 30px; width: auto; justify-content: center;">
						<span>
							<h3> Nome: </h3>
							<input class="QUANT" type="text" name="Man_item" style="width: 250px">
						</span>
						<span>
							<h3> Quantidade: </h3>
							<input class="QUANT"  type="number" id="Man_qtd" name="Man_qtd" min="0">
						</span>
						<span>
							<h3> Preço: </h3>
							<input class="QUANT"  type="number" id="Man_preco" name="Man_preco" min="0">
						</span>
						<br>
						<button id="butao_enviar_manual" type="button" onclick="adicionarManual()">Adicionar manualmente</button>
					</div>
				</div>
				<br><br><br><br>


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
    E1: 20.00, E2: 40.00, E3: 60.00, E4: 80.00, E5: 100.00, E6: 6.00, E7: 8.00, E8: 15.00, E9: 17.00,
    B1: 10.00, B2: 8.00, B3: 7.00, B4: 3.50, B5: 5.00, B6: 3.00, B7: 3.00, B8: 2.00, B9: 3.00,
    B10: 17.00, B11: 13.00, B12: 10.00, B13: 10.00, B14: 9.50, B15: 10.00, B16: 10.00, B17: 8.00, B18: 10.00, 
    B19: 9.00, B20: 12.00, B21: 5.00, B22: 10.00, B23: 5.00, B24: 13.00, B25: 13.00, B26: 10.00,
    W1: 12.00, W2: 10.00, W3: 10.00, W4: 10.00, W5: 15.00, W6: 10.00, W7: 10.00, W8: 2.00, W9: 10.00, W10: 4.00,
    C1:60.00, C2: 50.00, C3: 40.00, C4: 30.00,
    A1:8.00, A2: 9.00,
    P1: 15.00, P2: 15.00, P3: 30.00
};

function atualizarHiddenTotal() {
    let totalText = document.getElementById("total").textContent;
    let totalValue = totalText.replace("Total: R$ ", "").trim();
    totalValue = parseFloat(totalValue.replace(',', '.')) || 0;
    document.getElementById("total_input").value = totalValue.toFixed(2);
}

// Atualiza o total visível na tela e no input hidden
function atualizarTotal() {
    let total = 0;

    document.querySelectorAll(".QUANT").forEach(input => {
        const id = input.id;
        const quantidade = parseFloat(input.value) || 0;
        const preco = precos[id] || 0;

        total += quantidade * preco; // soma simples, sem conversões
    });

    document.getElementById("total").textContent = `Total: R$ ${total.toFixed(2)}`;
    document.getElementById("total_input").value = total.toFixed(2);
}

// Prepara os itens selecionados para envio ao backend
function getItensSelecionados() {
    const itensSelecionados = [];

    document.querySelectorAll('.conteiner-produto').forEach(produto => {
        const input = produto.querySelector('input');
        const label = produto.querySelector('label') || produto.querySelector('h2');

        if (input && label) {
            const quantidade = parseFloat(input.value) || 0;

            if (quantidade > 0) {
                const id = input.id;
                const preco_unitario = precos[id] || 0;

                itensSelecionados.push({
                    nome: label.textContent.trim(),
                    quantidade: quantidade,         // envia exatamente o que o usuário digitou
                    preco_unitario: preco_unitario,
                    subtotal: quantidade * preco_unitario
                });
            }
        }
    });

    document.getElementById("itens_selecionados_input").value = JSON.stringify(itensSelecionados);
}
</script>

			

</body>
</html>



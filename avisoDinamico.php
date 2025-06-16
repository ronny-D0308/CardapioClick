<?php
function AvisoDinamico($mensagem, $corBack, $tempo = 3000) { ?>
	<style>
		#avisoDinamico {
			margin: 0 auto;
			width: 50%;
			height: 30px;
			background-color: <?= $corBack ?>;
			text-align: center;
			color: #FFF;
			font-family: arial;
			border-radius: 10px;

			display: flex;
			align-items: center;
			justify-content: center;

			position: absolute;
			top: 15px;
			left: 0;
			right: 0;
			z-index: 9999;
			transition: opacity 0.5s ease;
			padding: 5px;
		}

		#avisoDinamico h3 {
			margin: 0;
			font-size: 16px;
		}
	</style>

	<div id="avisoDinamico">
		<h3><?= htmlspecialchars($mensagem) ?></h3>
	</div>

	<script>
		// Espera o tempo definido e oculta o aviso
		setTimeout(function() {
			const aviso = document.getElementById('avisoDinamico');
			if (aviso) {
				aviso.style.opacity = '0';
				setTimeout(() => aviso.remove(), 500); // Remove após o fade-out
			}
		}, <?= $tempo ?>);
	</script>
<?php }
?>

<?php
// INSERIR O VALOR DO FECHAMENTO DO CAIXA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Valorfechamento'])) {
    $valor_digitado = mysqli_escape_string($conn, $_POST['Valorfechamento']);
    $valor_tratado = str_replace(['.', ','], ['', '.'], $valor_digitado);
    $Valorfechamento = floatval($valor_tratado);

    $sql = "UPDATE caixa 
            SET cx_ValorFechamento = $Valorfechamento, cx_DataFechamento = '$dataatual', cx_HoraFechamento = '$hora', cx_Fechado = 'S'
            WHERE cx_DataAbertura = '$dataatual';
            ";
    $query = mysqli_query($conn, $sql);
    if ($query) {
        include 'avisoDinamico.php';
        avisoDinamico("Caixa fechado", "#01B712");
        header("Refresh:2; url=index.php");
    } else {
        include 'avisoDinamico.php';
        avisoDinamico("Erro ao fechar o caixa", "#CB0606");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Cardapioclick.ico" type="image/x-icon">
    <script src="JS_Centraladm/js_modal.js"> </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- ESSENCIAL -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <title>.: Caixa :.</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kreon:wght@300..700&display=swap');
        *{
            font-family: 'Kreon',sans-serif;
            margin: 0;
        }
        body{
            background-color:#da6c22;
            padding: 20px;
        }
        .title {
          text-align: center;
          font-size: 50px;
        }

        .conteiner-relatorio {
            width: 80%;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            border: 2px solid black;
            margin-top: 10%;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 30px 5px;
            justify-content: center;      /* Centraliza o grid no container */
            justify-items: center;        /* Centraliza o conteúdo das células */
            align-items: center;          /* Centraliza verticalmente o conteúdo */
            padding: 10px;
        }

        .comandas {
            width: 70%;
            height: 100px;
            border-radius: 10px;
            background-color: rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
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

        .modal-content {
          background-color: white;
          padding: 20px;
          border-radius: 10px;
          max-width: 600px;
          width: 80%;
          max-height: 80%;
          overflow-y: auto;
        }

        /* Estilo para o modal (janela centralizada) */
        .fimcaixa {
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

        .fimcaixa-content {
          background-color: white;
          padding: 20px;
          border-radius: 10px;
          max-width: 400px;
          width: 80%;
          max-height: 80%;
          overflow-y: auto;
        }
        .fimcaixa-content form {
          display: block;
          text-align: center;
        }
        .fimcaixa-content form input {
          height: 30px;
          font-size: 20px; 
          color: black;
        }
        .fimcaixa-content form button {
          height: 30px;
          font-size: 20px; 
          color: black;
        }

        .div-botao {
            width: 100%;
            text-align:center;
        }

        .close {
            position: absolute;
            top: 10px; /* Ajuste a posição em relação ao topo */
            right: 10px; /* Ajuste a posição em relação à borda direita */
            font-size: 20px;
            cursor: pointer;
            color: red;
            z-index: 10; /* Corrige o z-index para um valor apropriado */
            width: auto; /* Define o tamanho exato do botão, não 100% */
            height: auto; /* Define o tamanho exato do botão, não 100% */
            padding: 5px 10px; /* Opcional: adicione um padding para melhorar o clique */
        }

        .modal-content button {
            width: 150px;
            height: 30px;
            color: white;
            background-color: #da6c22;
        }
        .divFinaliza {
          margin-top: 30px;
          width: 100%;
          text-align: center;
        }
        .divFinaliza button {
          width: 150px;
          height: 40px;
          font-size: 20px;
          border-radius: 10px;
          border: none;
        }

        @media print {
            body * {
                visibility: hidden;
            }
        }

        @media (max-width: 800px),
               (max-width: 600px),
               (max-width: 400px){
          
          /*
          .navBar {
            grid-template-columns: repeat(1, 2fr);
            grid-template-rows: repeat(4, 2fr);
            justify-content: start;
          }

          .navBar ul {
            margin-left: 0;
          }*/

          .conteiner-relatorio {
            margin-top: 30%;
            grid-template-columns: repeat(1, 2fr);
            grid-template-rows: repeat(4, 2fr);
            justify-content: start;
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

    <script>
      $(document).ready(function(){
         $('#valorfechamento').mask('#.##0,00', {reverse: true});
      });
    </script>
</head>
<body>
    <!-- <a class="sair" style="text-decoration:none; " href="Central_adm.php"> <img src="imagens/left.png" width="40px" > </a> -->

    <h1 class="title"> Caixa </h1>


    <!-- MENU | NAVBAR DA PAGINA 
    <nav>
        <ul class="navBar">
            <li> <a class="links" href="Estoq_cadprod.php" > 
                    <img src="imagens/register.png" width="20px">
                    Cadastrar Produto </a> </li>

            <li> <a class="links" href="Estoq_editprod.php" > 
                    <img src="imagens/edit.png" width="20px">
                    Editar Produto </a> </li>
            <li> <a class="links" href="Estoq_removprod.php" > 
                    <img src="imagens/remove.png" width="20px">
                    Remover Produto </a> </li>

            <li> <a class="links" href="Estoq_entradaProd.php" > 
                    <img src="imagens/enter.png" width="20px">
                    Entrada de Estoque </a> </li>
            <li> <a class="links" href="Estoq_consulta.php" > 
                    <img src="imagens/lupa.png" width="20px">
                    Consulta de Estoque </a> </li>
        </ul>
    </nav>
    -->

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

    <!-- MODAL PARA FINALIZAR O CAIXA -->
    <div class="fimcaixa" id="fimcaixa">
      <div class="fimcaixa-content">
          <form action="" method="POST">
              <h1> Fechamento do caixa </h1>
              <input type="text" name="Valorfechamento" id="valorfechamento">
              <button type="submit" name=""> Fechar </button>
          </form>
      </div>
    </div>

    <!-- RELATORIOS DO ESTOQUE PARA ATUALIZAÇÃO (ESTOQUE EM FALTA, PERTO DE ACABAR) -->
    <div class="conteiner-relatorio">

        <?php
            $sql_geral = "SELECT *
                          FROM vendas
                          WHERE ven_Finalizada <> 'S'
                          GROUP BY ven_Mesa
                          ORDER BY ven_Mesa;
                          ";
            $query_geral = mysqli_query($conn, $sql_geral);

            if (mysqli_num_rows($query_geral) > 0) {              
              while($linhas = mysqli_fetch_object($query_geral)) {

                  echo "<div class='comandas' id='". $linhas->ven_Mesa ."' >
                          <h1>Comanda: ". $linhas->ven_Mesa ."</h1>
                        </div>";
              }
            } else {
                echo "<h2> Sem comandas fechadas </h2>";
            }
        ?>

        <!-- FINALIZAR O CAIXA -->
      </div>
      <div class="divFinaliza"> <button type="button" id="botaofim"> Finalizar caixa </button> </div>

<script type="text/javascript">
    
    const comandas = document.querySelectorAll('.comandas');
    const closeComandaBtn = document.getElementById('close-comanda');
    const closeBtn = document.querySelector('.close');
    const modalContent = document.getElementById('modal-content-body');
    const totalValue = document.getElementById('total-value');
    const addItemBtn = document.getElementById("add-item");
    const botaofim = document.getElementById("botaofim");

    comandas.forEach(comanda => {
      comanda.addEventListener('click', function () {
        const comandaId = comanda.id;
        selectedComanda = comanda;
        //console.log(selectedComanda);
        //console.log(comandaId);

        fetch(`verificar_comanda.php?mesa=${comandaId}`)
          .then(response => response.json())
          .then(data => {
            //console.log("retornou");
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
                    <td style="text-align: right;"> 
                      <img class='remover-item' src='imagens/remove.png' width='20px' data-nome='${nome}' data-mesa='${primeiroItem.ven_Mesa}'>
                    </td>
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

    closeComandaBtn.addEventListener('click', function () {
      const venMesa = parseInt(document.getElementById('venMesa').value);
      
      // Coleta os dados dos itens da comanda
      const itens = itensDaComandaAtual.map(item => ({
        nome: item.nome,
        quantidade: item.quantidade
      }));

      if (confirm("Deseja realmente finalizar e imprimir esta comanda?")) {

          // Cria o overlay
          const overlay = document.createElement('div');
          overlay.style.position = 'fixed';
          overlay.style.top = '0';
          overlay.style.left = '0';
          overlay.style.width = '100%';
          overlay.style.height = '100%';
          overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
          overlay.style.zIndex = '999'; // Abaixo do modal, acima do resto da página
          document.body.appendChild(overlay);

              // Cria um modal simples com select para forma de pagamento
          const modal = document.createElement('div');
          modal.style.position = 'fixed';
          modal.style.top = '50%';
          modal.style.left = '50%';
          modal.style.transform = 'translate(-50%, -50%)';
          modal.style.backgroundColor = 'white';
          modal.style.padding = '20px';
          modal.style.border = '1px solid #ccc';
          modal.style.boxShadow = '0 0 10px rgba(0,0,0,0.5)';
          modal.style.zIndex = '1000';
          modal.style.display = 'flex';
          modal.style.flexDirection = 'column'; // Organiza os itens em coluna
          modal.style.justifyContent = 'center'; // Centraliza horizontalmente os itens internos
          modal.style.alignItems = 'center'; // Corrige o erro de digitação, centraliza verticalmente os itens internos
          modal.style.width = '300px'; // Define uma largura fixa para consistência
          modal.style.height = '250px';
          modal.style.borderRadius = '8px'; // Opcional: bordas arredondadas para melhor estética
          modal.innerHTML = `
              <h4 style='color:black;'>Selecione a Forma de Pagamento</h4>
              <select id="formaPagamento">
                  <option value="">Selecione...</option>
                  <option value="dinheiro">Dinheiro</option>
                  <option value="credito">Cartão de Crédito</option>
                  <option value="debito">Cartão de Débito</option>
                  <option value="pix">PIX</option>
              </select>
              <br><br>
              <button class='botoesformapag' onclick="confirmarPagamento()">Confirmar</button>
              <button class='botoesformapag' onclick="cancelarPagamento()">Cancelar</button>
          `;
          document.body.appendChild(modal);

          // Funções globais para confirmar ou cancelar (devem ser acessíveis no escopo da página)
          window.confirmarPagamento = function() {
              const formaPag = document.getElementById('formaPagamento').value;
              if (formaPag) {

                  // Envia os dados para o servidor
                  fetch(`fechar_comanda.php?venMesa=${venMesa}&formapag=${encodeURIComponent(formaPag)}`, {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ itens })
                  })
                  .then(response => response.json())
                  .then(data => {
                    if (data.sucesso) {
                      modal.style.display = 'none';
                      window.location.reload(); // Recarrega a página corretamente
                    } else {
                      alert('Erro ao fechar a comanda: ' + data.erro);
                    }
                  })
                  .catch(error => {
                    console.error('Erro na requisição:', error);
                  });
              }
          }

          window.cancelarPagamento = function() {
              document.body.removeChild(modal);
              //alert("Operação cancelada.");
              window.location.reload();
          };
      }
    });

    closeBtn.addEventListener('click', function() {
      //alert("fechou a comanda");
      modal.style.display = 'none';
    });

    botaofim.addEventListener('click', function() {
      //fimcaixa.classList.add('fimvisivel');
      fimcaixa.style.display = 'flex';
    });


    modalContent.addEventListener('click', function (event) {
        if (event.target.classList.contains('remover-item')) {
            const itemNome = event.target.getAttribute('data-nome');
            const mesaId = event.target.getAttribute('data-mesa');
        
            if (!itemNome || !mesaId) {
                alert('Erro: nome ou mesa não informado.');
                return;
            }
          
            // Exibe o prompt para o usuário digitar a justificativa
            const justificativa = prompt(`Informe o motivo para remover o item "${itemNome}":`);
          
            if (justificativa) {
                // Caso o usuário preencha, envia os dados para `registrajustificativa.php`
                fetch(`registrajustificativa.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded' // Tipo de dados compatível com POST
                    },
                    body: `justificativa=${encodeURIComponent(justificativa)}&item=${encodeURIComponent(itemNome)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        // Exibe no console que a justificativa foi registrada
                        console.log('Justificativa registrada com sucesso.');
                    } else {
                        // Exibe mensagem de erro caso algo dê errado
                        alert('Erro ao registrar justificativa: ' + data.erro);
                    }
                })
                .catch(error => {
                    console.error('Erro ao enviar justificativa:', error);
                });
              
                // O fluxo principal segue e tenta remover o item mesmo após enviar a justificativa
                fetch(`Central_finalizarComanda.php?mesa=${mesaId}&item=${encodeURIComponent(itemNome)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.sucesso) {
                            window.location.reload(); // Recarrega a página após sucesso
                        } else {
                            alert("Erro ao remover item: " + data.mensagem);
                        }
                    })
                    .catch(err => console.error("Erro na requisição:", err));
            } else {
                // Caso o usuário cancele o prompt ou não preencha a justificativa
                alert("Ação cancelada: é necessário fornecer uma justificativa para remover o item.");
            }
        }
    });

    addItemBtn.addEventListener('click', function () {
        if (selectedComanda && selectedComanda.id) { // Verifica se tem uma comanda selecionada válida
            const comandaId = selectedComanda.id; // Pega o ID da comanda selecionada
            //console.log(`Adicionando itens para a comanda: ${comandaId}`);

            // Redireciona para a página de adicionar itens para essa comanda
            window.location.href = `Adicionar_itens.php?comandaId=${comandaId}`;
        } else {
            alert("Nenhuma comanda selecionada! Por favor, selecione uma comanda antes de adicionar itens.");
        }
    });

</script>

</body>
</html>

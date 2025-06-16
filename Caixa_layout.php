<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Cardapioclick.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- ESSENCIAL -->
    <title>.: Estoque :.</title>
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
        nav {
            width: 100%;
            height: 40px; 
        }
        .navBar {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
            margin: 0 auto;     /* centraliza horizontalmente */
            box-sizing: border-box;
        }
        .navBar li {
            font-size: 20px;
            list-style-type: none;
            width: auto;
            height: auto;
            cursor: pointer;
        }
        .links{
            text-decoration: none;
            color: black;
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
            justify-content: center;      /* Centraliza o grid no container */
            justify-items: center;        /* Centraliza o conteúdo das células */
            align-items: center;          /* Centraliza verticalmente o conteúdo */
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

        .div-botao {
            width: 100%;
            text-align:center;
        }

        .close {
          position: absolute;
          top: 10px;
          right: 10px;
          font-size: 20px;
          cursor: pointer;
          color: red;
          width: 100%;
          height: 100%;
        }

        .modal-content button {
            width: 150px;
            height: 30px;
            color: white;
            background-color: #da6c22;
        }

        @media print {
            body * {
                visibility: hidden;
            }
        }

        @media (max-width: 800px),
               (max-width: 600px),
               (max-width: 400px){

          .navBar {
            grid-template-columns: repeat(1, 2fr);
            grid-template-rows: repeat(4, 2fr);
            justify-content: start;
          }

          .navBar ul {
            margin-left: 0;
          }

          .conteiner-relatorio {
            margin-top: 30%;
          }
        }
    </style>
</head>
<body>
    <a class="sair" style="text-decoration:none; " href="Central_adm.php"> <img src="imagens/left.png" width="40px" > </a>


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


    <!-- RELATORIOS DO ESTOQUE PARA ATUALIZAÇÃO (ESTOQUE EM FALTA, PERTO DE ACABAR) -->
    <div class="conteiner-relatorio">

        <?php
            $sql_geral = "SELECT *
                          FROM vendas
                          WHERE ven_Finalizada <> 'N'
                          GROUP BY ven_Mesa;
                          ";
            $query_geral = mysqli_query($conn, $sql_geral);

            while($linhas = mysqli_fetch_object($query_geral)) {

                echo "<div class='comandas' id='". $linhas->ven_Mesa ."' >
                        <h1>Comanda: ". $linhas->ven_Mesa ."</h1>
                      </div>";
            }
        ?>
    </div>

<script type="text/javascript">
    
    const comandas = document.querySelectorAll('.comandas');
    const closeComandaBtn = document.getElementById('close-comanda');
    const closeBtn = document.querySelector('.close');
    const modalContent = document.getElementById('modal-content-body');
    const totalValue = document.getElementById('total-value');

    comandas.forEach(comanda => {
      comanda.addEventListener('click', function () {
        const comandaId = comanda.id;
        selectedComanda = comanda;
        console.log(selectedComanda);
        console.log(comandaId);

        fetch(`verificar_comanda.php?mesa=${comandaId}`)
          .then(response => response.json())
          .then(data => {
            console.log("retornou");
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

    closeComandaBtn.addEventListener('click', function () {
      const venMesa = parseInt(document.getElementById('venMesa').value);
      
    
      // Coleta os dados dos itens da comanda
      const itens = itensDaComandaAtual.map(item => ({
        nome: item.nome,
        quantidade: item.quantidade
      }));
    
      // Envia os dados para o servidor
      fetch(`fechar_comanda.php?venMesa=${venMesa}`, {
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
    });

    closeBtn.addEventListener('click', function() {
      modal.style.display = 'none';
    });

</script>

</body>
</html>

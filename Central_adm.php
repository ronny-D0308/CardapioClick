<?php
    error_reporting(0);       // Desativa todos os relatórios de erro
    ini_set('display_errors', 0);  // Garante que os erros não sejam exibidos na tela
    
    //include('forcar_erros.php');
    include('config.php');
	session_start();

	if(!isset($_SESSION['usuario'])) {
		header('Location:index.php');
		exit;
    }

    $flag = isset($_GET['flag']) ? $_GET['flag'] : '';

    if ($flag == 'pagar') {
        $venMesa = intval($_GET['venMesa']);
        
        // Se vier via GET (um único pagamento simples)
        $formaPag = isset($_GET['formapag']) ? htmlspecialchars(trim($_GET['formapag'])) : '';
    
        // Se vier via POST (vários pagamentos)
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $pagamentos = $data['pagamentos'] ?? [];
    
        if ($venMesa <= 0) {
            http_response_code(400);
            echo "ID da comanda inválido.";
            exit;
        }
    
        $pagamentoStr = '';
    
        // 1️⃣ — Se vierem múltiplos pagamentos via POST
        if (!empty($pagamentos)) {
            $parts = [];
            foreach ($pagamentos as $pg) {
                $forma = ucfirst(htmlspecialchars(trim($pg['forma'] ?? '')));
                $valor = number_format(floatval($pg['valor'] ?? 0), 2, ',', '');
                if ($forma && $valor > 0) {
                    $parts[] = "{$forma}: {$valor}";
                }
            }
            $pagamentoStr = implode(' | ', $parts);
        } 
        // 2️⃣ — Caso contrário, pega o único método do GET
        elseif (!empty($formaPag)) {
            $pagamentoStr = ucfirst($formaPag);
        } 
        else {
            http_response_code(400);
            echo "Forma de pagamento inválida.";
            exit;
        }
    
        // Atualiza no banco
        $sql_upd = "UPDATE vendas 
                    SET ven_Finalizada = 'S', ven_Formapag = ? 
                    WHERE ven_Mesa = ? AND ven_Finalizada = 'N'";
        $stmt = $conn->prepare($sql_upd);
        $stmt->bind_param("si", $pagamentoStr, $venMesa);
        $stmt->execute();
    
        if ($stmt->affected_rows > 0) {
            echo "Comanda finalizada com sucesso!";
        } else {
            http_response_code(500);
            echo "Erro ao finalizar comanda ou comanda já finalizada.";
        }
    
        $stmt->close();
    } elseif ($flag == 'Deletacomanda') {
        $mesa = intval($_POST['mesa'] ?? $_GET['mesa'] ?? 0);
        $delete = "DELETE FROM vendas WHERE ven_Mesa = $mesa AND ven_Finalizada <> 'S'";
        // echo $delete;
        $querydelete = mysqli_query($conn, $delete);
    }
?>


<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Cardapioclick.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>jQuery.noConflict();</script>
    <script src="https://cdn.anychart.com/releases/v8/js/anychart-core.min.js" type="text/javascript"></script>
    <script src="https://cdn.anychart.com/releases/v8/js/anychart-pie.min.js" type="text/javascript"></script>
    <script src="JS_Centraladm/js_modal.js"> </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>.: Central :.</title>

<!--ESTILIZAÇÃO DA PÁGINA-->
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
         .container {
           display: flex;
           justify-content: center;
           align-items: center;
        }
        .menu {
           position: relative;
           width: 60px;
           height: 60px;
           display: flex;
           overflow: hidden;
           justify-content: space-evenly;
           align-items: center;
           border-radius: 50px;
           box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .menu div {
           padding-left: 10px;
        }
        .menu:hover {
            padding: 2px;
           width: 450px;
           transition-duration: 2s;
        }
        a {
            text-decoration: none;
            font-size: 20px;
            color: black;
        }
        .title{
            font-size: 50px;
            color: white;
            text-align: center;
            margin: 30px 0px 40px 0px;
            letter-spacing: 5px;
        }
        h3{
            font-size:40px;
            color: white;
            text-align: center;
            margin: 30px 0px 40px 0px;
            letter-spacing: 5px;
        }
        
        .conteiner-tabela{
            margin: 0 auto;
        }
        .sessão{
            margin-bottom:200px;
        }
        #imprimir{
            text-align:center;
            margin:auto 0;
            margin-top:30px;
        }
        #imprimir button {
            width: 180px;
            height: 30px;
            cursor: pointer;
            border-radius: 10px;
        }
        .butoes{
            text-align:center;
            margin-top:30px;
        }
        .conteiner-table{
            display:flex;
            justify-content:center; 
            margin-top: 40px;
        }
        .table{
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px 15px 0 0;
            margin: 0 auto;
            width: 600px;
            text-align: center;
            font-size:20px;
        }
        table th{
            color: white;
            text-decoration: underline;
        }
        .acoes{
            text-decoration:none;
            color: white;
            margin-left:10px;
            font-weight: bold;
        }
        canvas {
            max-width: 1000px; /* Define um tamanho máximo para o gráfico */
            margin: 20px auto;
            display: block;
            background: #f9f9f9; /* Fundo claro */
            border-radius: 10px; /* Bordas arredondadas */
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2); /* Sombra suave */
        }

        .form-ana {
            text-align: center;
            margin-bottom: 20px;
        }

        .input-ana, select {
            padding: 8px;
            margin: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .input-ana[type="submit"] {
            background: #28a745;
            color: white;
            cursor: pointer;
            border: none;
        }

        .input-ana[type="submit"]:hover {
            background: #218838;
        }

        /* Destaque para a linha selecionada */
        .selecionada {
            background-color: #d3f9d8 !important;
        }
        .modal {
          display: none;
          position: fixed;
          z-index: 1000;
          left: 0;
          top: 0;
          width: 100vw;
          height: 100vh;
          overflow: auto;
          background-color: rgba(0,0,0,0.5); /* fundo escurecido */
          justify-content: center;
          align-items: center;
        }
        
        .modal-content {
          background-color: #fff;
          margin: auto;
          padding: 20px;
          border-radius: 6px;
          max-width: 600px;
          width: 90%;
          position: relative;
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

        @media print {
            body * {
                visibility: hidden;
            }
            #tabela, #tabela * {
                visibility: visible;
            }
            #tabela{
                position: fixed;
                left:0;
                top:0;
            }
        }

        /*------ STYLE PARA A ANÁLISE DE VENDAS -------*/
        .container-tabela {
            margin: 0 auto;
            width: 100%;
            background: rgba(0, 0, 0, 0.4);
            border-radius: 15px 15px 0 0;
            display: flex;
            flex-direction: row;
            /*flex-wrap: wrap;*/
            justify-content: space-around;
            padding: 10px;
        }
        .filtros {
            display: flex; 
            flex-direction: row;
            justify-content: center;
            gap: 0px 20px;
            align-items: center;
            width: 100%;
            margin: 10px 0px;
            color: white;
        }
        .filtros input {
            width: 150px;
            height: 30px;
            border-radius: 10px;
            font-size: 15px;
        }
        .Card {
            width: auto;
            height: 70px;
            font-size: 25px;
            background-color: white;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3px;
        }
        p {
            font-size: 12px;
        }

        /*----- ESTILO DOS BOTÕES DE FORMA DE PAGAMENTO ----*/
            .botoesformapag {
                background-color: #da6c22;
                color: white; 
                font-size: 15px;
                margin: 5px;
            }

        .botaoProd {
            width: auto;
            height: 30px;
            margin: 5px;
            font-size: 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
    </style>

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            // Certifique-se de que o elemento existe antes de adicionar o listener
            const btnDelete = document.querySelector('.Deletacomanda');
        
            if (btnDelete) {
                btnDelete.addEventListener('click', function () {
                    // Pegue a mesa do input hidden, se necessário
                    const mesa = document.getElementById('venMesa').value;
        
                    // Confirmação
                    if (confirm('Deseja deletar a comanda?')) {
                        // alert(mesa);
                        // Faça a requisição para deletar (AJAX ou redirecionamento)
                        // Exemplo com redirecionamento:
                        window.location.href = `Central_adm.php?flag=Deletacomanda&mesa=${mesa}`;
        
                        // OU, melhor ainda, usando AJAX (fetch):
                        /*
                        fetch(`seuarquivo.php?flag=deletar&venMesa=${mesa}`)
                            .then(resp => resp.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Comanda deletada!');
                                    // Update UI, close modal, etc
                                } else {
                                    alert('Falha ao deletar!');
                                }
                            })
                            .catch(() => alert('Erro ao deletar.'));
                        */
                    }
                });
            }
        });
    </script>

</head>

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
      </div>

        <span style="position: absolute; top: 90%; left: 90%; background-color: #E70A0A; width: 35px; height: auto; display: flex; justify-content: center; align-items: center; cursor:pointer;" 
              class="Deletacomanda" title="Deletar comanda">
            <h1 style="margin:0;color:#fff;font-size:24px;">x</h1>
        </span>

    </div>
</div>


<body>

    <!--LOCAL DE BUSCA DE INFOMAÇÕES DOS CLIENTES-->

    <section class="sessão">
            <div class="container">
                <div class="menu">

                    <div class="icon"><svg class="svg-profile" xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960"
        width="40px" fill="#000000"><path d="M480-480q-66 0-113-47t-47-113q0-66
        47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-112q0-34
        17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5
        43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56
        0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0
        56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5
        56.5T480-560Zm0-80Zm0 400Z" /></svg>
                    </div>

                    <div class="icon"> <a href="Estoque_main.php"> Estoque </a> </div>
                    <div class="icon"> <a href="Cadastro.php"> Cadastro </a> </div>
                    <div class="icon"> <a href="Pedidosremov.php"> Pedidos removidos </a> </div>
                    <div class="icon"> <a href="index.php"> Sair </a> </div>
                </div>
            </div>

            <h1 class="title">CardápioClick</h1>

            <h3>Área Administrativa</h3>

           <div class="conteiner-table">

                <!-- FUNÇÃO JS PARA A DINÂMICA DE SELEÇÃO DE LINHA DA TABELA -->
                <script>
                    let linhaSelecionada = null;
                
                    // Adiciona evento de clique para selecionar linha
                    document.addEventListener('DOMContentLoaded', function () {
                        const linhas = document.querySelectorAll("#tabela tbody tr");
                
                        linhas.forEach(linha => {
                            linha.addEventListener("click", function () {
                                // Remove a seleção anterior
                                if (linhaSelecionada) {
                                    linhaSelecionada.classList.remove("selecionada");
                                }
                                // Marca nova linha
                                this.classList.add("selecionada");
                                linhaSelecionada = this;
                            });
                        });
                    });
                </script>


                <table class="table" id="tabela">
                    <thead>
                        <tr>
                            <th class="coluna">Nome</th>
                            <th class="coluna">Garçon</th>
                            <!-- <th class="coluna">Data</th> --> 
                            <th class="coluna">Valor</th>
                            <th class="coluna">.</th>
                        </tr>
                    </thead>
                    <tbody>
                         
                        <?php
                            // Suponha que você já tem uma consulta SQL para pegar os dados
                            // Exemplo:
                            $sql = "SELECT ven_Seq, ven_Cliente, ven_Garcom, ven_Mesa, SUM(ven_Valor) AS Total, ven_Itens 
                                    FROM vendas 
                                    WHERE ven_Finalizada <> 'S'
                                    GROUP BY ven_Mesa";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    //echo "<tr>";
                                  echo "<tr data-id='".$row['ven_Mesa']."'>";

                                    echo "<td>".$row['ven_Cliente']."</td>";
                                    echo "<td>".$row['ven_Garcom']."</td>";
                                    echo "<td style='display:none;'>". date("d/m/Y") ."</td>";
                                    echo "<td>".number_format($row['Total'], 2, ',', '.')."</td>";
                                    echo "<td style='display:none;'>".$row['ven_Itens']."</td>";

                                    echo "<td><img src='imagens/carton-box.png' width='25px' class='abrir-comanda' data-id='".$row['ven_Mesa']."' style='cursor:pointer;'></td>";

                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>Sem resultados</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Seção de Impressão -->
            <div id="imprimir">
                <button onclick="finalizarEImprimir()">IMPRIMIR COMANDA</button>
            </div>

            <!-- Estilos para a impressão -->
            <style>
                @media print {
                    @page {
                      size: 80mm auto;  /* largura da bobina térmica */
                      margin: 0;
                    }
                
                            body {
                      font-family: monospace;
                      font-size: 10px;
                      margin: 0;
                      padding: 0;
                    }
                
                            #imprimir, .menu, .title, h3 {
                      display: none;  /* esconde elementos visuais desnecessários na impressão */
                    }
                
                    table {
                      width: 100%;
                      border-collapse: collapse;
                      margin: 0 auto;
                      font-size: 10px;
                    }
                
                            th, td {
                      padding: 2px;
                      text-align: left;
                      border-bottom: 1px dashed #000;
                    }
                
                            th {
                      font-weight: bold;
                    }
                
                            h2, h3, h4, p {
                      margin: 4px 0;
                      text-align: center;
                    }
                
                            hr {
                      border: none;
                      border-top: 1px dashed #000;
                      margin: 5px 0;
                    }
                }
            </style>


            <!-- FUNÇÃO DE IMPRESSÃO -->
            <script>
                function finalizarEImprimir() {
                    if (!linhaSelecionada) {
                        alert("Nenhuma comanda selecionada.");
                        return;
                    }
                
                    const venMesa = linhaSelecionada.getAttribute("data-id");
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

// Cria um modal dinâmico com múltiplas formas de pagamento
const modal = document.createElement('div');
modal.style = `
  position: fixed; top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  background: white; padding: 20px;
  border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.5);
  z-index: 1000; width: 350px;
`;
modal.innerHTML = `
  <h4 style="text-align:center; color:black;">Formas de Pagamento</h4>
  <div id="pagamentos"></div>
  <button onclick="adicionarLinhaPagamento()">+ Adicionar</button>
  <hr>
  <button onclick="confirmarPagamento()">Confirmar</button>
  <button onclick="cancelarPagamento()">Cancelar</button>
`;
document.body.appendChild(modal);

window.adicionarLinhaPagamento = function() {
  const container = document.getElementById('pagamentos');
  const linha = document.createElement('div');
  linha.style = "display:flex; gap:5px; margin:5px 0;";
  linha.innerHTML = `
    <select class="formaPag">
      <option value="">Forma...</option>
      <option value="dinheiro">Dinheiro</option>
      <option value="credito">Crédito</option>
      <option value="debito">Débito</option>
      <option value="pix">PIX</option>
    </select>
    <input type="number" class="valorPag" placeholder="Valor" step="0.01" min="0" style="width:80px;">
    <button onclick="this.parentElement.remove()">🗑️</button>
  `;
  container.appendChild(linha);
};

// Adiciona a primeira linha automaticamente
adicionarLinhaPagamento();

window.confirmarPagamento = function() {
  const linhas = document.querySelectorAll('#pagamentos div');
  const pagamentos = [];

  linhas.forEach(l => {
    const forma = l.querySelector('.formaPag').value;
    const valor = parseFloat(l.querySelector('.valorPag').value);
    if (forma && valor > 0) {
      pagamentos.push({ forma, valor });
    }
  });

  if (pagamentos.length === 0) {
    alert('Adicione ao menos uma forma de pagamento.');
    return;
  }

  fetch('Central_adm.php?flag=pagar&venMesa=' + venMesa, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ pagamentos })
  })
  .then(r => {
    if (!r.ok) throw new Error('Erro ao finalizar comanda');
    return r.text();
  })
  .then(() => {
    document.body.removeChild(modal);
    if (!sessionStorage.getItem('recarregado')) {
      sessionStorage.setItem('recarregado', 'true');
      window.location.reload();
    } else {
      sessionStorage.removeItem('recarregado');
    }
  })
  .catch(e => {
    console.error(e);
    alert('Erro ao finalizar comanda.');
  });
};

window.cancelarPagamento = function() {
  document.body.removeChild(modal);
  alert("Operação cancelada.");
  window.location.reload();
};

                    }

                }

                function imprimir() {

                    if (!linhaSelecionada) {
                        alert("Por favor, selecione uma comanda para imprimir.");
                        return;
                    }
                
                    const colunas = linhaSelecionada.querySelectorAll("td");
                    const nomeCliente = colunas[0].innerText;
                    const nomeGarcom = colunas[1].innerText;
                    const dataComanda = colunas[2].innerText;
                    const valorComanda = colunas[3].innerText;

                                //FORMATAR OS ITENS EM TABELA
                    const itensRaw = colunas[4].innerText;
                    let itensComanda;
                    try {
                        itensComanda = JSON.parse(itensRaw);
                    } catch (e) {
                        //alert("Erro ao processar itens da comanda.");
                        return;
                    }

                    let tabelaItens = `
                        <table style="width:100%; border-collapse: collapse; text-align:center;">
                            <thead>
                                <tr>
                                    <th style="border-bottom:1px solid #000; ">Item</th>
                                    <th style="border-bottom:1px solid #000; ">Qtd</th>
                                    <th style="border-bottom:1px solid #000; ">Unit</th>
                                    <th style="border-bottom:1px solid #000; ">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>`;

                    itensComanda.forEach(item => {
                        tabelaItens += `
                            <tr>
                                <td>${item.nome}</td>
                                <td>${item.quantidade}</td>
                                <td>R$ ${Number(item.preco_unitario).toFixed(2).replace('.', ',')}</td>
                                <td>R$ ${Number(item.subtotal).toFixed(2).replace('.', ',')}</td>
                            </tr>`;
                    });

                    tabelaItens += `</tbody></table>`;
                    // FIM DA FORMATAÇÃO DOS ITENS
                
                    const conteudoImpressao = `
                        <div style="width: 72mm; margin: 0 auto; text-align: center;">
                            <h2>Mais Sabor</h2>
                            <p>Endereço: Rua das Flores, 123</p>
                            <p>Telefone: (11) 1234-5678</p>
                            <hr>
                            <h3>Comanda Fechada</h3>
                            <p><strong>Cliente:</strong> ${nomeCliente}</p>
                            <p><strong>Data:</strong> ${dataComanda}</p>
                            <p><strong>Garçom:</strong> ${nomeGarcom}</p>
                            <p><strong>Valor Total:</strong> R$ ${valorComanda}</p>
                            <h4>Itens:</h4> ${tabelaItens}
                            <hr>
                            <p>Obrigado pela preferência!</p>
                        </div>
                    `;
                
                    const janelaImpressao = window.open('', '', 'height=400,width=600');
                    janelaImpressao.document.write('<html><head><title>Comanda Fechada</title></head><body>');
                    janelaImpressao.document.write(conteudoImpressao);
                    janelaImpressao.document.write('</body></html>');
                    janelaImpressao.document.close();
                    janelaImpressao.print();
                    janelaImpressao.close();

                    // imprimirComanda();
                }

                //async function imprimirComanda() {
                //    try {
                //        // Verifica se o servidor está respondendo
                //        const isServerRunning = await fetch('http://localhost:3000/health', {
                //            method: 'GET',
                //            cache: 'no-store'
                //        }).then(res => res.ok).catch(() => false);
                //    
                //        if (!isServerRunning) {
                //            // Tenta iniciar o servidor
                //            const startResponse = await fetch('http://localhost/start-print-server.php', {
                //                method: 'GET',
                //                cache: 'no-store'
                //            });

                //            if (!startResponse.ok) {
                //                throw new Error('Servidor offline - usando impressão alternativa');
                //            }

                //            // Aguarda 3 segundos para o servidor iniciar
                //            await new Promise(resolve => setTimeout(resolve, 3000));
                //        }
                //    
                //        // Continua com a impressão normal
                //        const colunas = linhaSelecionada.querySelectorAll("td");
                //        const comanda = {
                //            mesa: linhaSelecionada.getAttribute('data-id'),
                //            cliente: colunas[0].textContent,
                //            garcom: colunas[1].textContent,
                //            data: colunas[2].textContent,
                //            total: colunas[3].textContent.replace('R$', '').trim(),
                //            itens: JSON.parse(colunas[4].textContent)
                //        };
                //    
                //        const printResponse = await fetch('http://localhost:3000/imprimir', {
                //            method: 'POST',
                //            headers: { 'Content-Type': 'application/json' },
                //            body: JSON.stringify(comanda)
                //        });
                //    
                //        if (!printResponse.ok) {
                //            throw new Error('Falha na impressão');
                //        }
                //        
                //        window.location.reload();
                //        //alert('Comanda impressa com sucesso!');
                //    } catch (error) {
                //        console.error('Erro:', error);
                //        //alert('Usando impressão alternativa: ' + error.message);
                //        imprimir(); // Fallback para impressão pelo navegador
                //    }
                //}
            </script>
        <?php
            // start-print-server.php
            //$output = shell_exec('node C:/wamp64/www/CardapioClick/servidor-impressao.js > print-server.log 2>&1 &');
            //header('Content-Type: application/json');
            //echo json_encode(['success' => true]);
        ?>
    </section>



    <!-------------------------------------------------- LOCAL DE ANÁLISE DE VENDAS  ------------------------------------------------------------>

    <?php
        $consulDataini = $_POST['dataini'] ?? '';
        $consulDatafim = $_POST['datafim'] ?? '';
        $consulFormapag = $_POST['formapag'] ?? '';
        $produtoNome = $_POST['produtoNome'] ?? '';
    ?>
    <section class="sessão">
        <h1 class="title"> Análise de vendas </h1>
        
        <div class="filtros">
            <form action="" method="POST" id="formfiltros">
                <span>
                    <label> De: </label>
                    <input type="date" name="dataini" id="dataini" value='<?php echo htmlspecialchars($consulDataini); ?>'>
                    <label> até: </label>
                    <input type="date" name="datafim" id="datafim" value='<?php echo htmlspecialchars($consulDatafim); ?>'>
                </span>
                <select name="formapag" id="formapag" value='<?php echo htmlspecialchars($consulFormapag); ?>'>
                    <option value="Todos">Todos</option>
                    <option value="credito">Cartão de Crédito</option>
                    <option value="debito">Cartão de Débito</option>
                    <option value="pix">Pix</option>
                    <option value="dinheiro">Dinheiro</option>
                </select>

                <input type="submit" name="" value="Filtrar">
                <input type="button" name="" value="Limpar filtros" onclick="limpar()">
            </form>

        </div>
            <div class="container-tabela">

                <!-- CARTÕES E GRÁFICO DE COLUNAS -->
                <div style="display: flex; flex-direction: row; justify-content: space-between; flex-wrap: wrap; width: 1100px; gap: 10px;">

                    <!--CARD FATURAMENTO TOTAL -->
                    <div class="Card">
                        <?php
                            $consulDataini = $_POST['dataini'] ?? '';
                            $consulDatafim = $_POST['datafim'] ?? '';
                            $consulFormapag = $_POST['formapag'] ?? '';
                            
                            $condicao = '';
                            
                            if (!empty($consulDataini) && !empty($consulDatafim) && !empty($consulFormapag) && $consulFormapag == 'Todos') {
                                $condicao = " WHERE ven_Data BETWEEN '$consulDataini' AND '$consulDatafim'";
                            } elseif (!empty($consulDataini) && !empty($consulDatafim) && !empty($consulFormapag) && $consulFormapag !== 'Todos') {
                                $condicao = " WHERE ven_Data BETWEEN '$consulDataini' AND '$consulDatafim'AND ven_Formapag = '$consulFormapag'";
                            }
                            
                            $sql_BI = "SELECT SUM(ven_Valor) AS Faturamento FROM vendas" . $condicao;
                            $result_BI = $conn->query($sql_BI); 
                            $linhas = mysqli_fetch_object($result_BI);
                            echo "Faturamento Bruto R$ " . number_format($linhas->Faturamento ?? 0, 2, ',', '.');
                        ?>
                    </div>

                    <!--CARD TICKET MÉDIO -->
                    <div class="Card">
                        <?php
                            $sql_BI_tk = "SELECT COUNT(ven_Mesa) AS Mesas FROM vendas". $condicao;
                            $result_BI_tk = $conn->query($sql_BI_tk); 
                            $linhas_tk = mysqli_fetch_object($result_BI_tk);
                            $ticket = 0;
                            $faturamento = !empty($linhas->Faturamento) ? $linhas->Faturamento : 0;
                            $mesas = !empty($linhas_tk->Mesas) ? $linhas_tk->Mesas : 0;
                            
                            if ($mesas > 0) {
                                $ticket = $faturamento / $mesas;
                            } else {
                                $ticket = 0; // Ou pode deixar como null, ou alguma outra tratativa
                            }
                            echo 'Ticket Médio: R$ ' . number_format($ticket, 2, ',', '.');
                        ?>
                    </div>

                    <!--CARD CUSTOS -->
                    <div class="Card" style="width: 280px;">
                        <?php
                            
                            $condicao = '';
                            if (!empty($consulDataini) && !empty($consulDatafim) && !empty($consulFormapag) && $consulFormapag == 'Todos') {
                                $condicao1 = " WHERE ven_Data BETWEEN '$consulDataini' AND '$consulDatafim'";
                                $condicao2 = " WHERE rom_Dataentrada BETWEEN '$consulDataini' AND '$consulDatafim'";
                            } elseif (!empty($consulDataini) && !empty($consulDatafim) && !empty($consulFormapag) && $consulFormapag !== 'Todos') {
                                $condicao1 = " WHERE ven_Data BETWEEN '$consulDataini' AND '$consulDatafim'AND ven_Formapag = '$consulFormapag'";
                                $condicao2 = " WHERE rom_Dataentrada BETWEEN '$consulDataini' AND '$consulDatafim'";
                            }

                            $sql_BI_Des = "SELECT 
                                        (SELECT SUM(ven_Valor) FROM vendas ". $condicao1 .") AS Faturamento,
                                        (SELECT SUM(rom_Preco) FROM romaneio ". $condicao2 .") AS Despesas";
                                //echo $sql_BI_Des;
                            $result_BI_Des = $conn->query($sql_BI_Des); 
                            $linhas_Des = mysqli_fetch_object($result_BI_Des);
                            $Custo = $linhas_Des->Faturamento - $linhas_Des->Despesas;
                            echo "Lucro R$ ". number_format($Custo ,'2',',','.');
                            echo "<p> ". number_format($linhas_Des->Faturamento,'2',',','.') ." - ". number_format($linhas_Des->Despesas,'2',',','.') ." </p>"; 
                        ?>
                    </div>

                    <div class="Card" id="container2" style="width: 100%; height: 325px; border-radius: 10px;"></div>
                    
                    <?php
                        // Faturamento por mês
                        $faturamentos = [];
                        $sql_faturamento = "SELECT DATE_FORMAT(ven_Data, '%Y-%m') AS Mes, SUM(ven_Valor) AS Faturamento 
                                            FROM vendas
                                            ". $condicao1 ." 
                                            GROUP BY Mes 
                                            ORDER BY Mes";
                        $query_faturamento = mysqli_query($conn, $sql_faturamento);
                        while($row = mysqli_fetch_assoc($query_faturamento)){
                            $faturamentos[$row['Mes']] = floatval($row['Faturamento']);
                        }
                        
                        // Despesas por mês
                        $despesas = [];
                        $sql_despesas = "SELECT DATE_FORMAT(rom_Dataentrada, '%Y-%m') AS Mes, SUM(rom_Preco) AS Despesas 
                                         FROM romaneio 
                                         ". $condicao2 ."
                                         GROUP BY Mes 
                                         ORDER BY Mes";
                        $query_despesas = mysqli_query($conn, $sql_despesas);
                        while($row = mysqli_fetch_assoc($query_despesas)){
                            $despesas[$row['Mes']] = floatval($row['Despesas']);
                        }
                        
                        // Juntar meses dos dois arrays:
                        $meses = array_unique(array_merge(array_keys($faturamentos), array_keys($despesas)));
                        sort($meses);
                        
                        $meses_nomes = [
                            '01' => 'Janeiro',
                            '02' => 'Fevereiro',
                            '03' => 'Março',
                            '04' => 'Abril',
                            '05' => 'Maio',
                            '06' => 'Junho',
                            '07' => 'Julho',
                            '08' => 'Agosto',
                            '09' => 'Setembro',
                            '10' => 'Outubro',
                            '11' => 'Novembro',
                            '12' => 'Dezembro'
                        ];

                        $valores = [];
                        foreach($meses as $mes){
                            $ano = substr($mes, 0, 4);
                            $num_mes = substr($mes, 5, 2);
                            $mes_nome = $meses_nomes[$num_mes] . '/' . $ano;
                            $valores[] = [
                                'Mes' => $mes_nome,
                                'Faturamento' => isset($faturamentos[$mes]) ? $faturamentos[$mes] : 0,
                                'Despesas' => isset($despesas[$mes]) ? $despesas[$mes] : 0
                            ];
                        }
                        $json_bi_periodo = json_encode($valores);
                    ?>


                    <script>
                        anychart.onDocumentReady(function () {
                            var phpData = <?php echo $json_bi_periodo; ?>;
                    
                            // Formato: [Mês, Faturamento, Despesas]
                            var dataSet = anychart.data.set(
                                phpData.map(r => [r.Mes, r.Faturamento, r.Despesas])
                            );
                    
                            var faturamentoSeries = dataSet.mapAs({ x: 0, value: 1 });
                            var despesasSeries = dataSet.mapAs({ x: 0, value: 2 });
                    
                            var chart = anychart.column();
                            chart.animation(true);
                            chart.title('Faturamento e Despesas por Mês');
                    
                            var s1 = chart.column(faturamentoSeries);
                            s1.name('Faturamento').fill('#4caf50').stroke('#388e3c');
                    
                            var s2 = chart.column(despesasSeries);
                            s2.name('Despesas').fill('#e53935').stroke('#b71c1c');
                    
                            chart.yAxis().labels().format('R$ {%Value}{groupsSeparator: }');
                            chart.yAxis().title('Valores (R$)');
                            chart.xAxis().title('Mês');
                            chart.xAxis().labels().rotation(-45);
                    
                            chart.legend().enabled(true).fontSize(13).padding([0,0,20,0]);
                            chart.interactivity().hoverMode('single');
                            chart.tooltip().format('R$ {%Value}{groupsSeparator: }');
                            chart.container('container2');
                            chart.draw();
                        });
                    </script>
                </div>

<!-- CARD GRÁFICO DE FORMAS DE PAGAMENTO -->
<div class="Card" id="container" style="width: 400px; height: auto; border-radius: 10px;">
    <?php
        $sql_BI_Fp = "SELECT ven_Formapag 
                      FROM vendas 
                      ". $condicao1 ." 
                      WHERE ven_Formapag IS NOT NULL AND ven_Formapag <> ''";
        $result_BI_Fp = $conn->query($sql_BI_Fp);

        // Array para armazenar os dados de formas de pagamento
        $formasPagamento = [
            'Dinheiro' => 0,
            'Cartão de Crédito' => 0,
            'Cartão de Débito' => 0,
            'PIX' => 0,
            'Não Informado' => 0
        ];

        while ($row = mysqli_fetch_assoc($result_BI_Fp)) {
            $jsonPagamentos = json_decode($row['ven_Formapag'], true);

            if (is_array($jsonPagamentos)) {
                foreach ($jsonPagamentos as $pag) {
                    $forma = $pag['forma'] ?? 'Não Informado';
                    $valor = floatval($pag['valor'] ?? 0);
                    if (!isset($formasPagamento[$forma])) {
                        $formasPagamento[$forma] = 0;
                    }
                    $formasPagamento[$forma] += $valor;
                }
            } else {
                // Caso ven_Formapag não seja JSON, mantemos como "Não Informado"
                $formasPagamento['Não Informado'] += 0;
            }
        }

        // Converte para formato que o AnyChart entende [[forma, valor], ...]
        $dadosChart = [];
        foreach ($formasPagamento as $forma => $valor) {
            $dadosChart[] = [$forma, $valor];
        }

        // Garante que sempre tenha dados
        if (empty($dadosChart)) {
            $dadosChart = [
                ['Cartão de Débito', 0],
                ['Cartão de Crédito', 0],
                ['Pix', 0],
                ['Dinheiro', 0]
            ];
        }
    ?>

    <script>
        var cores = ["#FF6384", "#3D52BB", "#FFCE56", "#34A853", "#F16838"]; // rosa, azul, amarelo, verde
        anychart.onDocumentReady(function () {
            var chart = anychart.pie();
            chart.data(<?php echo json_encode($dadosChart); ?>);
            chart.palette(cores);
            chart.title("Formas de Pagamento");
            chart.container("container");
            const container = document.getElementById("container");
            container.style.borderRadius = '5px';
            chart.draw();
        });
    </script>
</div>

            </div>

                <script type="text/javascript">
                    function limpar() {
                        const form = document.getElementById("formfiltros");
                        form.reset();
                        form.submit();
                    }
                </script>


        <!--------------------------------------------- ANÁLISE MAIS GRANULADA (PRODUTOS) ---------------------------------------------------->
        <div style=" margin: 0 auto; width: 100%; background: rgba(0, 0, 0, 0.4); border-radius: 0 0 15px 15px; display: flex;
                flex-direction: row; flex-wrap: wrap; justify-content: space-around; padding: 10px;">
                <!-- CARTÕES E GRÁFICO DE COLUNAS -->
                <div class="filtros">
                    <form action="" method="POST" id="formProd" style="display: flex; flex-direction: row; justify-content: space-between; flex-wrap: wrap;">
                        <input type="hidden" id="inputProd" name="produtoNome" value='<?php echo htmlspecialchars($produtoNome); ?>'>
                        <input type="hidden" name="dataini" id="dataini" value='<?php echo htmlspecialchars($consulDataini); ?>'>
                        <input type="hidden" name="datafim" id="datafim" value='<?php echo htmlspecialchars($consulDatafim); ?>'>
                        
                        <?php
                            $prod = "SELECT etq_Id, etq_Nome FROM estoque ORDER BY etq_Nome";
                            $query_prod = mysqli_query($conn, $prod);
                            if (mysqli_num_rows($query_prod) > 0) {
                                while ($prodLinha = mysqli_fetch_object($query_prod)) {
                                    $selected = ($produtoNome == $prodLinha->etq_Nome) ? 'active' : '';
                                    echo "<button class='botaoProd $selected' value='". $prodLinha->etq_Nome ."' onclick='consulProd(this)'>". $prodLinha->etq_Nome ."</button>";
                                }
                            }
                        ?>
                    </form>
                </div>
                   


                <?php
                        // ✅ Função para obter componentes de produtos compostos
                        function obterComponentesProduto($nomeProduto) {
                            $produtosCompostos = [
                                'Filé trinchado' => [
                                    ['nome' => 'Batata', 'quantidade' => 250],
                                    ['nome' => 'Gado', 'quantidade' => 250]
                                ]
                                // Adicione outros produtos compostos aqui
                            ];
                            
                            return $produtosCompostos[trim($nomeProduto)] ?? null;
                        }

                        // ✅ Função para calcular custo por grama/unidade
                        function calcularCustoPorUnidade($conn, $nomeProduto, $dataIni, $dataFim) {
                            // Buscar ID do produto
                            $sql_id = "SELECT etq_Id, etq_Categoria FROM estoque WHERE etq_Nome = ?";
                            $stmt = $conn->prepare($sql_id);
                            $stmt->bind_param("s", $nomeProduto);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $produto = $result->fetch_assoc();
                            $stmt->close();
                            
                            if (!$produto) {
                                return 0;
                            }
                            
                            $idProduto = $produto['etq_Id'];
                            $categoria = $produto['etq_Categoria'];
                            
                            // Calcular custo médio ponderado do produto no período
                            $whereData = "";
                            $params = [$idProduto];
                            $types = "i";
                            
                            if (!empty($dataIni) && !empty($dataFim)) {
                                $whereData = "AND rom_Dataentrada BETWEEN ? AND ?";
                                $params[] = $dataIni;
                                $params[] = $dataFim;
                                $types .= "ss";
                            }
                            
                            $sql_custo = "
                                SELECT 
                                    SUM(rom_Quantidade * rom_precounitario) / SUM(rom_Quantidade) AS custo_medio_por_unidade,
                                    SUM(rom_Quantidade) AS quantidade_total_comprada
                                FROM romaneio 
                                WHERE rom_Idproduto = ? $whereData
                                AND rom_Quantidade > 0
                            ";
                            
                            $stmt = $conn->prepare($sql_custo);
                            $stmt->bind_param($types, ...$params);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $custo_data = $result->fetch_assoc();
                            $stmt->close();
                            
                            $custoMedioPorUnidade = $custo_data['custo_medio_por_unidade'] ?? 0;
                            
                            // Para produtos vendidos por gramas (carnes, batata, etc.)
                            if (strtolower($categoria) === 'carnes' || 
                                in_array(strtolower($nomeProduto), ['batata', 'macaxeira'])) {
                                // Converter custo por kg para custo por grama
                                return $custoMedioPorUnidade / 1000; // custo por grama
                            }
                            
                            return $custoMedioPorUnidade; // custo por unidade
                        }

                        // ✅ Função para calcular custo total de produto composto
                        function calcularCustoComposto($conn, $nomeProduto, $quantidadeVendida, $dataIni, $dataFim) {
                            $componentes = obterComponentesProduto($nomeProduto);
                            
                            if (!$componentes) {
                                return 0; // Não é produto composto
                            }
                            
                            $custoTotal = 0;
                            
                            foreach ($componentes as $componente) {
                                $nomeComponente = $componente['nome'];
                                $quantidadePorPorcao = $componente['quantidade']; // em gramas
                                
                                $custoPorGrama = calcularCustoPorUnidade($conn, $nomeComponente, $dataIni, $dataFim);
                                $custoComponente = $custoPorGrama * $quantidadePorPorcao * $quantidadeVendida;
                                
                                $custoTotal += $custoComponente;
                            }
                            
                            return $custoTotal;
                        }

                        // ✅ Preparar variáveis
                        $produtoNome = mysqli_real_escape_string($conn, $_POST['produtoNome'] ?? '');

                        // Condições para as consultas
                        $condicoes = ["v.ven_Finalizada = 'S'"]; // Apenas vendas finalizadas
                        if (!empty($consulDataini) && !empty($consulDatafim)) {
                            $condicoes[] = "v.ven_Data BETWEEN '$consulDataini' AND '$consulDatafim'";
                        }
                        $where = "WHERE " . implode(" AND ", $condicoes);

                        if (!empty($produtoNome)) {
                            // ✅ Consulta principal para obter dados do produto
                            $sql_principal = "
                                SELECT 
                                    SUM(item.quantidade) AS quantidade_total_vendida,
                                    SUM(item.subtotal) AS faturamento_total,
                                    COUNT(DISTINCT v.ven_Mesa) AS total_mesas,
                                    COUNT(DISTINCT v.ven_Seq) AS total_vendas
                                FROM vendas v
                                JOIN JSON_TABLE(
                                    v.ven_Itens,
                                    '$[*]' COLUMNS (
                                        nome VARCHAR(255) PATH '$.nome',
                                        quantidade DECIMAL(10,2) PATH '$.quantidade',
                                        subtotal DECIMAL(10,2) PATH '$.subtotal'
                                    )
                                ) AS item ON 1=1
                                $where
                                AND item.nome = '$produtoNome'
                            ";
                            
                            $result_principal = $conn->query($sql_principal);
                            $dados_produto = mysqli_fetch_object($result_principal);
                            
                            // Valores padrão
                            $quantidadeVendida = $dados_produto->quantidade_total_vendida ?? 0;
                            $faturamentoTotal = $dados_produto->faturamento_total ?? 0;
                            $totalMesas = $dados_produto->total_mesas ?? 0;
                            $totalVendas = $dados_produto->total_vendas ?? 0;
                            
                            // ✅ Calcular custo baseado na quantidade vendida
                            $custoTotal = 0;
                            
                            if ($quantidadeVendida > 0) {
                                // Verificar se é produto composto
                                $componentes = obterComponentesProduto($produtoNome);
                                
                                if ($componentes) {
                                    // Produto composto
                                    $custoTotal = calcularCustoComposto($conn, $produtoNome, $quantidadeVendida, $consulDataini, $consulDatafim);
                                } else {
                                    // Produto simples
                                    $custoPorUnidade = calcularCustoPorUnidade($conn, $produtoNome, $consulDataini, $consulDatafim);
                                    
                                    // Buscar categoria para determinar se é vendido por gramas
                                    $sql_categoria = "SELECT etq_Categoria FROM estoque WHERE etq_Nome = '$produtoNome'";
                                    $result_cat = $conn->query($sql_categoria);
                                    $categoria_data = mysqli_fetch_object($result_cat);
                                    $categoria = $categoria_data->etq_Categoria ?? '';
                                    
                                    if (strtolower($categoria) === 'carnes' || 
                                        in_array(strtolower($produtoNome), ['batata', 'macaxeira'])) {
                                        // Produto vendido por gramas - cada unidade vendida = 250g
                                        $custoTotal = $custoPorUnidade * ($quantidadeVendida * 250);
                                    } else {
                                        // Produto vendido por unidades
                                        $custoTotal = $custoPorUnidade * $quantidadeVendida;
                                    }
                                }
                            }
                            
                            // ✅ Calcular métricas
                            $ticketMedio = ($totalMesas > 0) ? ($faturamentoTotal / $totalMesas) : 0;
                            $lucroTotal = $faturamentoTotal - $custoTotal;
                            $margemLucro = ($faturamentoTotal > 0) ? (($lucroTotal / $faturamentoTotal) * 100) : 0;
                        }
                ?>


                <!--CARD FATURAMENTO TOTAL -->
                <div class="Card">
                    <h3>💰 Faturamento Bruto</h3>
                    <p class="valor-principal">R$ <?php echo number_format($faturamentoTotal ?? 0, 2, ',', '.'); ?></p>
                    <small>Quantidade vendida: <?php echo number_format($quantidadeVendida ?? 0, 0, ',', '.'); ?> unidades</small>
                </div>

                <!--CARD TICKET MÉDIO -->
                <div class="Card">
                    <h3>🎯 Ticket Médio</h3>
                    <p class="valor-principal">R$ <?php echo number_format($ticketMedio ?? 0, 2, ',', '.'); ?></p>
                    <small>Baseado em <?php echo $totalMesas ?? 0; ?> mesas</small>
                </div>

                <!--CARD CUSTOS -->
                <div class="Card" style="width: 300px;">
                    <h3>📊 Custo Total</h3>
                    <p class="valor-principal">R$ <?php echo number_format($custoTotal ?? 0, 2, ',', '.'); ?></p>
                    <small>
                        <?php 
                        if (!empty($produtoNome) && $quantidadeVendida > 0) {
                            $custoPorUnidade = $custoTotal / $quantidadeVendida;
                            echo "Custo por unidade: R$ " . number_format($custoPorUnidade, 2, ',', '.');
                        }
                        ?>
                    </small>
                </div>

                <!--CARD LUCRO -->
                <div class="Card" style="width: 300px;">
                    <h3>💎 Lucro Líquido</h3>
                    <p class="valor-principal <?php echo ($lucroTotal >= 0) ? 'positivo' : 'negativo'; ?>">
                        R$ <?php echo number_format($lucroTotal ?? 0, 2, ',', '.'); ?>
                    </p>
                    <small>
                        Margem: <?php echo number_format($margemLucro ?? 0, 1, ',', '.'); ?>%
                        <br>
                        <span style="font-size: 11px;">
                            <?php echo number_format($faturamentoTotal ?? 0, 2, ',', '.'); ?> - 
                            <?php echo number_format($custoTotal ?? 0, 2, ',', '.'); ?>
                        </span>
                    </small>
                </div>

                <style>
                        .Card {
                            background: white;
                            border-radius: 8px;
                            padding: 20px;
                            margin: 10px;
                            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                            border-left: 4px solid #007bff;
                        }

                        .Card h3 {
                            margin: 0 0 10px 0;
                            color: #333;
                            font-size: 16px;
                        }

                        .valor-principal {
                            font-size: 24px;
                            font-weight: bold;
                            margin: 10px 0;
                            color: #007bff;
                        }

                        .valor-principal.positivo {
                            color: #28a745;
                        }

                        .valor-principal.negativo {
                            color: #dc3545;
                        }

                        .botaoProd.active {
                            background-color: #007bff;
                            color: white;
                        }

                        small {
                            color: #666;
                            font-size: 12px;
                        }
                </style>

                <script type="text/javascript">
                        // ✅ Função para consultar produto
                        function consulProd(botao) {
                            // Previne o comportamento padrão do botão
                            event.preventDefault();
                            
                            // Pega o valor do produto selecionado
                            const produtoNome = botao.value;
                            
                            // Atualiza o campo hidden com o produto selecionado
                            document.getElementById('inputProd').value = produtoNome;
                            
                            // Remove a classe 'active' de todos os botões
                            document.querySelectorAll('.botaoProd').forEach(btn => {
                                btn.classList.remove('active');
                            });
                            
                            // Adiciona a classe 'active' ao botão clicado
                            botao.classList.add('active');
                            
                            // Submete o formulário
                            document.getElementById('formProd').submit();
                        }

                        // ✅ Função alternativa usando AJAX (mais suave, sem recarregar a página)
                        async function consulProdAjax(botao) {
                            event.preventDefault();
                            
                            const produtoNome = botao.value;
                            const dataini = document.getElementById('dataini').value;
                            const datafim = document.getElementById('datafim').value;
                            
                            // Atualiza visualmente os botões
                            document.querySelectorAll('.botaoProd').forEach(btn => {
                                btn.classList.remove('active');
                            });
                            botao.classList.add('active');
                            
                            // Mostra loading
                            const cards = document.querySelectorAll('.Card');
                            cards.forEach(card => {
                                if (card.querySelector('.valor-principal')) {
                                    card.querySelector('.valor-principal').textContent = 'Carregando...';
                                }
                            });
                            
                            try {
                                // Faz requisição AJAX
                                const formData = new FormData();
                                formData.append('produtoNome', produtoNome);
                                formData.append('dataini', dataini);
                                formData.append('datafim', datafim);
                                
                                const response = await fetch('Central_adm.php', {
                                    method: 'POST',
                                    body: formData
                                });
                                
                                if (response.ok) {
                                    // Recarrega a página com os novos dados
                                    window.location.reload();
                                } else {
                                    throw new Error('Erro na requisição');
                                }
                            } catch (error) {
                                console.error('Erro:', error);
                                alert('Erro ao carregar dados do produto');
                            }
                        }
                </script>

        </div>
    </section>
    

    <!--------------------------------------------------- LOCAL DO GRÁFICO DE ATUALIDADES DA EMPRESA ------------------------------------------------------------>
<!--
    <section>
        <h1 class="title">Gestão de equipe</h1>

        <div class="conteiner-grafico">
            <form class="form-ana" action="" method="post">
                <label for="nomeBusca">Nome do Garçom:</label>
                <input class="input-ana" type="text" name="nomeBusca" placeholder="Nome do Garçom">
                
                <label for="tipoBusca">Tipo de análise:</label>
                <select name="tipoBusca">
                    <option value="por-mesa">Por mesa atendida</option>
                    <option value="por-valor">Por valor vendido</option>
                    <option value="ticket">Ticket médio</option>
                </select>
                
                <label for="dataInicio">De:</label>
                <input class="input-ana" type="date" name="dataInicio" required>
                
                <label for="dataFinal">Até:</label>
                <input class="input-ana" type="date" name="dataFinal" required>
                
                <input class="input-ana" type="submit" name="ConsultaGarcom">
            </form>
        </div>

        <div id="container" style="width:100%; height:600px;"></div>  
        
        Área do gráfico 
-->      
        <?php /*
            $labels = [];
            $dados = [];

            if (isset($_POST['ConsultaGarcom'])) {
                // include 'conexao.php';
                $nomeBusca = $_POST['nomeBusca'];
                $tipoBusca = $_POST['tipoBusca'];
                $dataInicio = $_POST['dataInicio'];
                $dataFinal = $_POST['dataFinal'];

                    if ($tipoBusca == "por-mesa") {
                    $sql = "SELECT ven_Data, COUNT(DISTINCT CONCAT(ven_Data, '-', ven_Mesa)) AS total_mesas 
                            FROM vendas 
                            WHERE ven_Garcom = '$nomeBusca'  
                                  AND ven_Data BETWEEN '$dataInicio' AND '$dataFinal' 
                            GROUP BY ven_Data
                            ORDER BY ven_Data ASC";
                } elseif ($tipoBusca == "por-valor") {
                    $sql = "SELECT ven_Data, ven_Garcom, SUM(ven_Valor) AS total_valor 
                            FROM vendas 
                            WHERE ven_Garcom = '$nomeBusca' 
                                  AND ven_Data BETWEEN '$dataInicio' AND '$dataFinal'
                            GROUP BY ven_Data
                            ORDER BY ven_Data ASC";
                } elseif ($tipoBusca == "ticket") {
                    $sql = "SELECT ven_Data, ven_Garcom, COUNT(DISTINCT ven_Mesa) AS total_mesas, SUM(ven_Valor) AS total_venda 
                            FROM vendas 
                            WHERE ven_Garcom = '$nomeBusca' 
                                  AND ven_Data BETWEEN '$dataInicio' AND '$dataFinal' 
                            GROUP BY ven_Data
                            ORDER BY ven_Data ASC";
                }
                    //echo $sql;
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    if ($tipoBusca == "por-mesa") {
                        $totalMesas = 0;
                        while ($row = $result->fetch_assoc()) {
                            $totalMesas += (int)$row['total_mesas'];
                        }
                        echo "<h2 style='text-align:center; color:white;'>Total de mesas atendidas: <strong>$totalMesas</strong></h2>";
                    } else {
                        while ($row = $result->fetch_assoc()) {
                            if ($tipoBusca == "ticket") {
                                $labels[] = date('m/Y', strtotime($row['ven_Data']));
                                $dados[] = round($row['total_venda'] / max($row['total_mesas'], 1), 2);
                            } elseif ($tipoBusca == "por-valor") {

                                $labels[] = date('d/m/Y', strtotime($row['ven_Data']));
                                $dados[] = (float) $row['total_valor'];
                            }
                        }
                    }
                } else {
                    echo "<p style='text-align:center; color:red;'>Nenhum resultado encontrado.</p>";
                }

                $conn->close();
            }
            */
            ?>

            <!-- AnyChart 3D -->
            <script src="https://cdn.anychart.com/releases/v8/js/anychart-base.min.js"></script>
            <script src="https://cdn.anychart.com/releases/v8/js/anychart-cartesian-3d.min.js"></script>

            <?php // if ($tipoBusca !== "por-mesa") : ?>
<!--
                <script>
                    const labels = <?php // echo json_encode($labels); ?>;
                    const dados = <?php // echo json_encode($dados); ?>;
                    const tipoBusca = "<?php // echo $tipoBusca ?? ''; ?>";

                        let titulo = "Resultado";
                    if (tipoBusca === "por-mesa") titulo = "Mesas Atendidas por Garçom";
                    else if (tipoBusca === "por-valor") titulo = "Valor Vendido por Garçom";
                    else if (tipoBusca === "ticket") titulo = "Ticket Médio por Garçom";

                        if (labels.length > 0 && dados.length > 0) {
                        anychart.onDocumentReady(function () {
                            let chart = anychart.column3d();
                            chart.animation(true);
                            chart.title(titulo);

                                let data = labels.map((label, index) => [label, dados[index]]);
                            chart.column(data);

                                chart.tooltip()
                                .position("center-top")
                                .anchor("center-bottom")
                                .offsetX(0)
                                .offsetY(5)
                                .format('${%Value}');

                                chart.yScale().minimum(0);
                            chart.yAxis().labels().format('{%Value}{groupsSeparator: }');
                            chart.xAxis().title("Data");
                            chart.yAxis().title("Valor");

                                chart.container('container');
                            chart.draw();
                        });
                    }
                </script>
-->
            <?php // endif; ?>
    </section>
</body>
</html>

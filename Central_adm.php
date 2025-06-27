<?php
    //error_reporting(0);       // Desativa todos os relatórios de erro
    //ini_set('display_errors', 0);  // Garante que os erros não sejam exibidos na tela

    include('config.php');
	session_start();

	if(!isset($_SESSION['usuario'])) {
		header('Location:Validacao.php');
		exit;
    }

    $flag = isset($_GET['flag']) ? $_GET['flag'] : '';

    if ($flag == 'pagar') {
        // PEGA O VALOR DA MESA E FORMA DE PAGAMENTO
        $venMesa = intval($_GET['venMesa']);
        $formaPag = isset($_GET['formapag']) ? htmlspecialchars(trim($_GET['formapag'])) : ''; // Sanitiza a entrada

        // ATUALIZA A VENDA COM A FORMA DE PAGAMENTO E FINALIZA
        $sql_del = "UPDATE vendas SET ven_Finalizada = 'S', ven_Formapag = ? WHERE ven_Mesa = ? AND ven_Finalizada = 'N'";
        $stmt = $conn->prepare($sql_del);
        $stmt->bind_param("si", $formaPag, $venMesa);
        $stmt->execute();
        $stmt->close();

        // Resposta para o fetch (opcional)
        if ($stmt->affected_rows > 0) {
            echo "Comanda finalizada com sucesso!";
        } else {
            http_response_code(500);
            echo "Erro ao finalizar comanda ou comanda já finalizada.";
        }
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
            width: 80%;
            background: rgba(0, 0, 0, 0.4);
            border-radius: 15px 15px 0 0;
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 0px 10px;
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
            padding: 5px;
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
    </style>

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
                    <div class="icon"> <a href="Validacao.php"> Sair </a> </div>
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
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Cartão de Crédito">Cartão de Crédito</option>
                            <option value="Cartão de Débito">Cartão de Débito</option>
                            <option value="PIX">PIX</option>
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
                            fetch(`Central_adm.php?flag=pagar&venMesa=${venMesa}&formapag=${encodeURIComponent(formaPag)}`)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error("Erro ao finalizar a comanda.");
                                    }
                                    return response.text();
                                })
                                .then(() => {
                                    imprimirComanda();
                                    if (!sessionStorage.getItem('recarregado')) {
                                        sessionStorage.setItem('recarregado', 'true');
                                        window.location.reload();
                                    } else {
                                        sessionStorage.removeItem('recarregado');
                                    }
                                })
                                .catch(error => {
                                    console.error(error);
                                    alert("Erro ao finalizar comanda.");
                                });
                            document.body.removeChild(modal);
                        } else {
                            alert("Por favor, selecione uma forma de pagamento.");
                        }
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
                        alert("Erro ao processar itens da comanda.");
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

                    imprimirComanda();
                }

                async function imprimirComanda() {
                    try {
                        // Verifica se o servidor está respondendo
                        const isServerRunning = await fetch('http://localhost:3000/health', {
                            method: 'GET',
                            cache: 'no-store'
                        }).then(res => res.ok).catch(() => false);
                    
                        if (!isServerRunning) {
                            // Tenta iniciar o servidor
                            const startResponse = await fetch('http://localhost/start-print-server.php', {
                                method: 'GET',
                                cache: 'no-store'
                            });

                            if (!startResponse.ok) {
                                throw new Error('Servidor offline - usando impressão alternativa');
                            }

                            // Aguarda 3 segundos para o servidor iniciar
                            await new Promise(resolve => setTimeout(resolve, 3000));
                        }
                    
                        // Continua com a impressão normal
                        const colunas = linhaSelecionada.querySelectorAll("td");
                        const comanda = {
                            mesa: linhaSelecionada.getAttribute('data-id'),
                            cliente: colunas[0].textContent,
                            garcom: colunas[1].textContent,
                            data: colunas[2].textContent,
                            total: colunas[3].textContent.replace('R$', '').trim(),
                            itens: JSON.parse(colunas[4].textContent)
                        };
                    
                        const printResponse = await fetch('http://localhost:3000/imprimir', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(comanda)
                        });
                    
                        if (!printResponse.ok) {
                            throw new Error('Falha na impressão');
                        }
                    
                        alert('Comanda impressa com sucesso!');
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Usando impressão alternativa: ' + error.message);
                        imprimir(); // Fallback para impressão pelo navegador
                    }
                }
            </script>
        <?php
            // start-print-server.php
            $output = shell_exec('node C:/wamp64/www/CardapioClick/servidor-impressao.js > print-server.log 2>&1 &');
            //header('Content-Type: application/json');
            //echo json_encode(['success' => true]);
        ?>
    </section>



    <!-------------------------------------------------- LOCAL DE ANÁLISE DE VENDAS  ------------------------------------------------------------>
    <section class="sessão">
        <h1 class="title"> Análise de vendas </h1>
        
        <div class="filtros">
            <span>
                <label> De: </label>
                <input type="date" name="dataini" id="dataini">
                <label> até: </label>
                <input type="date" name="datafim" id="datafim">
            </span>
            <select name="formapag" id="formapag">
                <option value="Todos">Todos</option>
                <option value="Cartao">Cartão</option>
                <option value="Pix">Pix</option>
                <option value="Dinheiro">Dinheiro</option>
            </select>

            <select name="produtos" id="produtos">
                <?php
                    $sql_prods = "SELECT etq_Nome, etq_Id FROM estoque WHERE etq_Ativo <> 'N'";
                    $query_prods = mysqli_query($conn, $sql_prods);
                    if (mysqli_num_rows($query_prods) > 0) {
                        echo "<option value='Todos'> Todos </option>";
                        while ($linhas = mysqli_fetch_object($query_prods)) {
                            echo "<option value='". $linhas->etq_Id ."'> ". $linhas->etq_Nome ."</option>";
                        }
                    } else {
                        echo "<option value=''> Nenhum item ativo </option>";
                    }
                ?>
            </select>

        </div>

            <div class="container-tabela">

                <!-- CARTÕES E GRÁFICO DE COLUNAS -->
                <div style="display: flex; flex-direction: row; justify-content: space-between; flex-wrap: wrap; width: 850px; gap: 10px;">
                    <!--CARD FATURAMENTO TOTAL -->
                    <div class="Card">
                    <?php
                        $sql_BI = "SELECT SUM(ven_Valor) AS Faturamento FROM vendas";
                        $result_BI = $conn->query($sql_BI); 
                        $linhas = mysqli_fetch_object($result_BI);
                        echo "Faturamento R$ ". number_format($linhas->Faturamento ,'2',',','.');
                    ?>
                    </div>

                    <!--CARD TICKET MÉDIO -->
                    <div class="Card">
                    <?php
                        $sql_BI_tk = "SELECT COUNT(ven_Mesa) AS Mesas FROM vendas";
                        $result_BI_tk = $conn->query($sql_BI_tk); 
                        $linhas_tk = mysqli_fetch_object($result_BI_tk);
                        $ticket = $linhas->Faturamento / $linhas_tk->Mesas;
                        echo "Ticket Médio ". number_format($ticket, 2, ',', '.');
                    ?>
                    </div>

                    <!--CARD CUSTOS -->
                    <div class="Card" style="width: 280px;">
                    <?php
                        $sql_BI_Des = "SELECT 
                                    (SELECT SUM(ven_Valor) FROM vendas) AS Faturamento,
                                    (SELECT SUM(rom_Preco) FROM romaneio) AS Despesas";
                        $result_BI_Des = $conn->query($sql_BI_Des); 
                        $linhas_Des = mysqli_fetch_object($result_BI_Des);
                        $Custo = $linhas_Des->Faturamento - $linhas_Des->Despesas;
                        echo "Lucro Bruto R$ ". number_format($Custo ,'2',',','.');
                        echo "<p> Receita - custos </p>"; 
                    ?>
                    </div>

                    <div class="Card" id="container2" style="width: 900px; height: 325px; border-radius: 10px;"></div>
                    
                    <?php
                        // Faturamento por mês
                        $faturamentos = [];
                        $sql_faturamento = "SELECT DATE_FORMAT(ven_Data, '%Y-%m') AS Mes, SUM(ven_Valor) AS Faturamento 
                                            FROM vendas GROUP BY Mes ORDER BY Mes";
                        $query_faturamento = mysqli_query($conn, $sql_faturamento);
                        while($row = mysqli_fetch_assoc($query_faturamento)){
                            $faturamentos[$row['Mes']] = floatval($row['Faturamento']);
                        }
                        
                        // Despesas por mês
                        $despesas = [];
                        $sql_despesas = "SELECT DATE_FORMAT(rom_Dataentrada, '%Y-%m') AS Mes, SUM(rom_Preco) AS Despesas 
                                         FROM romaneio GROUP BY Mes ORDER BY Mes";
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
                    // Consulta SQL ajustada para agrupar por forma de pagamento
                    $sql_BI_Fp = "SELECT SUM(ven_Valor) AS Faturamento, ven_Formapag FROM vendas WHERE ven_Finalizada = 'S' GROUP BY ven_Formapag";
                    $result_BI_Fp = $conn->query($sql_BI_Fp);

                    // Array para armazenar os dados de formas de pagamento
                    $formasPagamento = [];
                    while ($row = mysqli_fetch_assoc($result_BI_Fp)) {
                        $forma = $row['ven_Formapag'] ? htmlspecialchars($row['ven_Formapag']) : 'Não Informado';
                        $faturamento = floatval($row['Faturamento']);
                        $formasPagamento[] = [$forma, $faturamento];
                    }
                    // Se não houver dados, definir um array padrão para evitar erros no gráfico
                    if (empty($formasPagamento)) {
                        $formasPagamento = [
                            ['Cartão de Débito', 0],
                            ['Cartão de Crédito', 0],
                            ['Pix', 0],
                            ['Dinheiro', 0]
                        ];
                    }
                    ?>
                    

                    <script>
                        anychart.onDocumentReady(function () {
                            // Cria uma instância de um gráfico de pizza
                            var chart = anychart.pie();
                            // Define os dados dinamicamente a partir do PHP
                            chart.data(<?php echo json_encode($formasPagamento); ?>);
                            // Define o título do gráfico
                            chart.title("Formas de Pagamento");
                            // Define o container do gráfico
                            chart.container("container");
                            const container = document.getElementById("container");
                            container.style.borderRadius = '5px';
                            // Inicia a exibição do gráfico
                            chart.draw();
                        });
                    </script>
                </div>

            </div>
    </section>
    

    <!--------------------------------------------------- LOCAL DO GRÁFICO DE ATUALIDADES DA EMPRESA ------------------------------------------------------------>
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

        <div id="container" style="width:100%; height:600px;"></div> <!-- Área do gráfico -->

        <?php
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
            ?>

            <!-- AnyChart 3D -->
            <script src="https://cdn.anychart.com/releases/v8/js/anychart-base.min.js"></script>
            <script src="https://cdn.anychart.com/releases/v8/js/anychart-cartesian-3d.min.js"></script>

            <?php if ($tipoBusca !== "por-mesa") : ?>
                <script>
                    const labels = <?php echo json_encode($labels); ?>;
                    const dados = <?php echo json_encode($dados); ?>;
                    const tipoBusca = "<?php echo $tipoBusca ?? ''; ?>";

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
            <?php endif; ?>
    </section>
</body>
</html>

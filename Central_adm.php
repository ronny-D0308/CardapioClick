<?php
    error_reporting(0);       // Desativa todos os relatórios de erro
    ini_set('display_errors', 0);  // Garante que os erros não sejam exibidos na tela

    include('config.php');
	session_start();

	if(!isset($_SESSION['usuario'])) {
		header('Location:Validacao.php');
		exit;
	}

	$usuario = $_SESSION["usuario"];

    $flag = isset($_GET['flag']) ? $_GET['flag'] : '';

    if($flag == 'pagar') {

        // PEGA O VALOR DO SEQUÊNCIA DA VENDA
        $venMesa = intval($_GET['venMesa']);
        $sql_del = "UPDATE vendas SET ven_Finalizada = 'S' WHERE ven_Mesa = $venMesa AND ven_Finalizada = 'N'";
            //echo $sql_del;
        $query = mysqli_query($conn, $sql_del);
    }
?>


<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Cardapioclick.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>
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
            margin-bottom:300px;
        }
        #imprimir{
            text-align:center;
            margin:auto 0;
            margin-top:30px;
            cursor: pointer;
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
                        // Usa fetch para fazer a finalização em background, sem redirecionar
                        fetch(`Central_adm.php?flag=pagar&venMesa=${venMesa}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error("Erro ao finalizar a comanda.");
                                }
                                return response.text();
                            })
                            .then(() => {
                                // Após sucesso, chama impressão
                                imprimir();

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
    }

//qz.websocket.connect().then(() => {
//  console.log("✅ Conectado ao QZ Tray");
//  return qz.printers.find();
//}).then(printers => {
//  console.log("🖨️ Impressoras disponíveis:", printers);
//}).catch(err => {
//  console.error("❌ Erro ao conectar:", err);
//});
//
//
//function imprimir() {
//    if (!linhaSelecionada) {
//        alert("Por favor, selecione uma comanda para imprimir.");
//        return;
//    }
//
//    const colunas = linhaSelecionada.querySelectorAll("td");
//    const nomeCliente = colunas[0].innerText;
//    const nomeGarcom = colunas[1].innerText;
//    const valorComanda = colunas[2].innerText;
//
//    const texto = `
//        Comanda Fechada - Mais Sabor
//        Cliente: ${nomeCliente}
//        Garçom: ${nomeGarcom}
//        Total: R$ ${valorComanda}
//
//        Obrigado pela preferência!
//    `;
//
//    qz.websocket.connect().then(() => {
//        return qz.printers.find("Brother DCP-L5652DN Printer"); // ou a EPSON, se preferir
//    }).then(printer => {
//        const config = qz.configs.create(printer);
//        const data = [{ type: 'raw', format: 'plain', data: texto }];
//        return qz.print(config, data);
//    }).then(() => {
//        console.log("Impressão enviada.");
//    }).catch(err => {
//        console.error("Erro ao imprimir:", err);
//    });
//}

            </script>


    </section>



    <!-------------------------------------------------- LOCAL DE BUSCA DE COMANDAS PENDENTES  ------------------------------------------------------------>
    <!--
    <section class="sessão">
        <h1 class="title">Comandas pendentes</h1>
               
                ?php
                    $sql_pen = "SELECT comp_Id, comp_Cliente, comp_Garcom, comp_Valor, comp_Data FROM comandaspendentes";
                    $result_pen = $conn->query($sql_pen); 
                ?>

            <div class="container-tabela">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="coluna" >Nome</th>
                            <th class="coluna" >Garçon</th>
                            <th class="coluna" >Data</th>
                            <th class="coluna" >Valor</th>
                            <th class="coluna" style="display:none;" >Id</th>
                            !-- <th class="coluna" >...</th> --
                        </tr>
                    </thead>
                    <tbody>
                        ?php
                            if ($result_pen === false) {
                                echo "Erro na consulta SQL: " . $conn->error;
                            } else {
                                 if($result_pen->num_rows > 0) {
                                     while($row = $result_pen->fetch_assoc()) {
                                         echo "<tr>";
                                         echo "<td>".$row['comp_Cliente']."</td>";
                                         echo "<td>".$row['comp_Garcom']."</td>";
                                         echo "<td>".$row['comp_Data']."</td>";
                                         echo "<td>". number_format($row['comp_Valor'], 2,',','.') ."</td>";
                                         echo "<td style='display:none;'>".$row['comp_Id']."</td>";
                                         echo "<td>
                                             <a class='acoes' id='Deletar' href='delete.php?compId=$row[comp_Id]' title='Deletar'>
                                                <img src='imagens/remove.png' width='20px'> </a>
                                             !-- <a class='acoes' id='Editar' href='edit.php?compId=$row[comp_Id]' title='Editar'> EDITAR </a>--
                                         </td>";

                                         }
                                 } else {
                                         echo "<tr c><td> 0 resultados </td></tr>";
                                 }
                            }
                        ?>
                    </tbody>
                </table>
            </div>

    </section> -->
    

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

<?php
    $comandaId = !empty($_GET['comandaId']) ? htmlspecialchars($_GET['comandaId']) : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sua Comanda</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .total {
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 20px;
            text-align: right;
        }
        .no-items {
            text-align: center;
            color: #888;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bem-vindo ao Autoatendimento</h1>
        <input type="hidden" id="comandaId" value="<?= $comandaId ?>">
        <div id="modalContent"></div>
        <div id="totalValue" class="total"></div>
    </div>

    <script>
        // Obtém o valor do comandaId do input hidden
        const comandaId = document.getElementById("comandaId").value;
        const modalContent = document.getElementById("modalContent");
        const totalValue = document.getElementById("totalValue");

        // Verifica se comandaId tem valor antes de fazer a requisição
        if (comandaId) {
            fetch(`verificar_comanda.php?mesa=${encodeURIComponent(comandaId)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na resposta do servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    let totalComanda = 0;
                    modalContent.innerHTML = '';

                    if (data.temItens && data.itens && data.itens.length > 0) {
                        const primeiroItem = data.itens[0];
                        let tabelaHTML = `
                            <table>
                                <tr>
                                    <td colspan="2" style="font-weight: bold;">Cliente: ${primeiroItem.Cliente || 'N/A'}</td>
                                    <td colspan="2" style="font-weight: bold; text-align: right;">Garçom: ${primeiroItem.Garcom || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Item</th>
                                    <th style="text-align: center;"></th>
                                    <th style="text-align: right;">Valor Unitário</th>
                                    <th style="text-align: right;">Subtotal</th>
                                </tr>
                        `;

                        // Agrupa os itens por nome
                        const agrupados = {};
                        data.itens.forEach(item => {
                            const nome = item.nome || 'Item';
                            const precoUnitario = item.preco_unitario ?? item.Valor ?? 0;
                            const quantidade = item.quantidade || 1;
                            const subtotal = item.subtotal || (precoUnitario * quantidade);

                            if (!agrupados[nome]) {
                                agrupados[nome] = {
                                    quantidade: quantidade,
                                    preco: precoUnitario,
                                    subtotal: subtotal
                                };
                            } else {
                                agrupados[nome].quantidade += quantidade;
                                agrupados[nome].subtotal += subtotal;
                            }

                            totalComanda = item.ven_Valor || totalComanda;
                        });

                        // Gera as linhas da tabela
                        for (const nome in agrupados) {
                            const item = agrupados[nome];
                            tabelaHTML += `
                                <tr>
                                    <td>${nome}</td>
                                    <td style="text-align: center;"></td>
                                    <td style="text-align: right;">${item.quantidade} X R$ ${item.preco.toFixed(2)}</td>
                                    <td style="text-align: right;">R$ ${item.subtotal.toFixed(2)}</td>
                                </tr>
                            `;
                        }

                        tabelaHTML += `</table>`;
                        modalContent.innerHTML = tabelaHTML;
                        totalValue.innerHTML = `Total: R$ ${totalComanda.toFixed(2)}`;
                    } else {
                        modalContent.innerHTML = `<p class="no-items">Esta comanda não tem itens.</p>`;
                        totalValue.innerHTML = '';
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar a comanda:', error);
                    modalContent.innerHTML = `<p class="no-items">Erro ao carregar os dados da comanda. Tente novamente mais tarde.</p>`;
                    totalValue.innerHTML = '';
                });
        } else {
            modalContent.innerHTML = `<p class="no-items">Nenhuma comanda selecionada. Por favor, forneça um ID válido.</p>`;
            totalValue.innerHTML = '';
        }
    </script>
</body>
</html>
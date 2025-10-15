<?php
header('Content-Type: application/json');
include 'config.php';

$mesa = isset($_GET['mesa']) ? (int)$_GET['mesa'] : 0;
$itemNome = isset($_GET['item']) ? $_GET['item'] : '';

$response = ['sucesso' => false, 'erro' => null];

if ($mesa <= 0 || empty($itemNome)) {
    $response['erro'] = 'Parâmetros inválidos.';
    echo json_encode($response);
    exit;
}

// Tenta buscar o produto no estoque, mas não aborta se não existir
$sqlProduto = "SELECT etq_Id, etq_Categoria FROM estoque WHERE LOWER(etq_Nome) LIKE LOWER(?) LIMIT 1";
$stmt = $conn->prepare($sqlProduto);
$nomeLike = "%$itemNome%";
$stmt->bind_param("s", $nomeLike);
$stmt->execute();
$res = $stmt->get_result();
$rowProduto = $res->fetch_assoc();
$stmt->close();

$idProduto = $rowProduto['etq_Id'] ?? null; // Pode ser null se não existir
$Categoria = $rowProduto['etq_Categoria'] ?? null;

// Buscar vendas não finalizadas da mesa
$sql = "SELECT ven_Seq, ven_Itens, ven_Valor FROM vendas WHERE ven_Mesa = ? AND ven_Finalizada <> 'S'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mesa);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $venSeq = $row['ven_Seq'];
    $venValor = (float)$row['ven_Valor'];
    $itens = json_decode($row['ven_Itens'], true);

    if (!is_array($itens)) continue;

    $alterado = false;
    $novoTotal = $venValor;

    foreach ($itens as $key => &$item) {
        if ($item['nome'] === $itemNome) {
            if ($item['quantidade'] > 1) {
                $item['quantidade'] -= 1;
                $item['subtotal'] = $item['quantidade'] * $item['preco_unitario'];
            } else {
                unset($itens[$key]);
            }

            $novoTotal -= $item['preco_unitario'];
            $alterado = true;
            break; // Remove apenas uma vez por venda
        }
    }

    if ($alterado) {
        // Atualiza JSON e valor da venda
        $novoJson = json_encode(array_values($itens), JSON_UNESCAPED_UNICODE);
        $update = $conn->prepare("UPDATE vendas SET ven_Itens = ?, ven_Valor = ? WHERE ven_Seq = ?");
        $update->bind_param("sdi", $novoJson, $novoTotal, $venSeq);
        $update->execute();
        $update->close();

        // Atualiza estoque apenas se o produto existir
        if ($idProduto) {
            $sqlRom = "SELECT rom_Id, rom_Quantidade FROM romaneio WHERE rom_Idproduto = ? LIMIT 1";
            $stmtRom = $conn->prepare($sqlRom);
            $stmtRom->bind_param("i", $idProduto);
            $stmtRom->execute();
            $stmtRom->bind_result($romId, $qtdAtual);
            if ($stmtRom->fetch()) {
                $stmtRom->close();

                if ($Categoria == "Carnes") {
                    // carnes voltam em gramas
                    $novaQtd = $qtdAtual + 250;
                } elseif ($Categoria == "Destilados") {
                    // destilados voltam em mililitros (padrão de 50ml, por exemplo)
                    $novaQtd = $qtdAtual + 50;
                } else {
                    // demais itens voltam por unidade
                    $novaQtd = $qtdAtual + 1;
                }

                $updateRom = $conn->prepare("UPDATE romaneio SET rom_Quantidade = ? WHERE rom_Id = ?");
                $updateRom->bind_param("ii", $novaQtd, $romId);
                $updateRom->execute();
                $updateRom->close();
            } else {
                $stmtRom->close();
            }
        }

        break; // Atualiza apenas uma venda
    }
}

$response['sucesso'] = true;
echo json_encode($response);

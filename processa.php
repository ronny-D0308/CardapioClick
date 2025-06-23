<?php
include('forcar_erros.php');
include('config.php');
session_start();

// ✅ Verificação de sessão
if (!isset($_SESSION['usuario'])) {
    header('Location: Validacao.php');
    exit;
}

// ✅ Validação de parâmetros obrigatórios
if (
    !isset($_POST['nome_cliente'], $_POST['mesa'], $_POST['total'], $_POST['itens_selecionados']) ||
    !is_array(json_decode($_POST['itens_selecionados'], true))
) {
    die("Parâmetros inválidos ou incompletos.");
}

// 🔧 Dados da comanda
$Cliente = $_POST['nome_cliente'];
$Garcon = $_SESSION['usuario'];
$nivel = $_SESSION['nivel'];
$Mesa = $_POST['mesa'];
$Total = floatval($_POST['total']);
$itensOriginal = json_decode($_POST['itens_selecionados'], true);

$mensagens = [];

$itensParaInserir = []; // <- array só com os itens válidos para a comanda

foreach ($itensOriginal as $item) {
    $nome = $item['nome'];
    $quantidade = $item['quantidade'];

    // Buscar o produto no estoque
    $sql_estoq = "SELECT etq_Id, etq_Categoria FROM estoque WHERE etq_Nome = ?";
    $stmt = $conn->prepare($sql_estoq);
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if (!$row) {
        $mensagens[] = "Produto <strong>$nome</strong> não encontrado no estoque.";
        continue;
    }

    $IdProd = $row['etq_Id'];
    $Categoria = $row['etq_Categoria'];

    // Buscar quantidade disponível no romaneio
    $sql_romaneio = "SELECT rom_Id, rom_Quantidade FROM romaneio 
                     WHERE rom_Idproduto = ? AND rom_Quantidade > 0 
                     ORDER BY rom_Id ASC LIMIT 1";
    $stmt_rom = $conn->prepare($sql_romaneio);
    $stmt_rom->bind_param("i", $IdProd);
    $stmt_rom->execute();
    $res_rom = $stmt_rom->get_result();
    $rom = $res_rom->fetch_assoc();
    $stmt_rom->close();

    if (!$rom) {
        $mensagens[] = "Sem estoque disponível para o item <strong>$nome</strong>.";
        continue;
    }

    $romId = $rom['rom_Id'];
    $quantidade_atual = $rom['rom_Quantidade'];

    // Cálculo de baixa (em gramas para carnes)
    $quantidade_para_baixa = (strtolower($Categoria) === "carnes") ? $quantidade * 250 : $quantidade;

    // Ajusta a quantidade se não houver o suficiente
    if ($quantidade_para_baixa > $quantidade_atual) {
        if (strtolower($Categoria) === "carnes") {
            $quantidade_disponivel = floor($quantidade_atual / 250);
            $quantidade_para_baixa = $quantidade_disponivel * 250;
            $item['quantidade'] = $quantidade_disponivel;
        } else {
            $item['quantidade'] = $quantidade_atual;
            $quantidade_para_baixa = $quantidade_atual;
        }

        $mensagens[] = "Estoque insuficiente para <strong>$nome</strong>. Foi adicionado apenas <strong>{$item['quantidade']}</strong> unidade(s).";
    }

    if ($item['quantidade'] <= 0) continue;

    // Atualiza o romaneio
    $nova_quantidade = max(0, $quantidade_atual - $quantidade_para_baixa);
    $stmt_up = $conn->prepare("UPDATE romaneio SET rom_Quantidade = ? WHERE rom_Id = ?");
    $stmt_up->bind_param("ii", $nova_quantidade, $romId);
    $stmt_up->execute();
    $stmt_up->close();

    // Recalcula corretamente o subtotal com nova quantidade
    $item['subtotal'] = $item['quantidade'] * $item['preco_unitario'];

    $itensParaInserir[] = $item;
    $teste = $item['subtotal'];
}


// 🔍 Buscar venda existente para a mesa
$sqlBusca = "SELECT ven_Seq, ven_Itens, ven_Valor FROM vendas 
             WHERE ven_Mesa = ? AND ven_Finalizada <> 'S' 
             ORDER BY ven_Seq DESC LIMIT 1";
$stmtBusca = $conn->prepare($sqlBusca);
$stmtBusca->bind_param("s", $Mesa);
$stmtBusca->execute();
$result = $stmtBusca->get_result();
$vendaExistente = $result->fetch_assoc();
$stmtBusca->close();


if (count($itensParaInserir) === 0) {
    // Determinar a URL de redirecionamento com base no nível
    $redirectUrl = '';
    if ($nivel == 'Funcio') {
        $redirectUrl = "Comandas.php";
    } elseif ($nivel == 'Admin') {
        $redirectUrl = "Central_adm.php";
    } elseif ($nivel == 'Caixa') {
        $redirectUrl = "Caixa_main.php";
    }

    // Erro e mensagens em HTML com tempo de redirecionamento
    $mensagensHTML = '';
    foreach ($mensagens as $msg) {
        $mensagensHTML .= "<li>$msg</li>";
    }

    // Construir saída HTML utilizando heredoc
    echo <<<HTML
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="refresh" content="8;url=$redirectUrl"> <!-- Redirecionamento -->
            <title>Nenhum item adicionado</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #b88406;
                    padding: 20px;
                    color: white;
                    text-align: center;
                }
                ul {
                    font-size: 18px;
                    text-align: left;
                    margin: 20px auto;
                    display: inline-block;
                }
                h1 {
                    font-size: 22px;
                }
                p {
                    font-size: 16px;
                }
            </style>
        </head>
        <body>
            <h1>Nenhum item pôde ser adicionado:</h1>
            <ul>
                $mensagensHTML
            </ul>
            <p>Redirecionando em 10 segundos...</p>
        </body>
        </html>
        HTML;

    exit();
}

// ✅ Atualiza ou cria a venda
if ($vendaExistente) {
    $venSeq = $vendaExistente['ven_Seq'];
    $itensAntigos = json_decode($vendaExistente['ven_Itens'], true);
    $totalAnterior = floatval($vendaExistente['ven_Valor']);

    foreach ($itensParaInserir as $novoItem) {
        $encontrado = false;
        foreach ($itensAntigos as &$itemAntigo) {
            if ($itemAntigo['nome'] === $novoItem['nome']) {
                $itemAntigo['quantidade'] += $novoItem['quantidade'];
                $itemAntigo['subtotal'] += $novoItem['subtotal'];
                $encontrado = true;
                break;
            }
        }
        if (!$encontrado) {
            $itensAntigos[] = $novoItem;
        }
    }

    $novoTotal = $teste + $totalAnterior;

    $itensJson = json_encode($itensAntigos, JSON_UNESCAPED_UNICODE);

    $stmtUpdate = $conn->prepare("UPDATE vendas SET ven_Itens = ?, ven_Valor = ? WHERE ven_Seq = ?");
    $stmtUpdate->bind_param("sdi", $itensJson, $novoTotal, $venSeq);
    $stmtUpdate->execute();
    $stmtUpdate->close();
} else {
    $itensJson = json_encode($itensParaInserir, JSON_UNESCAPED_UNICODE);
    $stmtVenda = $conn->prepare("INSERT INTO vendas (ven_Cliente, ven_Garcom, ven_Valor, ven_Data, ven_Mesa, ven_Itens, ven_Finalizada) 
                                 VALUES (?, ?, ?, NOW(), ?, ?, 'N')");
    if ($stmtVenda) {
        $stmtVenda->bind_param("ssdis", $Cliente, $Garcon, $Total, $Mesa, $itensJson);
        $stmtVenda->execute();
        $stmtVenda->close();
    }
}

if($nivel == 'Funcio') {
    header('Location: Comandas.php');
} elseif ($nivel == 'Admin') {
    header('Location: Central_adm.php');
} elseif ($nivel == 'Caixa') {
    header('Location: Caixa_main.php');
}
?>
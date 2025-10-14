<?php
include('forcar_erros.php');
include('config.php');
session_start();

// Dados do usuário
$Garcon = $_SESSION['usuario'] ?? null;
$nivel = $_SESSION['nivel'] ?? null;

if (!$Garcon) {
    die(json_encode(['erro' => true, 'mensagem' => 'Usuário não autenticado']));
}

// Função de redirecionamento
function obterUrlRedirecionamento($nivel) {
    $urls = [
        'Funcio' => 'Comandas.php',
        'Admin'  => 'Central_adm.php',
        'Caixa'  => 'Caixa_main.php'
    ];
    return $urls[$nivel] ?? 'Comandas.php';
}

// Receber dados do item manual
$nomeItem = trim($_POST['Man_item'] ?? '');
$quantidade = $_POST['Man_qtd'] ?? 0;
$subtotalItem = floatval($_POST['Man_preco'] ?? 0);
$comandaId = intval($_POST['comandaId'] ?? 0);
$nome_cliente = trim($_POST['nome_cliente'] ?? '');

if (!$nomeItem || $subtotalItem <= 0) {
    die(json_encode(['erro' => true, 'mensagem' => 'Dados inválidos.']));
}

// Iniciar transação
$conn->autocommit(false);

try {
    // Buscar comanda aberta
    $stmt = $conn->prepare("SELECT ven_Seq, ven_Itens, ven_Valor FROM vendas WHERE ven_Mesa = ? AND ven_Finalizada <> 'S' ORDER BY ven_Seq DESC LIMIT 1");
    $stmt->bind_param("i", $comandaId);
    $stmt->execute();
    $result = $stmt->get_result();
    $comanda = $result->fetch_assoc();
    $stmt->close();

    $itensArray = [];
    $totalAtual = 0;

    if ($comanda) {
        $venSeq = $comanda['ven_Seq'];
        $itensArray = json_decode($comanda['ven_Itens'], true) ?: [];
        $totalAtual = floatval($comanda['ven_Valor']);
    }

    // Adicionar novo item
    $novoItem = [
        'nome' => $nomeItem,
        'quantidade' => $quantidade,
        'subtotal' => $subtotalItem * $quantidade
    ];
    $itensArray[] = $novoItem;
    $novoTotal = $totalAtual + ($subtotalItem * $quantidade);
    $novoJson = json_encode($itensArray, JSON_UNESCAPED_UNICODE);

    if ($comanda) {
        // Atualizar comanda existente
        $stmtUpdate = $conn->prepare("UPDATE vendas SET ven_Itens = ?, ven_Valor = ? WHERE ven_Seq = ?");
        $stmtUpdate->bind_param("sdi", $novoJson, $novoTotal, $venSeq);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    } else {
        // Criar nova comanda
        $stmtInsert = $conn->prepare("INSERT INTO vendas (ven_Cliente, ven_Garcom, ven_Mesa, ven_Itens, ven_Valor, ven_Data, ven_Finalizada) VALUES (?, ?, ?, ?, ?, NOW(), 'N')");
        $stmtInsert->bind_param("ssisd",$nome_cliente ,$Garcon, $comandaId, $novoJson, $subtotalItem);
        $stmtInsert->execute();
        $stmtInsert->close();
    }

    $conn->commit();

    echo json_encode([
        'erro' => false,
        'mensagem' => 'Item manual adicionado com sucesso!',
        'itens' => $itensArray,
        'total' => $novoTotal,
        'redirect' => obterUrlRedirecionamento($nivel)
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['erro' => true, 'mensagem' => 'Erro ao processar item manual.']);
}

$conn->autocommit(true);
exit();
?>

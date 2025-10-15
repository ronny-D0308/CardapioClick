<?php
include 'config.php';
header('Content-Type: application/json');

$response = [];

$venMesa = isset($_GET['venMesa']) ? (int)$_GET['venMesa'] : 0;
$data = json_decode(file_get_contents('php://input'), true);
$itens = $data['itens'] ?? [];
$pagamentos = $data['pagamentos'] ?? [];

if (!$venMesa || empty($pagamentos)) {
    $response['erro'] = 'ID da comanda ou pagamentos inválidos.';
    echo json_encode($response);
    exit;
}

// Monta string legível para salvar no VARCHAR
$pagamentosStr = [];
foreach ($pagamentos as $p) {
    $forma = ucfirst($p['forma']);
    $valor = number_format((float)$p['valor'], 2, ',', '.');
    $pagamentosStr[] = "{$forma}: ${valor}";
}
$pagamentosFinal = implode(' | ', $pagamentosStr);

// Atualiza venda como finalizada
$sql_upd = "UPDATE vendas 
             SET ven_Finalizada = 'S', ven_Formapag = ? 
             WHERE ven_Mesa = ? AND ven_Finalizada <> 'S'";
$stmt_upd = $conn->prepare($sql_upd);
$stmt_upd->bind_param("si", $pagamentosFinal, $venMesa);


if ($stmt_upd->execute()) {
    $response['sucesso'] = true;
} else {
    $response['erro'] = 'Erro ao atualizar a comanda: ' . $stmt_upd->error;
}

$stmt_upd->close();
$conn->close();

echo json_encode($response);

?>

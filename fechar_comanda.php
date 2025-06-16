<?php
include 'config.php';
header('Content-Type: application/json');

$response = [];

// Obtém o venSeq e valida
$venMesa = isset($_GET['venMesa']) ? (int)$_GET['venMesa'] : 0;
$itens = json_decode(file_get_contents('php://input'), true)['itens'] ?? [];

if (!$venMesa) {
    $response['erro'] = 'ID da comanda (venMesa) inválido.';
    echo json_encode($response);
    exit;
}

// Verifica conexão
if (!$conn) {
    $response['erro'] = 'Erro na conexão com o banco de dados.';
    echo json_encode($response);
    exit;
}

// Verifica se a venda existe
$sql = "SELECT ven_Seq FROM vendas WHERE ven_Mesa = ? AND ven_Finalizada <> 'S' ";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $response['erro'] = 'Erro ao preparar SELECT: ' . $conn->error;
    echo json_encode($response);
    exit;
}

$stmt->bind_param("i", $venMesa);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // Venda não encontrada
    $response['erro'] = 'Nenhuma comanda encontrada com esse ID.';
    echo json_encode($response);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Atualiza a venda como finalizada
$sql_upd = "UPDATE vendas SET ven_Finalizada = 'S' WHERE ven_Mesa = ?";
$stmt_upd = $conn->prepare($sql_upd);
if (!$stmt_upd) {
    $response['erro'] = 'Erro ao preparar UPDATE: ' . $conn->error;
    echo json_encode($response);
    exit;
}

$stmt_upd->bind_param("i", $venMesa);
if (!$stmt_upd->execute()) {
    $response['erro'] = 'Erro ao fechar a comanda.';
    echo json_encode($response);
    $stmt_upd->close();
    $conn->close();
    exit;
}

$stmt_upd->close();
$conn->close();

$response['sucesso'] = true;
$response['mensagem'] = 'Comanda fechada e romaneio atualizado com sucesso.';
echo json_encode($response);
?>

<?php
include 'config.php';
header('Content-Type: application/json');

$response = ['bloquear' => false];

// Lê o JSON enviado
$input = json_decode(file_get_contents('php://input'), true);
$nomeItem = $input['item'] ?? '';

if (!$nomeItem) {
    $response['erro'] = 'Nome do item não recebido.';
    echo json_encode($response);
    exit;
}

// Busca o ID do produto pelo nome
$sql_estoque = "SELECT etq_Id FROM estoque WHERE etq_Nome = ?";
$stmt1 = $conn->prepare($sql_estoque);
$stmt1->bind_param("s", $nomeItem);
$stmt1->execute();
$result1 = $stmt1->get_result();

if ($row1 = $result1->fetch_assoc()) {
    $idProduto = $row1['etq_Id'];

    // Verifica o estoque no romaneio
    $sql_romaneio = "SELECT rom_Idproduto, SUM(rom_Quantidade) AS qtd 
                     FROM romaneio 
                     WHERE rom_Idproduto = ?
                     GROUP BY rom_Idproduto";
    $stmt2 = $conn->prepare($sql_romaneio);
    $stmt2->bind_param("i", $idProduto);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if ($row2 = $result2->fetch_assoc()) {
        if ($row2['qtd'] <= 0) {
            $response['bloquear'] = true;
        }
    }
}

echo json_encode($response);

<?php
include 'config.php';
header('Content-Type: application/json');

// Exibe erros durante desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

    // Busca o status e estoque do produto
    $sql_romaneio = "SELECT e.etq_Id, e.etq_Ativo, COALESCE(SUM(r.rom_Quantidade), 0) AS qtd
                     FROM estoque e
                     LEFT JOIN romaneio r ON e.etq_Id = r.rom_Idproduto
                     WHERE e.etq_Id = ?
                     GROUP BY e.etq_Id, e.etq_Ativo";

    $stmt2 = $conn->prepare($sql_romaneio);
    $stmt2->bind_param("i", $idProduto);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if ($row2 = $result2->fetch_assoc()) {
        $ativo = $row2['etq_Ativo'];
        $quantidade = (int) $row2['qtd'];

        if ($ativo !== 'S' || $quantidade <= 0) {
            $response['bloquear'] = true;
        }
    }
}

echo json_encode($response);

<?php
header('Content-Type: application/json');
include 'config.php';

$quantidade = 1; // Remover hardcode se quiser passar isso via POST
$justificativa = isset($_POST['justificativa']) ? trim($_POST['justificativa']) : '';
$item = isset($_POST['item']) ? trim($_POST['item']) : '';

if (empty($quantidade) || empty($justificativa) || empty($item)) {
    http_response_code(400);
    echo json_encode([
        'erro' => true,
        'mensagem' => 'Parâmetros inválidos ou ausentes.'
    ]);
    exit();
}

$datahora = date('Y-m-d H:i:s');

try {
    $stmt = $conn->prepare("INSERT INTO justificaremocao (jrm_Datahora, jrm_Quantidade, jrm_Item, jrm_Justificativa) 
                            VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $datahora, $quantidade, $item, $justificativa);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Justificativa registrada com sucesso.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'erro' => true,
            'mensagem' => 'Falha ao registrar a justificativa no banco de dados.',
            'debug' => $stmt->error
        ]);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'erro' => true,
        'mensagem' => 'Erro ao inserir no banco de dados.',
        'exception' => $e->getMessage()
    ]);
}

$conn->close();
?>

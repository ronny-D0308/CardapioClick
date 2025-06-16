<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//header('Content-Type: application/json');

include 'config.php';

$response = [
    'temItens' => false,
    'itens' => [],
    'erro' => null
];

$mesa = isset($_GET['mesa']) ? (int)$_GET['mesa'] : 0;

if ($mesa <= 0) {
    $response['erro'] = 'Mesa inválida.';
    echo json_encode($response);
    exit;
}

if (!$conn) {
    $response['erro'] = 'Erro na conexão com o banco de dados.';
    echo json_encode($response);
    exit;
}

$sql = "SELECT ven_Seq, ven_Cliente, ven_Garcom, ven_Mesa, ven_Itens 
        FROM vendas WHERE ven_Mesa = ? AND ven_Finalizada <> 'S'";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $response['erro'] = 'Erro na preparação da consulta: ' . $conn->error;
    echo json_encode($response);
    exit;
}

$stmt->bind_param("i", $mesa);
$stmt->execute();
$result = $stmt->get_result();

$valor_mesa = "SELECT ven_Mesa, SUM(ven_Valor) AS total
               FROM vendas 
               WHERE ven_Mesa = $mesa AND ven_Finalizada <> 'S'
               GROUP BY ven_Mesa";
$query_mesa = mysqli_query($conn, $valor_mesa);
$resultado_estoq = mysqli_fetch_object($query_mesa);
$linha = !empty($resultado_estoq->total) ? $resultado_estoq->total : 0;


while ($row = $result->fetch_assoc()) {
    $itensDecodificados = json_decode($row['ven_Itens'], true);

    // Verifica se a decodificação foi bem-sucedida
    if (is_array($itensDecodificados)) {
        foreach ($itensDecodificados as $item) {
            $response['itens'][] = [
                'ven_Mesa' => $row['ven_Mesa'],
                'Cliente' => $row['ven_Cliente'],
                'Garcom' => $row['ven_Garcom'],
                'ven_Valor' => (float)$linha,
                'nome' => $item['nome'],
                'quantidade' => $item['quantidade'],
                'preco_unitario' => (float)$item['preco_unitario'],
                'subtotal' => (float)$item['subtotal']
            ];
        }
    } else {
        // Opcional: registrar erro para debug
        $response['erro'] = 'Erro ao decodificar ven_Itens. Conteúdo: ' . $row['ven_Itens'];
    }
}

$response['temItens'] = count($response['itens']) > 0;

echo json_encode($response);

<?php
header('Content-Type: application/json');
include 'conexao.php'; // Inclua sua conexão com o banco

$nomeBusca = $_POST['nomeBusca'];
$tipoBusca = $_POST['tipoBusca'];
$dataInicio = $_POST['dataInicio'];
$dataFinal = $_POST['dataFinal'];

$dados = [];
$labels = [];

if ($tipoBusca == "por-mesa") {
    $sql = "SELECT ven_Garcom, COUNT(DISTINCT ven_Mesa) AS total_mesas 
            FROM vendas 
            WHERE ven_Garcom LIKE '%$nomeBusca%' 
            AND ven_Data BETWEEN '$dataInicio' AND '$dataFinal' 
            GROUP BY ven_Garcom";
} elseif ($tipoBusca == "por-valor") {
    $sql = "SELECT ven_Garcom, SUM(ven_Valor) AS total_valor 
            FROM vendas 
            WHERE ven_Garcom LIKE '%$nomeBusca%' 
            AND ven_Data BETWEEN '$dataInicio' AND '$dataFinal' 
            GROUP BY ven_Garcom";
} elseif ($tipoBusca == "ticket") {
    $sql = "SELECT ven_Garcom, COUNT(DISTINCT ven_Mesa) AS total_mesas, SUM(ven_Valor) AS total_venda 
            FROM vendas 
            WHERE ven_Garcom LIKE '%$nomeBusca%' 
            AND ven_Data BETWEEN '$dataInicio' AND '$dataFinal' 
            GROUP BY ven_Garcom";
}

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['ven_Garcom'];
        if ($tipoBusca == "por-mesa") {
            $dados[] = (int) $row['total_mesas'];
        } elseif ($tipoBusca == "por-valor") {
            $dados[] = (float) $row['total_valor'];
        } elseif ($tipoBusca == "ticket") {
            $media = $row['total_mesas'] ? ($row['total_venda'] / $row['total_mesas']) : 0;
            $dados[] = (float) $media;
        }
    }
}

$conn->close();

echo json_encode([
    'labels' => $labels,
    'dados' => $dados,
    'tipoBusca' => $tipoBusca
]);

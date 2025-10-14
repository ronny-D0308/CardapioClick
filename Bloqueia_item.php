<?php
include 'config.php';
header('Content-Type: application/json');

// Configuração de erros para desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ Função para definir produtos compostos (mesma do processamento)
function obterComponentesProduto($nomeProduto) {
    $produtosCompostos = [
        'Filé trinchado' => [
            ['nome' => 'Batata', 'quantidade' => 250],
            ['nome' => 'Gado', 'quantidade' => 250]
        ],
        '1 Pessoa' => [
            ['nome' => 'Arroz', 'quantidade' => 300],
            ['nome' => 'Gado', 'quantidade' => 200]
        ],
        '2 Pessoa' => [
            ['nome' => 'Arroz', 'quantidade' => 600],
            ['nome' => 'Gado', 'quantidade' => 400]
        ],
        '3 Pessoa' => [
            ['nome' => 'Arroz', 'quantidade' => 900],
            ['nome' => 'Gado', 'quantidade' => 600]
        ],
        '4 Pessoa' => [
            ['nome' => 'Arroz', 'quantidade' => 1200],
            ['nome' => 'Gado', 'quantidade' => 800]
        ],
        '5 Pessoa' => [
            ['nome' => 'Arroz', 'quantidade' => 1500],
            ['nome' => 'Gado', 'quantidade' => 1000]
        ]
    ];
    
    return $produtosCompostos[trim($nomeProduto)] ?? null;
}

// ✅ Função para verificar disponibilidade de componente
function verificarDisponibilidadeComponente($conn, $nomeComponente, $quantidadeMinima = 250) {
    // Buscar ID do componente
    $sql_comp = "SELECT etq_Id FROM estoque WHERE etq_Nome = ?";
    $stmt_comp = $conn->prepare($sql_comp);
    if (!$stmt_comp) {
        return ['disponivel' => false, 'motivo' => 'Erro na consulta do componente'];
    }
    
    $stmt_comp->bind_param("s", $nomeComponente);
    $stmt_comp->execute();
    $result_comp = $stmt_comp->get_result();
    $comp_row = $result_comp->fetch_assoc();
    $stmt_comp->close();
    
    if (!$comp_row) {
        return ['disponivel' => false, 'motivo' => "Componente '$nomeComponente' não encontrado"];
    }
    
    $idComponente = $comp_row['etq_Id'];
    
    // Verificar status e quantidade do componente
    $sql_status = "SELECT e.etq_Ativo, COALESCE(SUM(r.rom_Quantidade), 0) AS qtd_total
                   FROM estoque e
                   LEFT JOIN romaneio r ON e.etq_Id = r.rom_Idproduto
                   WHERE e.etq_Id = ?
                   GROUP BY e.etq_Id, e.etq_Ativo";
    
    $stmt_status = $conn->prepare($sql_status);
    if (!$stmt_status) {
        return ['disponivel' => false, 'motivo' => 'Erro na consulta de status'];
    }
    
    $stmt_status->bind_param("i", $idComponente);
    $stmt_status->execute();
    $result_status = $stmt_status->get_result();
    $status_row = $result_status->fetch_assoc();
    $stmt_status->close();
    
    if (!$status_row) {
        return ['disponivel' => false, 'motivo' => "Dados do componente '$nomeComponente' não encontrados"];
    }
    
    $ativo = $status_row['etq_Ativo'];
    $quantidade = (float) $status_row['qtd_total'];
    
    // Verificar se está ativo
    if ($ativo !== 'S') {
        return ['disponivel' => false, 'motivo' => "Componente '$nomeComponente' desativado"];
    }
    
    // Verificar se há quantidade suficiente
    if ($quantidade < $quantidadeMinima) {
        return ['disponivel' => false, 'motivo' => "Estoque insuficiente de '$nomeComponente' (disponível: {$quantidade}g, mínimo: {$quantidadeMinima}g)"];
    }
    
    return ['disponivel' => true, 'quantidade_disponivel' => $quantidade];
}

// ✅ Resposta padrão
$response = ['bloquear' => false];

// ✅ Validação da entrada
$input = json_decode(file_get_contents('php://input'), true);
$nomeItem = trim($input['item'] ?? '');

if (!$nomeItem) {
    $response['erro'] = 'Nome do item não recebido.';
    echo json_encode($response);
    exit;
}

try {
    // ✅ Verificar se é produto composto
    $componentes = obterComponentesProduto($nomeItem);
    
    if ($componentes) {
        // ✅ Produto composto - verificar todos os componentes
        $componentesIndisponiveis = [];
        
        foreach ($componentes as $componente) {
            $nomeComponente = $componente['nome'];
            $quantidadeNecessaria = $componente['quantidade'];
            
            $verificacao = verificarDisponibilidadeComponente($conn, $nomeComponente, $quantidadeNecessaria);
            
            if (!$verificacao['disponivel']) {
                $componentesIndisponiveis[] = $verificacao['motivo'];
            }
        }
        
        if (!empty($componentesIndisponiveis)) {
            $response['bloquear'] = true;
            $response['motivo'] = 'Produto composto indisponível: ' . implode('; ', $componentesIndisponiveis);
            $response['tipo'] = 'produto_composto';
        }
        
    } else {
        // ✅ Produto simples - verificação original melhorada
        $sql_estoque = "SELECT etq_Id FROM estoque WHERE etq_Nome = ?";
        $stmt1 = $conn->prepare($sql_estoque);
        
        if (!$stmt1) {
            throw new Exception('Erro ao preparar consulta de estoque: ' . $conn->error);
        }
        
        $stmt1->bind_param("s", $nomeItem);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        
        if ($row1 = $result1->fetch_assoc()) {
            $idProduto = $row1['etq_Id'];
            $stmt1->close();
            
            // Buscar status e estoque do produto
            $sql_romaneio = "SELECT e.etq_Id, e.etq_Ativo, COALESCE(SUM(r.rom_Quantidade), 0) AS qtd_total
                             FROM estoque e
                             LEFT JOIN romaneio r ON e.etq_Id = r.rom_Idproduto
                             WHERE e.etq_Id = ?
                             GROUP BY e.etq_Id, e.etq_Ativo";
            
            $stmt2 = $conn->prepare($sql_romaneio);
            if (!$stmt2) {
                throw new Exception('Erro ao preparar consulta de romaneio: ' . $conn->error);
            }
            
            $stmt2->bind_param("i", $idProduto);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            
            if ($row2 = $result2->fetch_assoc()) {
                $ativo = $row2['etq_Ativo'];
                $quantidade = (float) $row2['qtd_total'];
                
                // ✅ Verificações detalhadas
                if ($ativo !== 'S') {
                    $response['bloquear'] = true;
                    $response['motivo'] = 'Produto desativado';
                    $response['tipo'] = 'produto_desativado';
                } elseif ($quantidade <= 0) {
                    $response['bloquear'] = true;
                    $response['motivo'] = 'Estoque zerado';
                    $response['tipo'] = 'sem_estoque';
                } else {
                    // ✅ Produto disponível - informações adicionais
                    $response['quantidade_disponivel'] = $quantidade;
                    $response['tipo'] = 'produto_simples';
                }
            } else {
                $response['bloquear'] = true;
                $response['motivo'] = 'Dados do produto não encontrados';
                $response['tipo'] = 'dados_nao_encontrados';
            }
            
            $stmt2->close();
            
        } else {
            $stmt1->close();
            $response['bloquear'] = true;
            $response['motivo'] = 'Produto não encontrado no estoque';
            $response['tipo'] = 'produto_nao_encontrado';
        }
    }
    
} catch (Exception $e) {
    $response['bloquear'] = true;
    $response['erro'] = 'Erro interno: ' . $e->getMessage();
    $response['tipo'] = 'erro_sistema';
    
    // Log do erro
    error_log("Erro em Bloqueia_item.php: " . $e->getMessage());
}

// ✅ Log para debug (remover em produção)
error_log("Verificação de item: $nomeItem - Resultado: " . json_encode($response));

echo json_encode($response);
?>
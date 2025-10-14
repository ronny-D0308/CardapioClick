<?php
include('forcar_erros.php');
include('config.php');
session_start();

// ✅ Função para log de auditoria
function logOperacao($conn, $usuario, $acao, $detalhes) {
    try {
        $stmt = $conn->prepare("INSERT INTO logs (log_Usuario, log_Acao, log_Detalhes, log_Data) VALUES (?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("sss", $usuario, $acao, $detalhes);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}

// ✅ Função para definir produtos compostos
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
            ['nome' => 'Arroz', 'quantidade' => 150],
            ['nome' => 'Gado', 'quantidade' => 100]
        ],
        '3 Pessoa' => [
            ['nome' => 'Arroz', 'quantidade' => 150],
            ['nome' => 'Gado', 'quantidade' => 100]
        ],
        '4 Pessoa' => [
            ['nome' => 'Arroz', 'quantidade' => 150],
            ['nome' => 'Gado', 'quantidade' => 100]
        ],
        '5 Pessoa' => [
            ['nome' => 'Arroz', 'quantidade' => 150],
            ['nome' => 'Gado', 'quantidade' => 100]
        ]
    ];
    
    return $produtosCompostos[trim($nomeProduto)] ?? null;
}

// ✅ Função para processar baixa de produtos compostos
function processarBaixaComposta($conn, $nomeProduto, $quantidade, $garcon, &$mensagens) {
    $componentes = obterComponentesProduto($nomeProduto);
    
    if (!$componentes) {
        return false; // Não é um produto composto
    }
    
    $baixasRealizadas = [];
    $erros = [];
    $componentesParaBaixar = [];
    
    // ✅ FASE 1: Verificar disponibilidade de todos os componentes
    foreach ($componentes as $componente) {
        $nomeComponente = $componente['nome'];
        $quantidadeNecessaria = $componente['quantidade'] * $quantidade;
        
        // Buscar o componente no estoque
        $sql_comp = "SELECT etq_Id FROM estoque WHERE etq_Nome = ?";
        $stmt_comp = $conn->prepare($sql_comp);
        if (!$stmt_comp) {
            throw new Exception("Erro ao preparar consulta de componente: " . $conn->error);
        }
        
        $stmt_comp->bind_param("s", $nomeComponente);
        $stmt_comp->execute();
        $res_comp = $stmt_comp->get_result();
        $comp_row = $res_comp->fetch_assoc();
        $stmt_comp->close();
        
        if (!$comp_row) {
            $erros[] = "Componente <strong>$nomeComponente</strong> não encontrado no estoque para <strong>$nomeProduto</strong>.";
            continue;
        }
        
        $idComponente = $comp_row['etq_Id'];
        
        // Verificar disponibilidade no romaneio
        $sql_rom_comp = "SELECT rom_Id, rom_Quantidade FROM romaneio 
                         WHERE rom_Idproduto = ? AND rom_Quantidade >= ? 
                         ORDER BY rom_Id ASC LIMIT 1";
        $stmt_rom_comp = $conn->prepare($sql_rom_comp);
        if (!$stmt_rom_comp) {
            throw new Exception("Erro ao preparar consulta de romaneio do componente: " . $conn->error);
        }
        
        $stmt_rom_comp->bind_param("id", $idComponente, $quantidadeNecessaria);
        $stmt_rom_comp->execute();
        $res_rom_comp = $stmt_rom_comp->get_result();
        $rom_comp = $res_rom_comp->fetch_assoc();
        $stmt_rom_comp->close();
        
        if (!$rom_comp) {
            $erros[] = "Estoque insuficiente do componente <strong>$nomeComponente</strong> para <strong>$nomeProduto</strong>. Necessário: {$quantidadeNecessaria}g.";
            continue;
        }
        
        // Armazenar dados para baixa posterior
        $componentesParaBaixar[] = [
            'nome' => $nomeComponente,
            'id' => $idComponente,
            'quantidade_necessaria' => $quantidadeNecessaria,
            'rom_id' => $rom_comp['rom_Id'],
            'quantidade_atual' => $rom_comp['rom_Quantidade']
        ];
    }
    
    // ✅ Se houver erros, não realizar nenhuma baixa
    if (!empty($erros)) {
        $mensagens = array_merge($mensagens, $erros);
        return false;
    }
    
    // ✅ FASE 2: Realizar as baixas de todos os componentes
    foreach ($componentesParaBaixar as $comp) {
        $nomeComponente = $comp['nome'];
        $quantidadeNecessaria = $comp['quantidade_necessaria'];
        $romIdComponente = $comp['rom_id'];
        $quantidadeAtualComponente = $comp['quantidade_atual'];
        
        // Calcular nova quantidade
        $novaQuantidadeComponente = $quantidadeAtualComponente - $quantidadeNecessaria;
        
        // Realizar a baixa
        $stmt_baixa = $conn->prepare("UPDATE romaneio SET rom_Quantidade = ? WHERE rom_Id = ?");
        if (!$stmt_baixa) {
            throw new Exception("Erro ao preparar baixa do componente $nomeComponente: " . $conn->error);
        }
        
        $stmt_baixa->bind_param("di", $novaQuantidadeComponente, $romIdComponente);
        
        if (!$stmt_baixa->execute()) {
            throw new Exception("Erro ao realizar baixa do componente $nomeComponente: " . $stmt_baixa->error);
        }
        $stmt_baixa->close();
        
        // Registrar a baixa realizada
        $baixasRealizadas[] = [
            'componente' => $nomeComponente,
            'quantidade' => $quantidadeNecessaria,
            'romaneio_id' => $romIdComponente
        ];
        
        // Log da operação
        logOperacao($conn, $garcon, "BAIXA_COMPONENTE", 
                   "Produto: $nomeProduto, Componente: $nomeComponente, Quantidade: {$quantidadeNecessaria}g, Romaneio: $romIdComponente");
    }
    
    return $baixasRealizadas;
}

// ✅ Função para calcular baixa de estoque
function calcularBaixaEstoque($categoria, $nomeProduto, $quantidade) {
    $produtosEspeciais = ['carnes'];
    $produtosPorGrama = ['batata', 'macaxeira'];
    
    // Verifica se é produto vendido por gramas
    if (in_array(strtolower($categoria), $produtosEspeciais) || 
        in_array(strtolower($nomeProduto), $produtosPorGrama)) {
        return $quantidade * 250; // 250g por porção
    }
    
    return $quantidade; // unidades normais
}

// ✅ Função para determinar URL de redirecionamento
function obterUrlRedirecionamento($nivel) {
    $urls = [
        'Funcio' => 'Comandas.php',
        'Admin' => 'Central_adm.php',
        'Caixa' => 'Caixa_main.php'
    ];
    
    return $urls[$nivel] ?? 'Comandas.php';
}

// ✅ Verificação de sessão
if (!isset($_SESSION['usuario'])) {
    header('Location: Validacao.php');
    exit;
}

// ✅ Validação rigorosa de parâmetros
$requiredFields = ['nome_cliente', 'mesa', 'total', 'itens_selecionados'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        die("Parâmetro obrigatório '$field' não fornecido.");
    }
}

// Validação específica do JSON
$itensDecodificados = json_decode($_POST['itens_selecionados'], true);
if (!is_array($itensDecodificados) || json_last_error() !== JSON_ERROR_NONE) {
    die("Dados de itens inválidos ou corrompidos.");
}

// ✅ Sanitização e validação dos dados
$Cliente = trim($_POST['nome_cliente']);
$Mesa = filter_var($_POST['mesa'], FILTER_VALIDATE_INT);
$Total = filter_var($_POST['total'], FILTER_VALIDATE_FLOAT);

if (!$Mesa || $Mesa <= 0) {
    die("Número da mesa inválido.");
}

if ($Total === false || $Total < 0) {
    die("Valor total inválido.");
}

if (strlen($Cliente) < 2 || strlen($Cliente) > 50) {
    die("Nome do cliente deve ter entre 2 e 50 caracteres.");
}

// 🔧 Dados da comanda
$Garcon = $_SESSION['usuario'];
$nivel = $_SESSION['nivel'];
$itensOriginal = $itensDecodificados;

$mensagens = [];
$itensParaInserir = [];
$somaNovos = 0.0;

// ✅ Iniciar transação
$conn->autocommit(false);

try {
    // ✅ Processar cada item
    foreach ($itensOriginal as $item) {
        // Validação dos dados do item
        if (!isset($item['nome'], $item['quantidade'], $item['preco_unitario'])) {
            $mensagens[] = "Item com dados incompletos foi ignorado.";
            continue;
        }

        $nome = trim($item['nome']);
        $quantidade = floatval($item['quantidade']);
        $precoUnitario = floatval($item['preco_unitario']);

        if ($quantidade <= 0 || $precoUnitario < 0) {
            continue;
        }

        // ✅ NOVO: Verificar se é produto composto
        $componentes = obterComponentesProduto($nome);
        
        if ($componentes) {
            // ✅ Processar produto composto
            $baixasRealizadas = processarBaixaComposta($conn, $nome, $quantidade, $Garcon, $mensagens);
            
            if ($baixasRealizadas === false) {
                // Erro na baixa composta - mensagens já foram adicionadas
                continue;
            }
            
            // Produto composto processado com sucesso
            $subtotal = $quantidade * $precoUnitario;
            $itemFinal = [
                'nome' => $nome,
                'quantidade' => $quantidade,
                'preco_unitario' => $precoUnitario,
                'subtotal' => $subtotal
            ];

            $itensParaInserir[] = $itemFinal;
            $somaNovos += $subtotal;
            
            // Log específico para produto composto
            $componentesTexto = implode(', ', array_map(function($b) {
                return $b['componente'] . ': ' . $b['quantidade'] . 'g';
            }, $baixasRealizadas));
            
            logOperacao($conn, $Garcon, "BAIXA_PRODUTO_COMPOSTO", 
                       "Produto: $nome, Quantidade: $quantidade, Componentes: [$componentesTexto]");
            
            continue; // Pular o processamento normal
        }

        // ✅ Processamento normal para produtos simples
        // Buscar produto no estoque com prepared statement
        $sql_estoq = "SELECT etq_Id, etq_Categoria, etq_Nome FROM estoque WHERE etq_Nome = ?";
        $stmt = $conn->prepare($sql_estoq);
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta de estoque: " . $conn->error);
        }

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
        $NomeProd = $row['etq_Nome'];

        // ✅ Buscar quantidade disponível no romaneio
        $sql_romaneio = "SELECT rom_Id, rom_Quantidade FROM romaneio 
                         WHERE rom_Idproduto = ? AND rom_Quantidade > 0 
                         ORDER BY rom_Id ASC LIMIT 1";
        $stmt_rom = $conn->prepare($sql_romaneio);
        if (!$stmt_rom) {
            throw new Exception("Erro ao preparar consulta de romaneio: " . $conn->error);
        }

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
        $quantidade_atual = floatval($rom['rom_Quantidade']);

        // ✅ Cálculo correto de baixa de estoque
        $quantidade_para_baixa = calcularBaixaEstoque($Categoria, $NomeProd, $quantidade);
        $quantidadeOriginal = $quantidade;

        // ✅ Verificar disponibilidade e ajustar se necessário
        if ($quantidade_para_baixa > $quantidade_atual) {
            // Calcular quantas unidades realmente podem ser fornecidas
            if (calcularBaixaEstoque($Categoria, $NomeProd, 1) > 1) {
                // Produto vendido por gramas
                $quantidade_disponivel = floor($quantidade_atual / 250);
                $quantidade_para_baixa = $quantidade_disponivel * 250;
                $quantidade = $quantidade_disponivel;
            } else {
                // Produto vendido por unidades
                $quantidade = $quantidade_atual;
                $quantidade_para_baixa = $quantidade_atual;
            }

            if ($quantidade > 0) {
                $mensagens[] = "Estoque insuficiente para <strong>$nome</strong>. Solicitado: <strong>$quantidadeOriginal</strong>, disponível: <strong>$quantidade</strong> unidade(s).";
            }
        }

        if ($quantidade <= 0) {
            $mensagens[] = "Item <strong>$nome</strong> não pôde ser adicionado - estoque insuficiente.";
            continue;
        }

        // ✅ Atualizar romaneio
        $nova_quantidade = max(0, $quantidade_atual - $quantidade_para_baixa);
        $stmt_up = $conn->prepare("UPDATE romaneio SET rom_Quantidade = ? WHERE rom_Id = ?");
        if (!$stmt_up) {
            throw new Exception("Erro ao preparar atualização de romaneio: " . $conn->error);
        }

        $stmt_up->bind_param("di", $nova_quantidade, $romId);
        if (!$stmt_up->execute()) {
            throw new Exception("Erro ao atualizar romaneio: " . $stmt_up->error);
        }
        $stmt_up->close();

        // ✅ Preparar item para inserção
        $subtotal = $quantidade * $precoUnitario;
        $itemFinal = [
            'nome' => $nome,
            'quantidade' => $quantidade,
            'preco_unitario' => $precoUnitario,
            'subtotal' => $subtotal
        ];

        $itensParaInserir[] = $itemFinal;
        $somaNovos += $subtotal;

        // Log da operação de baixa
        logOperacao($conn, $Garcon, "BAIXA_ESTOQUE", 
                   "Produto: $nome, Quantidade: $quantidade_para_baixa, Romaneio: $romId");
    }

    // ✅ Verificar se há itens para processar
    if (count($itensParaInserir) === 0) {
        $conn->rollback();
        $redirectUrl = obterUrlRedirecionamento($nivel);
        
        $mensagensHTML = '';
        foreach ($mensagens as $msg) {
            $mensagensHTML .= "<li>$msg</li>";
        }

        echo <<<HTML
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="refresh" content="8;url=$redirectUrl">
            <title>Nenhum item adicionado</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #da6c22;
                    padding: 20px;
                    color: white;
                    text-align: center;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: rgba(0,0,0,0.3);
                    padding: 30px;
                    border-radius: 10px;
                }
                ul {
                    font-size: 18px;
                    text-align: left;
                    margin: 20px auto;
                    display: inline-block;
                }
                h1 {
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                p {
                    font-size: 16px;
                    margin-top: 20px;
                }
                .countdown {
                    font-weight: bold;
                    font-size: 18px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>⚠️ Nenhum item pôde ser adicionado</h1>
                <ul>
                    $mensagensHTML
                </ul>
                <p class="countdown">Redirecionando em 8 segundos...</p>
            </div>
        </body>
        </html>
        HTML;

        exit();
    }

    // ✅ Buscar venda existente para a mesa
    $sqlBusca = "SELECT ven_Seq, ven_Itens, ven_Valor FROM vendas 
                 WHERE ven_Mesa = ? AND ven_Finalizada <> 'S' 
                 ORDER BY ven_Seq DESC LIMIT 1";
    $stmtBusca = $conn->prepare($sqlBusca);
    if (!$stmtBusca) {
        throw new Exception("Erro ao preparar busca de venda: " . $conn->error);
    }

    $stmtBusca->bind_param("i", $Mesa);
    $stmtBusca->execute();
    $result = $stmtBusca->get_result();
    $vendaExistente = $result->fetch_assoc();
    $stmtBusca->close();

    // ✅ Atualizar ou criar venda
    if ($vendaExistente) {
        // Atualizar venda existente
        $venSeq = $vendaExistente['ven_Seq'];
        $itensAntigos = json_decode($vendaExistente['ven_Itens'], true) ?: [];
        $totalAnterior = floatval($vendaExistente['ven_Valor']);

        // Mesclar itens novos com existentes
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

        $novoTotal = $totalAnterior + $somaNovos;
        $itensJson = json_encode($itensAntigos, JSON_UNESCAPED_UNICODE);

        $stmtUpdate = $conn->prepare("UPDATE vendas SET ven_Cliente = ?, ven_Itens = ?, ven_Valor = ? WHERE ven_Seq = ?");
        if (!$stmtUpdate) {
            throw new Exception("Erro ao preparar atualização de venda: " . $conn->error);
        }

        $stmtUpdate->bind_param("ssdi", $Cliente, $itensJson, $novoTotal, $venSeq);
        if (!$stmtUpdate->execute()) {
            throw new Exception("Erro ao atualizar venda: " . $stmtUpdate->error);
        }
        $stmtUpdate->close();

        logOperacao($conn, $Garcon, "ATUALIZAR_COMANDA", 
                   "Mesa: $Mesa, Cliente: $Cliente, Valor adicionado: R$ " . number_format($somaNovos, 2, ',', '.'));

    } else {
        // Criar nova venda
        $itensJson = json_encode($itensParaInserir, JSON_UNESCAPED_UNICODE);
        
        $stmtVenda = $conn->prepare("INSERT INTO vendas (ven_Cliente, ven_Garcom, ven_Valor, ven_Data, ven_Mesa, ven_Itens, ven_Finalizada) 
                                     VALUES (?, ?, ?, NOW(), ?, ?, 'N')");
        if (!$stmtVenda) {
            throw new Exception("Erro ao preparar inserção de venda: " . $conn->error);
        }

        $stmtVenda->bind_param("ssdis", $Cliente, $Garcon, $somaNovos, $Mesa, $itensJson);
        if (!$stmtVenda->execute()) {
            throw new Exception("Erro ao inserir venda: " . $stmtVenda->error);
        }
        $stmtVenda->close();

        logOperacao($conn, $Garcon, "NOVA_COMANDA", 
                   "Mesa: $Mesa, Cliente: $Cliente, Valor: R$ " . number_format($somaNovos, 2, ',', '.'));
    }

    // ✅ Confirmar transação
    $conn->commit();

} catch (Exception $e) {
    // ✅ Reverter transação em caso de erro
    $conn->rollback();
    
    // Log do erro
    error_log("Erro no processamento da comanda: " . $e->getMessage());
    logOperacao($conn, $Garcon ?? 'SISTEMA', "ERRO_PROCESSAMENTO", $e->getMessage());
    
    // Exibir erro amigável
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="5;url=Comandas.php">
        <title>Erro no Processamento</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #dc3545;
                padding: 20px;
                color: white;
                text-align: center;
            }
            .container {
                max-width: 500px;
                margin: 0 auto;
                background: rgba(0,0,0,0.3);
                padding: 30px;
                border-radius: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>❌ Erro no Processamento</h1>
            <p>Ocorreu um erro ao processar sua solicitação. Tente novamente.</p>
            <p>Redirecionando em 5 segundos...</p>
        </div>
    </body>
    </html>
    HTML;
    
    exit();
}

// ✅ Restaurar autocommit
$conn->autocommit(true);

// ✅ Redirecionamento baseado no nível do usuário
$redirectUrl = obterUrlRedirecionamento($nivel);
header("Location: $redirectUrl");
exit();
?>
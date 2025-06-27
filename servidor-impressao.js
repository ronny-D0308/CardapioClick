require('dotenv').config();
const express = require('express');
const escpos = require('escpos');
escpos.Network = require('escpos-network');

const app = express();
const PORT = process.env.PORT || 3000;
const PRINTER_IP = process.env.PRINTER_IP || '192.168.0.131';
const PRINTER_PORT = process.env.PRINTER_PORT || 9100;
const TIMEOUT = process.env.TIMEOUT || 5000;

// Configuração CORS robusta
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', process.env.ALLOWED_ORIGINS || 'http://localhost');
    res.header('Access-Control-Allow-Methods', 'POST, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Content-Type');
    res.header('Access-Control-Max-Age', '86400');
    
    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }
    
    next();
});

app.use(express.json());

// Middleware de log para debug
app.use((req, res, next) => {
    console.log(`[${new Date().toISOString()}] ${req.method} ${req.path}`);
    next();
});

// Rota de saúde do servidor
app.get('/health', (req, res) => {
    res.status(200).json({
        status: 'online',
        printer: `${PRINTER_IP}:${PRINTER_PORT}`,
        timestamp: new Date().toISOString()
    });
});

// Rota principal de impressão
app.post('/imprimir', async (req, res) => {
    let device;
    try {
        const { mesa, cliente, garcom, data, total, itens } = req.body;
        
        // Validações
        if (!itens || !Array.isArray(itens) || itens.length === 0) {
            throw new Error('Lista de itens inválida ou vazia');
        }

        device = new escpos.Network(PRINTER_IP, PRINTER_PORT);
        
        await new Promise((resolve, reject) => {
            const timeout = setTimeout(() => {
                device.close();
                reject(new Error(`Timeout após ${TIMEOUT}ms ao tentar imprimir`));
            }, TIMEOUT);

            device.open((error) => {
                if (error) {
                    clearTimeout(timeout);
                    return reject(error);
                }

                const printer = new escpos.Printer(device);
                
                // Função auxiliar para criar linhas tracejadas, considerando a largura da impressora (aprox. 42-48 caracteres para 80mm)
                const dashedLine = () => printer.text('------------------------------------------'); // 42 hífens para 80mm

                printer
                    .encode('CP860') // Codificação para caracteres em português
                    .align('ct')     // Centralizar
                    .style('b')      // Negrito
                    .size(1, 1)      // Tamanho maior para o título (simulando h2/h3)
                    .text('MAIS SABOR')
                    .text('')        // Linha em branco para espaçamento
                    //.size(1, 1)      // Retorna ao tamanho normal de fonte (simulando 10px)
                    .style('normal'); // Retorna ao estilo normal (sem negrito)
                
                //dashedLine(); // Simula <hr> ou border-top
                
                printer
                    .encode('CP860') // Codificação para caracteres em português
                    .align('lt')     // Alinhar à esquerda para os detalhes
                    .text(`Mesa: ${mesa || 'N/A'}`)
                    .text(`Cliente: ${cliente || 'N/A'}`)
                    .text(`Garcom: ${garcom || 'N/A'}`)
                    .text(`Data: ${data || new Date().toLocaleDateString('pt-BR')}`); // Formato de data PT-BR
                
                dashedLine(); // Simula <hr> ou border-bottom de cabeçalho
                
                // Cabeçalho da tabela - Ajustando larguras para 80mm (aprox. 42 chars)
                // Item (22) | Qtd (6) | Valor (12) = 40 chars + 2 espaços = 42 chars
                printer
                    .style('b') // Negrito para o cabeçalho da tabela
                    .text(
                        'Item'.padEnd(22) + 
                        'Qtd'.padEnd(6) + 
                        'Valor'.padEnd(12)
                    )
                    .style('normal'); // Retorna ao normal
                
                dashedLine(); // Simula border-bottom para o cabeçalho da tabela
                
                // Itens da comanda
                itens.forEach(item => {
                    const nome = String(item.nome || '').substring(0, 22).padEnd(22); // Limita e preenche para 22 chars
                    const quantidade = String(item.quantidade || 0).padEnd(6);      // Preenche para 6 chars
                    const preco = `R$ ${Number(item.preco_unitario || 0).toFixed(2)}`.padEnd(12); // Preenche para 12 chars

                    printer
                        .align('lt')     // Centralizar
                        .text(nome + quantidade + preco);
                });

                dashedLine(); // Simula border-bottom para a lista de itens

                printer
                    .align('rt')    // Alinhar à direita para o total
                    .style('b')     // Negrito para o total
                    .size(1, 2)     // Ligeiramente maior para o total
                    .text(`TOTAL: R$ ${totalValue}`)
                    .style('normal') // Volta ao estilo normal
                    .align('ct')     // Centralizar
                    .size(1, 1)      // Tamanho normal
                    .text('Obrigado pela preferência')
                    .feed(2)        // Alimentar papel para 2 linhas (espaçamento final)
                    .cut()          // Cortar papel
                    .close(() => {
                        clearTimeout(timeout);
                        resolve();
                    });
            });
        });

        res.json({ 
            success: true,
            message: 'Comanda impressa com sucesso'
        });

        console.log('Processo concluído. Encerrando servidor...');
        process.exit(0); // Encerra o servidor após a resposta
        
    } catch (error) {
        console.error('[ERRO]', error);
        res.status(500).json({ 
            success: false, 
            error: error.message,
            details: process.env.NODE_ENV === 'development' ? error.stack : undefined
        });
    } finally {
        // Garante que o dispositivo seja fechado mesmo em caso de erro
        if (device && device.device) { // Verifica se device e device.device existem antes de tentar fechar
            device.device.close(); 
        }
    }
});

// Tratamento de erros global
app.use((err, req, res, next) => {
    console.error('[ERRO GLOBAL]', err);
    res.status(500).json({
        success: false,
        error: 'Erro interno no servidor'
    });
});

// Inicialização do servidor
app.listen(PORT, '0.0.0.0', () => {
    console.log(`\nServidor rodando na porta ${PORT}`);
    console.log(`Configuração da impressora: ${PRINTER_IP}:${PRINTER_PORT}`);
    console.log(`Timeout: ${TIMEOUT}ms`);
    console.log(`Ambiente: ${process.env.NODE_ENV || 'development'}`);
    console.log(`Rotas disponíveis:`);
    console.log(`- POST /imprimir`);
    console.log(`- GET /health\n`);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('[UNHANDLED REJECTION]', reason);
});

process.on('uncaughtException', (error) => {
    console.error('[UNCAUGHT EXCEPTION]', error);
    process.exit(1);
});
const express = require('express');
const escpos = require('escpos');
escpos.Network = require('escpos-network');

const app = express();

// Middleware CORS manual completo
app.use((req, res, next) => {
    // Headers obrigatórios
    res.header('Access-Control-Allow-Origin', 'http://localhost');
    res.header('Access-Control-Allow-Methods', 'POST, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Content-Type');
    
    // Resposta imediata para OPTIONS
    if (req.method === 'OPTIONS') {
        return res.status(200).send();
    }
    
    next();
});

app.use(express.json());

app.post('/imprimir', async (req, res) => {
    try {
        const { mesa, cliente, garcom, data, total, itens } = req.body;
        const device = new escpos.Network('192.168.0.131', 9100);
        const printer = new escpos.Printer(device);

        await new Promise((resolve, reject) => {
            device.open((error) => {
                if (error) return reject(error);
                
                printer
                    .encode('CP860')
                    .align('ct')
                    .style('b')
                    .size(2, 1)
                    .text('MAIS SABOR')
                    .size(1, 1)
                    .text('----------------')
                    .text(`Mesa: ${mesa}`)
                    .text(`Cliente: ${cliente}`)
                    .text(`Garçom: ${garcom}`)
                    .text(`Data: ${data}`)
                    .text('----------------')
                    .align('lt')
                    .table(['Item', 'Qtd', 'Valor'])
                    .text('----------------');
                
                itens.forEach(item => {
                    printer.table([
                        item.nome.substring(0, 20),
                        item.quantidade,
                        `R$ ${Number(item.preco_unitario).toFixed(2)}`
                    ]);
                });

                printer
                    .text('----------------')
                    .align('rt')
                    .style('b')
                    .text(`TOTAL: R$ ${total}`)
                    .feed(2)
                    .cut()
                    .close(() => resolve());
            });
        });

        res.json({ success: true });
    } catch (error) {
        console.error('Erro na impressão:', error);
        res.status(500).json({ 
            success: false, 
            error: error.message 
        });
    }
});

app.listen(3000, '0.0.0.0', () => {
    console.log('Servidor rodando em http://0.0.0.0:3000');
});
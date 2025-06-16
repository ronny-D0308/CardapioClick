const express = require('express');
const cors = require('cors');
const printer = require('printer'); // <- CORRIGIDO AQUI

const app = express();
app.use(cors());
app.use(express.json());

app.post('/imprimir', (req, res) => {
    const { texto } = req.body;

    if (!texto) {
        return res.status(400).send("Texto para impressão não fornecido.");
    }

    printer.printDirect({
        data: texto,
        printer: printer.getDefaultPrinterName(), // ou substitua pelo nome da impressora, ex: 'XP-80C'
        type: 'RAW',
        success: function(jobID) {
            console.log("✅ Trabalho de impressão enviado. ID:", jobID);
            res.send("Impressão enviada com sucesso.");
        },
        error: function(err) {
            console.error("❌ Erro na impressão:", err);
            res.status(500).send("Erro na impressão.");
        }
    });
});

app.listen(3001, () => {
    console.log("🖨️ Servidor de impressão rodando em http://localhost:3001");
});

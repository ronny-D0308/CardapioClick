// TESTE_IMPRESSORA_REDE.JS
const escpos = require('escpos');
escpos.Network = require('escpos-network');

// Configurações - Substitua com os dados da sua impressora
const PRINTER_IP = '192.168.0.131'; // Altere para o IP correto
const PRINTER_PORT = 9100; // Porta padrão para impressoras térmicas

console.log(`🖨️ Iniciando teste de impressora de rede em ${PRINTER_IP}:${PRINTER_PORT}`);

// 1. Criar conexão de rede
const device = new escpos.Network(PRINTER_IP, PRINTER_PORT);
const printer = new escpos.Printer(device);

// 2. Teste de conexão básica
device.open(async function(error) {
  if (error) {
    console.error('❌ Falha na conexão:', error.message);
    await testNetworkConnection(PRINTER_IP, PRINTER_PORT);
    return;
  }

  console.log('✅ Conexão estabelecida com sucesso!');
  
  // 3. Enviar comando de teste
  printer
    .align('ct')
    .style('B')
    .size(1, 1)
    .text('TESTE DE REDE')
    .text('----------------')
    .text(`IP: ${PRINTER_IP}`)
    .text(`Porta: ${PRINTER_PORT}`)
    .text(new Date().toLocaleString())
    .cut()
    .close(() => {
      console.log('📄 Comando de impressão enviado com sucesso!');
      process.exit(0);
    });
});

// Função para testar conectividade de rede
async function testNetworkConnection(ip, port) {
  console.log('\n🔍 Realizando diagnósticos de rede:');
  
  try {
    const net = require('net');
    const client = new net.Socket();
    
    // Teste de conexão TCP
    await new Promise((resolve, reject) => {
      client.connect(port, ip, () => {
        console.log(`✔️ Conexão TCP estabelecida com ${ip}:${port}`);
        client.destroy();
        resolve();
      });
      
      client.setTimeout(3000);
      
      client.on('timeout', () => {
        console.log(`⌛ Timeout ao conectar em ${ip}:${port}`);
        client.destroy();
        reject(new Error('Timeout'));
      });
      
      client.on('error', (err) => {
        console.log(`❌ Falha na conexão TCP: ${err.message}`);
        client.destroy();
        reject(err);
      });
    });
    
    // Teste de ping (Windows)
    if (process.platform === 'win32') {
      const { exec } = require('child_process');
      exec(`ping -n 4 ${ip}`, (error, stdout, stderr) => {
        if (error) {
          console.log('❌ Falha no ping:', error.message);
          return;
        }
        console.log(`📶 Resultado do ping:\n${stdout}`);
      });
    }
  } catch (err) {
    console.log('ℹ️ Recomendações:');
    console.log('- Verifique se o IP da impressora está correto');
    console.log('- Confira se a impressora está ligada e na mesma rede');
    console.log('- Teste a porta com telnet: "telnet ' + ip + ' ' + port + '"');
    console.log('- Verifique firewall/antivírus que possa bloquear a conexão');
  }
}
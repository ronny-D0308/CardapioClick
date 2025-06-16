            document.addEventListener('DOMContentLoaded', function () {
              const modal = document.getElementById("modal");
              const modalContent = document.getElementById("modal-content-body");
              const totalValue = document.getElementById("total-value");
              const removeItemBtn = document.getElementById("remove");
              const addItemBtn = document.getElementById("add-item");
              const imagens = document.querySelectorAll(".abrir-comanda");
              let selectedComanda = null;
              let itensDaComandaAtual = [];
            
              imagens.forEach(img => {
                img.addEventListener('click', function (e) {
                  e.stopPropagation(); // Evita conflitos com clique na linha
                  const comandaId = img.getAttribute("data-id");
                  selectedComanda = img; // guarda o elemento
            
                  fetch(`verificar_comanda.php?mesa=${comandaId}`)
                    .then(response => response.json())
                    .then(data => {
                      itensDaComandaAtual = data.itens || [];
                      modalContent.innerHTML = '';
                      let totalComanda = 0;
            
                      if (data.temItens && data.itens.length > 0) {
                        const primeiroItem = data.itens[0];
                        document.getElementById('venMesa').value = primeiroItem.ven_Mesa;
            
                        let tabelaHTML = `
                          <table style="width:100%; border-collapse: collapse; font-family: sans-serif;">
                            <tr>
                              <td colspan="2" style="padding: 8px; font-weight: bold;">Cliente: ${primeiroItem.Cliente}</td>
                              <td colspan="2" style="padding: 8px; font-weight: bold;">Garçom: ${primeiroItem.Garcom}</td>
                            </tr>
                            <tr>
                              <th style="color: black; text-align: left; padding: 6px;">Item</th>
                              <th style="color: black; text-align: center; padding: 6px;"></th>
                              <th style="color: black; text-align: right; padding: 6px;">Valor Unitário</th>
                              <th style="color: black; text-align: right; padding: 6px;">Subtotal</th>
                              <th style="color: black; text-align: right; padding: 6px;">.</th>
                            </tr>
                        `;
            
                        const agrupados = {};
            
                        data.itens.forEach(item => {
                          const nome = item.nome || 'Item';
                          const precoUnitario = item.preco_unitario ?? item.Valor;
            
                          if (!agrupados[nome]) {
                            agrupados[nome] = {
                              quantidade: item.quantidade || 1,
                              preco: precoUnitario,
                              subtotal: item.subtotal || item.Valor,
                            };
                          } else {
                            agrupados[nome].quantidade += item.quantidade || 1;
                            agrupados[nome].subtotal += item.subtotal || item.Valor;
                          }
            
                          totalComanda = item.ven_Valor;
                        });
                        
                        for (const nome in agrupados) {
                          const item = agrupados[nome];
                          tabelaHTML += `
                            <tr>
                              <td style="padding: 6px;">${nome}</td>
                              <td style="text-align: center;"></td>
                              <td style="text-align: right;">${item.quantidade} X ${item.preco.toFixed(2)}</td>
                              <td style="text-align: right;">R$ ${item.subtotal.toFixed(2)}</td>
                              <td style="text-align: right;"> 
                                <img class='remover-item' src='imagens/remove.png' width='20px' data-nome='${nome}' data-mesa='${primeiroItem.ven_Mesa}'>
                              </td>
                            </tr>
                          `;
                        }
            
                        tabelaHTML += `</table>`;
                        modalContent.innerHTML = tabelaHTML;
                        totalValue.innerHTML = `<p style="margin-top: 10px; font-weight: bold;">Total: R$ ${totalComanda.toFixed(2)}</p>`;



                      modalContent.querySelectorAll('.remover-item').forEach(btn => {
                        btn.addEventListener('click', function () {
                          const itemNome = this.getAttribute('data-nome');
                          const mesaId = this.getAttribute('data-mesa');
                      
                          if (!itemNome || !mesaId) {
                            alert('Erro: nome ou mesa não informado.');
                            return;
                          }
                      
                          fetch(`Central_finalizarComanda.php?mesa=${mesaId}&item=${encodeURIComponent(itemNome)}`)
                            .then(res => res.json())
                            .then(data => {
                              if (data.sucesso) {
                                window.location.reload();
                              } else {
                                alert("Erro ao remover item: " + data.erro);
                              }
                            })
                            .catch(err => console.error("Erro na requisição:", err));
                        });
                      });

                        modal.style.display = 'flex';
                      } else {
                        modalContent.innerHTML = `<p style="padding: 10px; font-weight: bold;">Esta comanda não tem itens.</p>`;
                        totalValue.innerHTML = '';
                        modal.style.display = 'flex';
                      }
                    })
                    .catch(error => {
                      console.error('Erro ao verificar a comanda:', error);
                    });
                });
              });
            
              // Fechar o modal ao clicar fora do conteúdo
              window.addEventListener('click', function(event) {
                const modal = document.getElementById("modal");
                const modalContent = document.querySelector(".modal-content");
              
                if (event.target === modal) {
                  modal.style.display = "none";
                }
              });

              addItemBtn.addEventListener('click', function () {
                if (selectedComanda && selectedComanda.getAttribute("data-id")) {
                  const comandaId = selectedComanda.getAttribute("data-id");
                  window.location.href = `Adicionar_itens.php?comandaId=${comandaId}`;
                }
              });
            });

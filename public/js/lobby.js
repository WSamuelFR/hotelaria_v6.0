document.addEventListener('DOMContentLoaded', () => {
    const quartoCardsContainer = document.getElementById('quartoCardsContainer');
    const hospTableBody = document.getElementById('hospedagemTableBody');
    const reservaTableBody = document.getElementById('reservaTableBody');
    const cadastroTableBody = document.getElementById('cadastroTableBody');

    let currentHospFilter = 'ativa';
    let currentQuartoFilter = 'todos';
    // Removido o filtro de status de reserva (currentReservaFilter) para listar todas

    // --- 1. FUNÇÃO DE BUSCA E CARREGAMENTO ---
    const fetchDataAndRender = async () => {
        const activeTab = document.querySelector('.nav-link.active')?.id;
        let searchTerm = '';

        if (activeTab === 'quartos-tab') searchTerm = document.getElementById('quartoSearchInput')?.value || '';
        else if (activeTab === 'hospedagens-tab') searchTerm = document.getElementById('hospedagemSearchInput')?.value || '';
        else if (activeTab === 'reservas-tab') searchTerm = document.getElementById('reservaSearchInput')?.value || '';
        else if (activeTab === 'cadastros-tab') searchTerm = document.getElementById('cadastroSearchInput')?.value || '';

        try {
            // Removido o parâmetro reservaStatus da URL
            const url = `../../app/models/lobbyModel.php?search=${encodeURIComponent(searchTerm)}&status=${currentHospFilter}`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                renderQuartos(data.quartos || []);
                renderHospedagens(data.hospedagens || []);
                renderReservas(data.reservas || []);
                renderCadastros(data.cadastros || []);
            }
        } catch (error) {
            console.error('Erro ao carregar lobby:', error);
        }
    };

    // --- 2. ATIVAÇÃO DOS EVENTOS DE BUSCA ---
    const setupSearchEvents = () => {
        const searchPairs = [
            { input: 'quartoSearchInput', btn: 'quartoSearchButton' },
            { input: 'hospedagemSearchInput', btn: 'hospedagemSearchButton' },
            { input: 'reservaSearchInput', btn: 'reservaSearchButton' },
            { input: 'cadastroSearchInput', btn: 'cadastroSearchButton' }
        ];

        searchPairs.forEach(pair => {
            const inputEl = document.getElementById(pair.input);
            const btnEl = document.getElementById(pair.btn);

            if (inputEl) {
                inputEl.addEventListener('input', () => {
                    clearTimeout(inputEl.timeout);
                    inputEl.timeout = setTimeout(fetchDataAndRender, 400);
                });
            }
            if (btnEl) {
                btnEl.addEventListener('click', fetchDataAndRender);
            }
        });
    };

    // --- 3. RENDERIZADORES ---
    // (Mantive renderQuartos e renderCadastros idênticos ao original)
    const renderQuartos = (quartos) => {
        if (!quartoCardsContainer) return;
        quartoCardsContainer.innerHTML = '';
        const filtrados = quartos.filter(q => {
            if (currentQuartoFilter === 'todos') return true;
            if (currentQuartoFilter === 'livre') return q.status_principal === 'livre' && q.clean_status === 'limpo';
            if (currentQuartoFilter === 'ocupado') return q.status_principal === 'ocupado';
            if (currentQuartoFilter === 'sujo') return q.clean_status === 'sujo';
            return true;
        });
        if (filtrados.length === 0) {
            quartoCardsContainer.innerHTML = '<div class="col-12 text-center text-muted py-5">Nenhum quarto encontrado.</div>';
            return;
        }
        filtrados.forEach(q => {
            let statusClass = q.status_principal === 'ocupado' ? 'status-card-ocupado' : (q.clean_status === 'sujo' ? 'status-card-sujo' : 'status-card-livre');
            let badgeColor = q.status_principal === 'ocupado' ? 'bg-primary' : (q.clean_status === 'sujo' ? 'bg-danger' : 'bg-success');
            let labelStatus = q.clean_status === 'sujo' ? 'SUJO' : (q.status_display || q.status_principal);
            let htmlOcupacao = q.status_principal === 'ocupado'
                ? `<a href="up_hospedagem.php?id=${q.hospedagem_ativa_id}" class="text-decoration-none">
                    <button class="btn btn-sm btn-primary w-100 text-truncate text-start shadow-sm fw-bold">
                        <i class="fas fa-user-check me-1"></i> ${q.cliente_atual || 'Hóspede Ativo'}
                    </button></a>`
                : '<span class="text-muted small italic"><i class="fas fa-check-circle me-1"></i> Quarto Livre</span>';

            quartoCardsContainer.innerHTML += `
                <div class="col-12 col-sm-6 col-md-4 col-xl-3 mb-4">
                    <div class="card h-100 card-quarto ${statusClass}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h4 class="card-title mb-0 fw-bold">#${q.numero}</h4>
                                <span class="badge ${badgeColor}">${labelStatus.toUpperCase()}</span>
                            </div>
                            <h6 class="card-subtitle mb-2 text-muted small"><i class="fas fa-fan me-1"></i> ${q.room_type || 'Padrão'}</h6>
                            <h6 class="card-subtitle mb-3 text-muted small"><i class="fas fa-bed me-1"></i> ${q.bed_quantity || 'Single'}</h6>
                            <p class="card-text mb-1 small"><strong>Limpeza:</strong> <span class="text-${q.clean_status === 'sujo' ? 'danger' : 'success'} fw-bold">${(q.clean_status || 'limpo').toUpperCase()}</span></p>
                            <div class="mt-3">${htmlOcupacao}</div>
                        </div>
                        <div class="card-footer bg-transparent border-0 d-flex justify-content-between gap-2 pb-3">
                            <button onclick="realizarLimpeza(${q.id})" class="btn btn-sm btn-outline-success w-100" ${(q.status_principal === 'ocupado' || q.clean_status === 'limpo') ? 'disabled' : ''}><i class="fas fa-broom"></i> Limpar</button>
                          <a href="up_quarto.php?id=${q.id || q.quarto_id}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-cog"></i>
                          </a>
                        </div>
                    </div>
                </div>`;
        });
    };

    const renderCadastros = (cadastros) => {
        if (!cadastroTableBody) return;
        cadastroTableBody.innerHTML = cadastros.length ? '' : '<tr><td colspan="4" class="text-center">Nenhum cadastro encontrado.</td></tr>';
        cadastros.forEach(c => {
            cadastroTableBody.innerHTML += `
            <tr>
                <td>${c.full_name || c.nome_cliente}</td>
                <td>${c.cpf_cnpj}</td>
                <td><span class="badge bg-info text-dark">${(c.tipo || 'Hóspede').toUpperCase()}</span></td>
                <td class="text-center">
                    <a href="up_cadastro.php?id=${c.cadastro_id}" class="btn btn-sm btn-primary"><i class="fas fa-user-edit"></i></a>
                    <button onclick="gerarPDFCliente(${c.cadastro_id})" class="btn btn-sm btn-danger ms-1"><i class="fas fa-file-pdf"></i></button>
                </td>
            </tr>`;
        });
    };

    // --- RENDER RESERVAS ATUALIZADO (Sem filtros e com botão Excluir) ---
    const renderReservas = (reservas) => {
        if (!reservaTableBody) return;
        reservaTableBody.innerHTML = reservas.length ? '' : '<tr><td colspan="3" class="text-center text-muted py-4">Nenhuma reserva encontrada.</td></tr>';

        reservas.forEach(r => {
            let badgeClass = r.situacao === 'cancelado' ? 'bg-danger' : (r.situacao === 'concluida' ? 'bg-success' : 'bg-primary');
            const dataIn = r.data_checkin.split('-').reverse().join('/');
            const dataOut = r.data_checkout.split('-').reverse().join('/');

            // Alterado para window.excluirReserva
            const botoesAcao = `
                <a href="up_reserva.php?id=${r.reserva_id}" class="btn btn-sm btn-outline-primary" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>
                <button onclick="window.excluirReserva(${r.reserva_id})" class="btn btn-sm btn-outline-danger ms-1" title="Excluir Reserva">
                    <i class="fas fa-trash-alt"></i>
                </button>`;

            reservaTableBody.innerHTML += `
            <tr>
                <td>
                    <div class="fw-bold text-dark">${r.titular_nome}</div> 
                    <div class="small text-muted">
                        <i class="fas fa-id-card me-1"></i>${r.titularCpf_cnpj || '---'} <span class="mx-2">|</span>
                        <i class="fas fa-calendar-alt me-1"></i>${dataIn} até ${dataOut}
                    </div>
                </td>
                <td>
                    <span class="badge ${badgeClass} shadow-sm">${r.situacao.toUpperCase()}</span>
                </td>
                <td class="text-center">
                    ${botoesAcao}
                </td>
            </tr>`;
        });
    };

    const renderHospedagens = (hospedagens) => {
        if (!hospTableBody) return;
        hospTableBody.innerHTML = hospedagens.length ? '' : '<tr><td colspan="6" class="text-center">Nenhuma hospedagem encontrada.</td></tr>';
        hospedagens.forEach(h => {
            const btnFinalizar = h.situacao === 'ativa' ?
                `<button onclick="finalizarEstadia(${h.hospedagem_id})" class="btn btn-sm btn-danger ms-1" title="Check-out"><i class="fas fa-sign-out-alt"></i></button>` : '';
            const valorExibicao = parseFloat(h.total_dispesa || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
            hospTableBody.innerHTML += `
            <tr>
                <td class="fw-bold">${h.nome_hospede}</td>
                <td><span class="badge bg-light text-dark border">Quarto ${h.numero_quarto}</span></td>
                <td class="small">${h.data_checkin} até ${h.data_checkout}</td>
                <td class="fw-bold text-success">R$ ${valorExibicao}</td>
                <td>
                    <span class="badge ${h.situacao === 'ativa' ? 'bg-success' : 'bg-secondary'}">
                        ${h.situacao.toUpperCase()}
                    </span>
                </td>
                <td class="text-center">
                    <a href="up_hospedagem.php?id=${h.hospedagem_id}" class="btn btn-sm btn-warning" title="Editar/Ver Detalhes">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button onclick="gerarPDF(${h.hospedagem_id})" class="btn btn-sm btn-info ms-1 text-white" title="Imprimir Extrato">
                        <i class="fas fa-print"></i>
                    </button>
                    ${btnFinalizar}
                </td>
            </tr>`;
        });
    };

    // --- FUNÇÃO EXCLUIR RESERVA (CORRIGIDA PARA POST JSON) ---
    window.excluirReserva = async (id) => {
        if (!id) return alert("ID inválido.");
        if (!confirm("Deseja realmente EXCLUIR permanentemente esta reserva?")) return;

        try {
            // Alterado para enviar JSON via POST
            const res = await fetch(`../../app/controllers/excluir_reserva_process.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const result = await res.json();

            if (result.success) {
                alert("Reserva excluída com sucesso!");
                fetchDataAndRender();
            } else {
                alert("Erro: " + result.message);
            }
        } catch (e) {
            console.error(e);
            alert("Erro ao conectar com o servidor.");
        }
    };

    // --- OUTROS EVENTOS (Mantidos conforme original) ---
    document.getElementById('btnConfirmarCheckout')?.addEventListener('click', async () => {
        if (!idHospedagemParaFinalizar) return;
        const res = await fetch('../../app/controllers/finalizar_hospedagem_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: idHospedagemParaFinalizar })
        });
        const result = await res.json();
        alert(result.message);
        if (result.success) {
            const modalEl = document.getElementById('modalCheckout');
            bootstrap.Modal.getInstance(modalEl).hide();
            fetchDataAndRender();
        }
    });

    window.realizarLimpeza = async (id) => {
        if (!confirm("Confirmar limpeza deste quarto?")) return;
        const res = await fetch('../../app/controllers/quarto_process.php?action=limpar_quarto', { method: 'POST', body: JSON.stringify({ id }) });
        if ((await res.json()).success) fetchDataAndRender();
    };

    window.finalizarEstadia = async (id) => {
        idHospedagemParaFinalizar = id;
        try {
            const res = await fetch(`../../app/controllers/leitura_hospedagem.php?id=${id}`);
            const result = await res.json();
            if (result.success) {
                const h = result.data;
                let nomesAcompanhantes = h.acompanhantes && h.acompanhantes.length > 0
                    ? h.acompanhantes.map(a => a.nome_hospede).join(', ')
                    : 'Nenhum acompanhante.';
                document.getElementById('checkoutNome').innerHTML = `${h.nome_titular} <br><small class="text-muted">Acomp: ${nomesAcompanhantes}</small>`;
                document.getElementById('checkoutQuarto').innerText = h.numero_quarto;
                let periodoTexto = `${h.data_checkin} até ${h.data_checkout}`;
                if (h.excedeu_prazo) periodoTexto += ` <span class="badge bg-danger ms-2">ESTADIA EXCEDIDA</span>`;
                document.getElementById('checkoutPeriodo').innerHTML = periodoTexto;
                const valorHospedagemFinal = parseFloat(h.valor_hospedagem_atualizado || h.valor_hospedagem);
                document.getElementById('checkoutTotalDiarias').innerText = `R$ ${valorHospedagemFinal.toFixed(2)} (${h.dias_estadia} diárias)`;
                const resConsumo = await fetch(`../../app/controllers/leitura_hospedagem.php?action=get_consumo_total&hospedagem_id=${id}`);
                const resultConsumo = await resConsumo.json();
                let htmlConsumo = '';
                let totalConsumoVal = 0;
                if (resultConsumo.success && resultConsumo.data.length > 0) {
                    resultConsumo.data.forEach(c => {
                        const sub = c.quantidade * c.preco_unitario_pago;
                        totalConsumoVal += sub;
                        htmlConsumo += `<tr>
                        <td>${c.nome_produto} <br><small class="text-muted">(${c.nome_cliente})</small></td>
                        <td class="text-center">${c.quantidade}</td>
                        <td>R$ ${parseFloat(c.preco_unitario_pago).toFixed(2)}</td>
                        <td class="text-end">R$ ${sub.toFixed(2)}</td>
                    </tr>`;
                    });
                } else {
                    htmlConsumo = '<tr><td colspan="4" class="text-center text-muted">Nenhum consumo registrado.</td></tr>';
                }
                document.getElementById('checkoutListaConsumo').innerHTML = htmlConsumo;
                document.getElementById('checkoutTotalConsumo').innerText = `R$ ${totalConsumoVal.toFixed(2)}`;
                const totalPagar = valorHospedagemFinal + totalConsumoVal;
                document.getElementById('checkoutTotalGeral').innerText = `R$ ${totalPagar.toFixed(2)}`;
                const myModal = new bootstrap.Modal(document.getElementById('modalCheckout'));
                myModal.show();
            }
        } catch (e) { console.error(e); alert("Erro ao processar resumo financeiro."); }
    };

    window.gerarPDF = (id) => window.open(`../../app/controllers/gerar_extrato_pdf.php?id=${id}`, '_blank');
    window.gerarPDFCliente = (id) => window.open(`../../app/controllers/gerar_ficha_cliente_pdf.php?id=${id}`, '_blank');
    window.gerarExtratoPrevia = () => { if (idHospedagemParaFinalizar) window.gerarPDF(idHospedagemParaFinalizar); };

    // FILTROS E EVENTOS (Sem o filter-reserva)
    document.querySelectorAll('.filter-hosp, .filter-quarto').forEach(btn => {
        btn.addEventListener('click', function () {
            this.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (this.classList.contains('filter-hosp')) currentHospFilter = this.dataset.status;
            else if (this.classList.contains('filter-quarto')) currentQuartoFilter = this.dataset.filter;
            fetchDataAndRender();
        });
    });

    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', fetchDataAndRender);
    });

    setupSearchEvents();
    fetchDataAndRender();
});
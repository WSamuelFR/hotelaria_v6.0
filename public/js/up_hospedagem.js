// public/js/up_hospedagem.js
let hospedesEdicao = [];
let hospedeAtivoId = null;

/**
 * Calcula o total da estadia baseando-se em Pessoas, Preço Unitário e Dias
 */
const calcularTotalEdicao = () => {
    const precoInput = document.getElementById('preco_unitario');
    const labelTotal = document.getElementById('labelTotal');
    const dataIn = document.getElementById('checkin').value;
    const dataOut = document.getElementById('checkout').value;

    if (!precoInput || !labelTotal || !dataIn || !dataOut) return;

    // 1. Diferença de dias (sem horas)
    const d1 = new Date(dataIn);
    const d2 = new Date(dataOut);
    const diffTime = d2 - d1;
    let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    // Regra: Mínimo de 1 diária
    if (diffDays <= 0) diffDays = 1;

    // 2. Cálculo: (Pessoas * Preço Unitário) * Dias
    const precoPessoa = parseFloat(precoInput.value) || 0;
    const total = (hospedesEdicao.length * precoPessoa) * diffDays;

    labelTotal.innerText = total.toFixed(2);
};

/**
 * 1. CARREGA OS DADOS DA HOSPEDAGEM
 */
const carregarDadosHospedagemEdicao = async () => {
    const idHosp = document.getElementById('hospedagem_id').value;
    if (!idHosp || idHosp === "0") return;

    try {
        const res = await fetch(`../../app/controllers/leitura_hospedagem.php?id=${idHosp}`);
        const result = await res.json();

        if (result.success) {
            const h = result.data;

            document.getElementById('checkin').value = h.data_checkin;
            document.getElementById('checkout').value = h.data_checkout;
            document.getElementById('obs').value = h.observacoes;
            document.getElementById('titular_id').value = h.hospedes;
            document.getElementById('busca_titular').value = `${h.nome_titular} | ${h.documento_titular || h.cpf_titular}`;

            // Na primeira carga, o preço unitário vem do total dividido por pessoas e dias
            const totalH = parseFloat(h.valor_hospedagem) || 0;
            const numPessoas = parseInt(h.qtd_total_hospedes) || 1;

            // Cálculo reverso para achar o preço por pessoa/dia inicial
            const d1 = new Date(h.data_checkin);
            const d2 = new Date(h.data_checkout);
            let dias = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
            if (dias <= 0) dias = 1;

            document.getElementById('preco_unitario').value = (totalH / numPessoas / dias).toFixed(2);

            hospedesEdicao = [];
            hospedesEdicao.push({ id: h.hospedes, nome: h.nome_titular, doc: h.documento_titular || h.cpf_titular, tipo: 'Titular' });

            if (h.acompanhantes) {
                h.acompanhantes.forEach(ac => {
                    hospedesEdicao.push({
                        id: ac.cadastro_id,
                        nome: ac.nome_hospede || ac.full_name,
                        doc: ac.documento || ac.cpf_cnpj,
                        tipo: 'Acompanhante'
                    });
                });
            }

            renderizarHospedesEdicao();
            carregarCatalogoLoja();
            window.selecionarHospedeVenda(h.hospedes, h.nome_titular);
        }
    } catch (e) { console.error("Erro ao carregar:", e); }
};

/**
 * 2. RENDERIZA TABELA DE HÓSPEDES
 */
const renderizarHospedesEdicao = () => {
    const grid = document.getElementById('gridHospedesEdicao');
    if (!grid) return;
    grid.innerHTML = '';

    hospedesEdicao.forEach((h, index) => {
        const tr = document.createElement('tr');
        if (h.id == hospedeAtivoId) tr.classList.add('table-warning', 'fw-bold');
        tr.style.cursor = 'pointer';
        tr.onclick = () => window.selecionarHospedeVenda(h.id, h.nome);

        tr.innerHTML = `
            <td><i class="fas fa-user me-2"></i>${h.nome}</td>
            <td>${h.doc}</td>
            <td class="text-center">
                ${h.tipo === 'Titular' ? '<span class="badge bg-primary">Titular</span>' :
                `<button type="button" onclick="event.stopPropagation(); removerHospedeEdicao(${index})" class="btn btn-sm text-danger border-0"><i class="fas fa-trash"></i></button>`}
            </td>`;
        grid.appendChild(tr);
    });

    calcularTotalEdicao();
};

/**
 * 3. SELECIONA HÓSPEDE PARA CONSUMO
 */
window.selecionarHospedeVenda = (id, nome) => {
    hospedeAtivoId = id;
    const labelNome = document.getElementById('nomeHospedeAtivo');
    if (labelNome) labelNome.innerText = nome;
    renderizarHospedesEdicao();
    fetchConsumoHospede(id);
};

/**
 * 4. BUSCA E AGRUPA CONSUMO
 */
window.fetchConsumoHospede = async (hospedeId) => {
    const idHospedagem = document.getElementById('hospedagem_id').value;
    const tbody = document.getElementById('listaConsumoHospede');
    if (!tbody) return;

    try {
        const res = await fetch(`../../app/controllers/leitura_hospedagem.php?action=get_consumo&hospedagem_id=${idHospedagem}&hospede_id=${hospedeId}`);
        const result = await res.json();
        tbody.innerHTML = '';
        let totalGeralHospede = 0;

        if (result.success && result.data.length > 0) {
            const agrupados = {};
            result.data.forEach(item => {
                if (!agrupados[item.nome_produto]) {
                    agrupados[item.nome_produto] = { qtd: 0, preco: parseFloat(item.preco_unitario_pago), sub: 0 };
                }
                agrupados[item.nome_produto].qtd += parseInt(item.quantidade);
                agrupados[item.nome_produto].sub += (parseInt(item.quantidade) * parseFloat(item.preco_unitario_pago));
            });

            for (const prod in agrupados) {
                const item = agrupados[prod];
                totalGeralHospede += item.sub;
                tbody.innerHTML += `<tr><td>${prod}</td><td class="text-center">${item.qtd}x</td><td>R$ ${item.preco.toFixed(2)}</td><td class="text-end fw-bold">R$ ${item.sub.toFixed(2)}</td></tr>`;
            }
            tbody.innerHTML += `<tr class="table-dark"><td colspan="3" class="text-end fw-bold">TOTAL DO HÓSPEDE:</td><td class="text-end fw-bold text-warning">R$ ${totalGeralHospede.toFixed(2)}</td></tr>`;
        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Nenhum consumo.</td></tr>';
        }
    } catch (e) { console.error(e); }
};

/**
 * 5. LOJA E VENDAS
 */
const carregarCatalogoLoja = async () => {
    const tbody = document.getElementById('catalogoLoja');
    if (!tbody) return;
    try {
        const res = await fetch('../../app/controllers/produto_process.php');
        const result = await res.json();
        tbody.innerHTML = '';
        if (result.success) {
            result.data.forEach(p => {
                tbody.innerHTML += `<tr><td>${p.nome}</td><td>R$ ${parseFloat(p.preco_venda).toFixed(2)}</td><td><input type="number" id="qtd_${p.produto_id}" class="form-control form-control-sm" value="1" min="1"></td><td><button type="button" class="btn btn-sm btn-success" onclick="window.confirmarCompra(${p.produto_id}, ${p.preco_venda})"><i class="fas fa-cart-plus"></i></button></td></tr>`;
            });
        }
    } catch (e) { console.error(e); }
};

window.confirmarCompra = async (produtoId, preco) => {
    if (!hospedeAtivoId) return alert("Selecione um hóspede na lista lateral!");
    const payload = {
        hospedagem_id: document.getElementById('hospedagem_id').value,
        hospede_id: hospedeAtivoId,
        produto_id: produtoId,
        quantidade: document.getElementById(`qtd_${produtoId}`).value,
        preco_unitario: preco
    };
    const res = await fetch('../../app/controllers/hospedagem_process.php?action=lancar_consumo', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
    const result = await res.json();
    if (result.success) { alert("Lançado!"); fetchConsumoHospede(hospedeAtivoId); }
};

/**
 * 6. ADICIONAR / REMOVER ACOMPANHANTES
 */
document.getElementById('busca_acompanhante')?.addEventListener('input', function () {
    const datalist = document.getElementById('listaClientes');
    const valor = this.value;
    const opcao = Array.from(datalist.options).find(opt => opt.value === valor);
    if (opcao) {
        const novo = { id: opcao.getAttribute('data-id'), nome: opcao.getAttribute('data-nome'), doc: opcao.getAttribute('data-doc'), tipo: 'Acompanhante' };
        if (!hospedesEdicao.some(h => String(h.id) === String(novo.id))) {
            hospedesEdicao.push(novo);
            renderizarHospedesEdicao();
        }
        this.value = '';
    }
});

window.removerHospedeEdicao = (index) => {
    hospedesEdicao.splice(index, 1);
    renderizarHospedesEdicao();
};

window.gerarPDF = (id) => window.open(`../../app/controllers/gerar_extrato_pdf.php?id=${id}`, '_blank');

/**
 * 7. SALVAR ALTERAÇÕES E EVENTOS DE RECALCULO
 */
document.getElementById('preco_unitario')?.addEventListener('input', calcularTotalEdicao);
document.getElementById('checkin')?.addEventListener('change', calcularTotalEdicao);
document.getElementById('checkout')?.addEventListener('change', calcularTotalEdicao);

document.getElementById('upHospedagemForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
        id: document.getElementById('hospedagem_id').value,
        quarto_id: document.getElementById('quarto_id').value,
        titular_id: document.getElementById('titular_id').value,
        checkin: document.getElementById('checkin').value,
        checkout: document.getElementById('checkout').value,
        total: document.getElementById('preco_unitario').value, // Envia o PREÇO UNITÁRIO, o Model calcula o resto
        observacoes: document.getElementById('obs').value,
        acompanhantes: hospedesEdicao.filter(h => h.tipo !== 'Titular').map(h => h.id)
    };
    const res = await fetch('../../app/controllers/hospedagem_process.php?action=update', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
    const result = await res.json();
    alert(result.message);
    if (result.success) window.location.href = 'lobby.php';
});

document.addEventListener('DOMContentLoaded', carregarDadosHospedagemEdicao);
// public/js/hospedagem.js
let hospedesNoQuarto = [];

/**
 * Calcula o total baseando-se na quantidade de hóspedes, preço unitário e QUANTIDADE DE DIAS
 */
const calcularTotalHospedagem = () => {
    const precoInput = document.getElementById('preco_unitario');
    const labelTotal = document.getElementById('labelTotal');
    const dataIn = document.getElementById('checkin').value;
    const dataOut = document.getElementById('checkout').value;
    
    if (!precoInput || !labelTotal || !dataIn || !dataOut) return;

    // 1. Calcula a diferença de dias
    const d1 = new Date(dataIn);
    const d2 = new Date(dataOut);
    const diffTime = d2 - d1;
    let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    // Regra: Mínimo de 1 diária
    if (diffDays <= 0) diffDays = 1;

    // 2. Calcula o valor total
    const precoPessoa = parseFloat(precoInput.value) || 0;
    const total = (hospedesNoQuarto.length * precoPessoa) * diffDays;
    
    labelTotal.innerText = total.toFixed(2);
};

/**
 * Renderiza a tabela de hóspedes na mini-tela
 */
window.renderizarTabela = () => {
    const grid = document.getElementById('gridHospedes');
    if (!grid) return;

    grid.innerHTML = '';
    hospedesNoQuarto.forEach((h, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${h.nome}</td>
            <td>${h.doc}</td>
            <td><span class="badge ${h.tipo === 'Titular' ? 'bg-primary' : 'bg-info'}">${h.tipo}</span></td>
            <td class="text-center">
                <button type="button" onclick="removerHospede(${index})" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
            </td>`;
        grid.appendChild(tr);
    });

    calcularTotalHospedagem();
};

/**
 * Adiciona um hóspede ao array global e atualiza a interface
 */
window.adicionarHospede = (id, nome, doc, tipo) => {
    if (!id || id === "undefined") return;
    
    if (hospedesNoQuarto.some(h => String(h.id) === String(id))) {
        alert("Este hóspede já foi adicionado.");
        return;
    }

    if (tipo === 'Titular') {
        hospedesNoQuarto = hospedesNoQuarto.filter(h => h.tipo !== 'Titular');
        hospedesNoQuarto.unshift({ id, nome, doc, tipo });
        const inputTitular = document.getElementById('titular_id');
        if (inputTitular) inputTitular.value = id;
    } else {
        hospedesNoQuarto.push({ id, nome, doc, tipo });
    }
    window.renderizarTabela();
};

/**
 * Remove um hóspede da lista
 */
window.removerHospede = (index) => {
    hospedesNoQuarto.splice(index, 1);
    window.renderizarTabela();
};

document.addEventListener('DOMContentLoaded', () => {
    const buscaTitular = document.getElementById('busca_titular');
    const buscaAcompanhante = document.getElementById('busca_acompanhante');
    const datalist = document.getElementById('listaClientes');
    const formHospedagem = document.getElementById('hospedagemForm');

    const validarESelecionar = (input, tipo) => {
        if (!input || !datalist) return;

        input.addEventListener('input', function () {
            const valorDigitado = this.value;
            const opcao = Array.from(datalist.options).find(opt => opt.value === valorDigitado);

            if (opcao) {
                const id = opcao.getAttribute('data-id');
                const nome = opcao.getAttribute('data-nome');
                const doc = opcao.getAttribute('data-doc');
                window.adicionarHospede(id, nome, doc, tipo);
                this.value = ''; 
            }
        });
    };

    validarESelecionar(buscaTitular, 'Titular');
    validarESelecionar(buscaAcompanhante, 'Acompanhante');

    // EVENTOS PARA RECALCULAR O TOTAL IMEDIATAMENTE
    document.getElementById('preco_unitario')?.addEventListener('input', calcularTotalHospedagem);
    document.getElementById('checkin')?.addEventListener('change', calcularTotalHospedagem);
    document.getElementById('checkout')?.addEventListener('change', calcularTotalHospedagem);

    if (formHospedagem) {
        formHospedagem.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (hospedesNoQuarto.length === 0) {
                alert("Por favor, adicione o hóspede titular antes de salvar.");
                return;
            }

            const payload = {
                reserva_id: document.getElementById('reserva_id')?.value || null,
                quarto_id: document.getElementById('quarto_id').value,
                preco_unitario: document.getElementById('preco_unitario').value,
                checkin: document.getElementById('checkin').value,
                checkout: document.getElementById('checkout').value,
                obs: document.getElementById('obs').value,
                hospedes: hospedesNoQuarto,
                usuario: document.getElementById('usuario_id')?.value || null
            };

            try {
                const res = await fetch('../../app/controllers/hospedagem_process.php?action=salvar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();

                if (result.success) {
                    alert(result.message);
                    window.location.href = 'lobby.php';
                } else {
                    alert("Erro: " + result.message);
                }
            } catch (err) {
                console.error("Erro:", err);
                alert("Erro ao conectar com o servidor.");
            }
        });
    }
});
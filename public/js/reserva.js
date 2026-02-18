document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reservaForm');
    const quartoSelect = document.getElementById('quarto_select');
    const btnAddAcomp = document.getElementById('btnAddAcomp');
    const btnLimparAcomp = document.getElementById('btnLimparAcomp');
    const acompanhantesTexto = document.getElementById('acompanhantes_texto');

    // --- 1. CARREGAR QUARTOS ---
    const carregarQuartos = async () => {
        try {
            const res = await fetch('../../app/controllers/reserva_data.php?type=quartos&search=');
            const result = await res.json();
            if (result.success) {
                quartoSelect.innerHTML = '<option value="">Selecione um Quarto...</option>';
                result.data.forEach(q => {
                    quartoSelect.innerHTML += `<option value="${q.id}">Quarto ${q.numero} - ${q.tipo} (${q.status_display})</option>`;
                });
            }
        } catch (e) { 
            console.error("Erro ao carregar quartos", e); 
        }
    };

    // --- 2. ADICIONAR AO TEXTAREA (FORMATADO) ---
    btnAddAcomp.onclick = () => {
        const nome = document.getElementById('temp_nome').value.trim();
        const cpf  = document.getElementById('temp_cpf').value.trim();
        const fone = document.getElementById('temp_fone').value.trim();

        if (!nome) {
            alert("O nome do acompanhante é obrigatório.");
            return;
        }

        const novaLinha = `• Nome: ${nome} | CPF: ${cpf || 'N/I'} | Tel: ${fone || 'N/I'}\n`;
        acompanhantesTexto.value += novaLinha;

        // Limpa campos temporários
        document.getElementById('temp_nome').value = '';
        document.getElementById('temp_cpf').value = '';
        document.getElementById('temp_fone').value = '';
        document.getElementById('temp_nome').focus();
    };

    // --- 3. LIMPAR TEXTAREA ---
    btnLimparAcomp.onclick = () => {
        if (confirm("Deseja limpar toda a lista de acompanhantes?")) {
            acompanhantesTexto.value = '';
        }
    };

    // --- 4. SALVAR RESERVA ---
    form.onsubmit = async (e) => {
        e.preventDefault();

        // Blindagem: Garantimos que o ID do quarto seja enviado como número e o texto como string limpa
        const payload = {
            quarto:        Number(quartoSelect.value),
            data_checkin:  document.getElementById('data_checkin').value,
            data_checkout: document.getElementById('data_checkout').value,
            titular_nome:  document.getElementById('titular_nome').value.trim(),
            titular_cpf:   document.getElementById('titular_cpf').value.trim(),
            titular_phone: document.getElementById('titular_phone').value.trim(),
            email:         document.getElementById('titular_email').value.trim(),
            acompanhantes: acompanhantesTexto.value.trim()
        };

        if (!payload.quarto) {
            alert("Por favor, selecione um quarto.");
            return;
        }

        try {
            const res = await fetch('../../app/controllers/reserva_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            
            const result = await res.json();
            alert(result.message);
            
            if (result.success) {
                window.location.href = 'lobby.php';
            }
        } catch (err) {
            console.error("Erro no envio:", err);
            alert("Erro ao conectar ao servidor.");
        }
    };

    carregarQuartos();
});
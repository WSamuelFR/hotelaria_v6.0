document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('upReservaForm');
    const quartoSelect = document.getElementById('quarto_select');
    const btnAddAcomp = document.getElementById('btnAddAcomp');
    const btnLimparAcomp = document.getElementById('btnLimparAcomp');
    const acompanhantesTexto = document.getElementById('acompanhantes_texto');
    const reservaId = document.getElementById('reserva_id').value;

    const inicializar = async () => {
        await carregarQuartos();
        await carregarDadosReserva();
    };

    // --- 1. CARREGAR QUARTOS ---
    const carregarQuartos = async () => {
        try {
            const res = await fetch('../../app/controllers/reserva_data.php?type=quartos');
            const result = await res.json();
            if (result.success) {
                quartoSelect.innerHTML = '<option value="">Selecione...</option>';
                result.data.forEach(q => {
                    quartoSelect.innerHTML += `<option value="${q.id}">Quarto ${q.numero} (${q.tipo})</option>`;
                });
            }
        } catch (e) { 
            console.error("Erro ao carregar quartos", e); 
        }
    };

    // --- 2. CARREGAR DADOS DA RESERVA ---
    const carregarDadosReserva = async () => {
        try {
            const res = await fetch(`../../app/controllers/leitura_reserva.php?id=${reservaId}`);
            const result = await res.json();
            if (result.success) {
                const r = result.data;
                
                // Preenche os campos de data e quarto
                document.getElementById('data_checkin').value = r.data_checkin;
                document.getElementById('data_checkout').value = r.data_checkout;
                quartoSelect.value = r.quarto;
                
                // Preenche os campos do titular
                document.getElementById('titular_nome').value = r.titular_nome;
                document.getElementById('titular_cpf').value = r.titularCpf_cnpj;
                document.getElementById('titular_phone').value = r.titular_phone;
                document.getElementById('titular_email').value = r.email;
                
                // Preenche a textarea com os acompanhantes já formatados no banco
                acompanhantesTexto.value = r.acompanhante || '';
            }
        } catch (e) { 
            console.error("Erro ao carregar reserva", e); 
        }
    };

    // --- 3. ADICIONAR AO TEXTAREA (FORMATADO) ---
    btnAddAcomp.onclick = () => {
        const nome = document.getElementById('temp_nome').value.trim();
        const cpf  = document.getElementById('temp_cpf').value.trim();
        const fone = document.getElementById('temp_fone').value.trim();

        if (!nome) {
            alert("O nome do acompanhante é obrigatório.");
            return;
        }

        // Verifica se já existe texto para pular linha
        const quebra = acompanhantesTexto.value.trim() ? "\n" : "";
        acompanhantesTexto.value += `${quebra}• Nome: ${nome} | CPF: ${cpf || 'N/I'} | Tel: ${fone || 'N/I'}`;
        
        // Limpa campos temporários
        document.getElementById('temp_nome').value = '';
        document.getElementById('temp_cpf').value = '';
        document.getElementById('temp_fone').value = '';
        document.getElementById('temp_nome').focus();
    };

    // --- 4. LIMPAR TEXTAREA ---
    btnLimparAcomp.onclick = () => {
        if (confirm("Deseja apagar todos os acompanhantes desta lista?")) {
            acompanhantesTexto.value = '';
        }
    };

    // --- 5. SALVAR ALTERAÇÕES (UPDATE) ---
    form.onsubmit = async (e) => {
        e.preventDefault();

        // Montagem do payload com as mesmas chaves que o Model espera receber no array $data
        const payload = {
            id:            Number(reservaId),
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
            // Chamada ao controller de processo (que lida com o UPDATE no Model)
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
        } catch (error) {
            console.error("Erro ao salvar:", error);
            alert("Erro ao conectar com o servidor.");
        }
    };

    inicializar();
});